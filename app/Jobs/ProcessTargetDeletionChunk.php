<?php

namespace App\Jobs;

use App\Models\TargetDeletionJob;
use App\Services\Task\TargetDeletionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTargetDeletionChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $jobId,
        public int $chunkSize = 50
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(TargetDeletionService $deletionService): void
    {
        $job = TargetDeletionJob::where('job_id', $this->jobId)->first();

        if (!$job) {
            Log::error('Target deletion job not found', ['job_id' => $this->jobId]);
            return;
        }

        // Mark as processing if not already
        if ($job->status === 'pending') {
            $job->markAsProcessing();
        }

        // Get unprocessed task IDs
        $allTaskIds = $job->task_ids ?? [];
        $processedTaskIds = $job->processed_task_ids ?? [];
        $remainingTaskIds = array_diff($allTaskIds, $processedTaskIds);

        if (empty($remainingTaskIds)) {
            // All tasks processed, mark as completed
            $job->markAsCompleted();
            return;
        }

        // Process chunk
        $chunk = array_slice($remainingTaskIds, 0, $this->chunkSize);

        try {
            foreach ($chunk as $taskId) {
                // Process deletion for this task
                $result = $deletionService->deleteTargets([$taskId], $this->jobId);

                // Update progress
                $job->addProcessedTaskId($taskId);
                $job->increment('processed_poles', $result['poles_deleted'] ?? 0);

                // Refresh job to get latest state
                $job->refresh();
            }

            // Check if all tasks are processed
            $job->refresh();
            $remainingAfterChunk = array_diff($job->task_ids, $job->processed_task_ids);

            if (empty($remainingAfterChunk)) {
                // All done
                $job->markAsCompleted();
            } else {
                // Queue next chunk
                self::dispatch($this->jobId, $this->chunkSize)->delay(now()->addSeconds(1));
            }

        } catch (\Exception $e) {
            Log::error('Error processing target deletion chunk', [
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $job->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $job = TargetDeletionJob::where('job_id', $this->jobId)->first();
        
        if ($job && $job->status !== 'completed') {
            $job->markAsFailed($exception->getMessage());
        }
    }
}
