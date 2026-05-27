<?php

namespace App\Jobs;

use App\Models\Settings\PoleNumberRegenerationBatch;
use App\Services\Settings\PoleNumberFormatService;
use App\Services\Settings\PoleNumberRegenerationNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RegeneratePoleNumbersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public function __construct(public int $batchId)
    {
    }

    public function handle(
        PoleNumberFormatService $service,
        PoleNumberRegenerationNotificationService $notifier,
    ): void {
        $batch = PoleNumberRegenerationBatch::with('format')->findOrFail($this->batchId);
        $batch->update([
            'status' => 'running',
            'started_at' => now(),
            'error_message' => null,
        ]);

        try {
            $processed = 0;
            $service->polesForFormat($batch->format)
                ->orderBy('id')
                ->chunkById(250, function ($poles) use ($service, $batch, &$processed) {
                    foreach ($poles as $pole) {
                        $pole->forceFill([
                            'complete_pole_number' => $service->generateForPole($pole, $batch->format),
                            'pole_number_format_id' => $batch->format->id,
                        ])->save();
                        $processed++;
                    }

                    $batch->forceFill(['processed_count' => $processed])->save();
                });

            $batch->update([
                'status' => 'completed',
                'processed_count' => $processed,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $batch->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            throw $e;
        } finally {
            $notifier->notifyFinished($batch->fresh());
        }
    }
}
