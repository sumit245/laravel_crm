<?php

namespace App\Services\Task\Strategies;

use App\Contracts\TaskTypeStrategyInterface;
use App\Enums\TaskStatus;
use App\Models\Task;

/**
 * Rooftop Task Strategy
 * 
 * Handles rooftop project-specific task logic
 */
class RooftopTaskStrategy implements TaskTypeStrategyInterface
{
    /**
     * Get the task model class for this strategy
     * 
     * @return string
     */
    public function getTaskModel(): string
    {
        return Task::class;
    }

    /**
     * Validate task data specific to rooftop tasks
     * 
     * @param array $data
     * @return array Validation errors if any
     */
    public function validateTaskData(array $data): array
    {
        $errors = [];

        // Rooftop tasks require site_id
        if (empty($data['site_id'])) {
            $errors['site_id'] = 'Site is required for rooftop tasks';
        }

        // Validate activity type for rooftop
        if (isset($data['activity'])) {
            $validActivities = [
                'Installation',
                'RMS',
                'Inspection',
                'Maintenance',
                'Drawing Approval',
                'Material Supply'
            ];

            if (!in_array($data['activity'], $validActivities)) {
                $errors['activity'] = 'Invalid activity type for rooftop task';
            }
        }

        return $errors;
    }

    /**
     * Calculate task progress percentage
     * 
     * @param object $task
     * @return float
     */
    public function calculateProgress(object $task): float
    {
        $status = TaskStatus::from($task->status);

        return match($status) {
            TaskStatus::PENDING => 0.0,
            TaskStatus::IN_PROGRESS => 50.0,
            TaskStatus::BLOCKED => 50.0,
            TaskStatus::COMPLETED => 100.0,
        };
    }

    /**
     * Get progress metrics for rooftop tasks
     * 
     * @param object $task
     * @return array
     */
    public function getProgressMetrics(object $task): array
    {
        $site = $task->site;

        return [
            'task_id' => $task->id,
            'task_name' => $task->task_name ?? $task->activity,
            'status' => $task->status,
            'progress_percentage' => $this->calculateProgress($task),
            'site_name' => $site?->site_name,
            'site_id' => $task->site_id,
            'start_date' => $task->start_date,
            'end_date' => $task->end_date,
            'is_overdue' => $task->end_date && now()->greaterThan($task->end_date) 
                && $task->status !== TaskStatus::COMPLETED->value,
        ];
    }

    /**
     * Get required fields for rooftop tasks
     * 
     * @return array
     */
    public function getRequiredFields(): array
    {
        return [
            'project_id',
            'site_id',
            'activity',
            'start_date',
        ];
    }

    /**
     * Prepare task data for storage
     * 
     * @param array $data
     * @return array
     */
    public function prepareTaskData(array $data): array
    {
        return [
            'project_id' => $data['project_id'],
            'site_id' => $data['site_id'],
            'engineer_id' => $data['engineer_id'] ?? null,
            'vendor_id' => $data['vendor_id'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'activity' => $data['activity'] ?? 'Installation',
            'task_name' => $data['task_name'] ?? $data['activity'] ?? 'Rooftop Installation',
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? TaskStatus::PENDING->value,
            'start_date' => $data['start_date'] ?? now(),
            'end_date' => $data['end_date'] ?? null,
            'image' => $data['image'] ?? null,
            'materials_consumed' => $data['materials_consumed'] ?? null,
        ];
    }

    /**
     * Get task type identifier
     * 
     * @return string
     */
    public function getTaskType(): string
    {
        return 'rooftop';
    }
}
