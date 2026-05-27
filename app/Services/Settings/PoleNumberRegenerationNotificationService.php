<?php

namespace App\Services\Settings;

use App\Models\Settings\PoleNumberRegenerationBatch;
use App\Services\Logging\ActivityLogger;
use App\Services\Notification\EventNotificationService;
use App\Services\Notification\StakeholderResolver;

class PoleNumberRegenerationNotificationService
{
    public function __construct(
        private readonly EventNotificationService $notifications,
        private readonly StakeholderResolver $stakeholders,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function notifyFinished(PoleNumberRegenerationBatch $batch): void
    {
        $batch->loadMissing('format');

        if (! in_array($batch->status, ['completed', 'failed'], true)) {
            return;
        }

        $projectId = $batch->project_id ?? $batch->format?->project_id;
        $formatName = $batch->format?->name ?? 'Unknown format';

        if ($batch->status === 'completed') {
            $action = 'pole_numbers_regenerated';
            $title = 'Pole numbers updated';
            $message = sprintf(
                'Format "%s" (%s ward): %s of %s pole(s) updated.',
                $formatName,
                strtoupper((string) $batch->ward_type),
                number_format((int) $batch->processed_count),
                number_format((int) $batch->affected_count),
            );
        } else {
            $action = 'pole_numbers_regeneration_failed';
            $title = 'Pole number update failed';
            $message = sprintf(
                'Format "%s": %s',
                $formatName,
                $batch->error_message ?? 'Unknown error',
            );
        }

        $recipientIds = collect([$batch->created_by])
            ->merge($this->stakeholders->resolve($projectId, null))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $this->notifications->notifyUsers(
            $recipientIds,
            'settings',
            $action,
            $title,
            $message,
            [
                'project_id' => $projectId,
                'entity_type' => PoleNumberRegenerationBatch::class,
                'entity_id' => $batch->id,
                'extra' => [
                    'batch_id' => $batch->id,
                    'format_id' => $batch->pole_number_format_id,
                    'status' => $batch->status,
                ],
            ]
        );

        $this->activityLogger->log('settings', $action, $batch, [
            'project_id' => $projectId,
            'description' => $message,
            'batch_id' => $batch->id,
        ]);
    }
}
