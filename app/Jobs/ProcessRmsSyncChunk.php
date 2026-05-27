<?php

namespace App\Jobs;

use App\Helpers\RemoteApiHelper;
use App\Models\Pole;
use App\Models\RmsPushLog;
use App\Models\RmsSyncBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessRmsSyncChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $batchId,
        public array $poleIds,
        public ?int $requestedBy,
        public ?string $projectName = null
    ) {
    }

    public function handle(): void
    {
        $batch = RmsSyncBatch::find($this->batchId);
        if (!$batch || in_array($batch->status, ['failed', 'completed'], true)) {
            return;
        }

        if ($batch->status === 'queued') {
            $batch->update([
                'status' => 'processing',
                'started_at' => $batch->started_at ?? now(),
            ]);
        }

        $poles = Pole::with(['task.engineer', 'task.site'])
            ->whereIn('id', $this->poleIds)
            ->get();

        $successCount = 0;
        $errorCount = 0;

        foreach ($poles as $pole) {
            try {
                $task = $pole->task;
                $streetlight = $task?->site;
                $engineer = $task?->engineer;
                if (!$task || !$streetlight) {
                    throw new \RuntimeException('Missing related task/streetlight data.');
                }

                $approvedBy = trim(($engineer->firstName ?? '') . ' ' . ($engineer->lastName ?? ''));
                if ($approvedBy === '') {
                    $approvedBy = $engineer?->name ?? 'System';
                }

                $apiResponse = RemoteApiHelper::sendPoleDataToRemoteServer(
                    $pole,
                    $streetlight,
                    $approvedBy,
                    $this->projectName ?? 'SUGS'
                );

                $responseData = $apiResponse ? $apiResponse->json() : null;
                $isSuccess = $apiResponse
                    && $apiResponse->successful()
                    && is_array($responseData)
                    && strtoupper((string) ($responseData['status'] ?? '')) === 'OK';

                $message = $responseData['detail']
                    ?? $responseData['details']
                    ?? ($isSuccess ? 'Successfully pushed to RMS' : ($apiResponse ? $apiResponse->body() : 'No response from RMS API'));

                RmsPushLog::create([
                    'pole_id' => $pole->id,
                    'message' => $message,
                    'response_data' => is_array($responseData) ? $responseData : ['raw' => (string) $message],
                    'district' => $streetlight->district ?? null,
                    'block' => $streetlight->block ?? null,
                    'panchayat' => $streetlight->panchayat ?? null,
                    'pushed_by' => $this->requestedBy,
                    'pushed_at' => now(),
                ]);

                if ($isSuccess) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } catch (Throwable $e) {
                $errorCount++;
                Log::error('Failed to process RMS sync pole', [
                    'batch_id' => $this->batchId,
                    'pole_id' => $pole->id ?? null,
                    'error' => $e->getMessage(),
                ]);

                RmsPushLog::create([
                    'pole_id' => $pole->id ?? null,
                    'message' => $e->getMessage(),
                    'response_data' => ['status' => 'ERR', 'detail' => $e->getMessage()],
                    'district' => $pole->task->site->district ?? null,
                    'block' => $pole->task->site->block ?? null,
                    'panchayat' => $pole->task->site->panchayat ?? null,
                    'pushed_by' => $this->requestedBy,
                    'pushed_at' => now(),
                ]);
            }
        }

        DB::transaction(function () use ($successCount, $errorCount) {
            $batch = RmsSyncBatch::where('id', $this->batchId)->lockForUpdate()->first();
            if (!$batch || in_array($batch->status, ['failed', 'completed'], true)) {
                return;
            }

            $processedIncrement = $successCount + $errorCount;
            $batch->processed_poles += $processedIncrement;
            $batch->success_count += $successCount;
            $batch->error_count += $errorCount;

            if ($batch->processed_poles >= $batch->total_poles) {
                $batch->status = 'completed';
                $batch->finished_at = now();
            }

            $batch->save();
        });
    }

    public function failed(Throwable $exception): void
    {
        RmsSyncBatch::where('id', $this->batchId)->update([
            'status' => 'failed',
            'last_error' => $exception->getMessage(),
            'finished_at' => now(),
        ]);
    }
}

