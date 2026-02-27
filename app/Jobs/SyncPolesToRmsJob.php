<?php

namespace App\Jobs;

use App\Helpers\RemoteApiHelper;
use App\Models\Pole;
use App\Models\Project;
use App\Models\RmsPushLog;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncPolesToRmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200; // 2 hours max

    protected $projectId;
    protected $poleIds;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($projectId, $poleIds, $userId)
    {
        $this->projectId = $projectId;
        $this->poleIds = $poleIds;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Starting SyncPolesToRmsJob for project {$this->projectId} with " . count($this->poleIds) . " poles.");

        $project = Project::find($this->projectId);
        $projectName = $project && $project->id == 11 ? 'SUGS' : 'SUGS Phase 4';

        // Chunking the poles to conserve memory
        $chunks = array_chunk($this->poleIds, 100);

        foreach ($chunks as $chunkIds) {
            $poles = Pole::whereIn('id', $chunkIds)
                ->with('task.site') // Eager load necessary relations, task engineer can be loaded later or just use user->name
                ->get();

            foreach ($poles as $pole) {
                try {
                    $task = $pole->task;
                    $streetlight = $task ? $task->site : null;

                    // Fallback for missing task or site (especially for project 19)
                    if (!$task && $this->projectId == 19) {
                        // Attempt to find any valid site for project 19 as fallback
                        $streetlight = Streetlight::where('project_id', 19)->first();
                    }

                    if (!$streetlight) {
                        throw new Exception('Missing streetlight site data.');
                    }

                    // Approved by could be the Task Engineer or the User who dispatched the job
                    $approvedBy = '';
                    if ($task && $task->engineer) {
                        $approvedBy = $task->engineer->firstName . ' ' . $task->engineer->lastName;
                    } else {
                        $approvedBy = "Admin User {$this->userId}";
                    }

                    // Call helper to send the data
                    $apiResponse = RemoteApiHelper::sendPoleDataToRemoteServer($pole, $streetlight, $approvedBy, $projectName);

                    $responseData = $apiResponse ? $apiResponse->json() : null;
                    $status = 'error';
                    $message = 'Unknown error';

                    if ($apiResponse && $apiResponse->successful() && $responseData && isset($responseData['status']) && strtoupper((string) $responseData['status']) === 'OK') {
                        $status = 'success';
                        $message = $responseData['detail'] ?? $responseData['details'] ?? 'Successfully pushed to RMS';
                    } else {
                        $message = $responseData['detail'] ?? $responseData['details'] ?? ($apiResponse ? $apiResponse->body() : 'No response from RMS API');
                        if (!$responseData || !isset($responseData['status'])) {
                            $responseData = ['status' => 'ERR', 'detail' => $message];
                        }
                    }

                    RmsPushLog::create([
                        'pole_id' => $pole->id,
                        'message' => $message,
                        'response_data' => $responseData,
                        'district' => $streetlight->district ?? null,
                        'block' => $streetlight->block ?? null,
                        'panchayat' => $streetlight->panchayat ?? null,
                        'pushed_by' => $this->userId,
                        'pushed_at' => now(),
                    ]);

                } catch (Exception $e) {
                    Log::error('Failed to sync pole data to RMS in Job', [
                        'pole_id' => $pole->id ?? null,
                        'error' => $e->getMessage(),
                    ]);

                    if (isset($pole) && $pole->id) {
                        RmsPushLog::create([
                            'pole_id' => $pole->id,
                            'message' => $e->getMessage(),
                            'response_data' => ['status' => 'ERR', 'detail' => $e->getMessage()],
                            'district' => $streetlight->district ?? null,
                            'block' => $streetlight->block ?? null,
                            'panchayat' => $streetlight->panchayat ?? null,
                            'pushed_by' => $this->userId,
                            'pushed_at' => now(),
                        ]);
                    }
                }
            } // end foreach pole
        } // end foreach chunk

        Log::info("Completed SyncPolesToRmsJob for project {$this->projectId}.");
    }
}
