<?php

namespace App\Services\Task\Strategies;

use App\Contracts\TaskTypeStrategyInterface;
use App\Enums\TaskStatus;
use App\Models\StreetlightTask;

/**
 * Streetlight Task Strategy
 * 
 * Handles streetlight project-specific task logic
 */
class StreetlightTaskStrategy implements TaskTypeStrategyInterface
{
    /**
     * Get the task model class for this strategy
     * 
     * @return string
     */
    public function getTaskModel(): string
    {
        return StreetlightTask::class;
    }

    /**
     * Validate task data specific to streetlight tasks
     * 
     * @param array $data
     * @return array Validation errors if any
     */
    public function validateTaskData(array $data): array
    {
        $errors = [];

        // Streetlight tasks require site_id (streetlight site)
        if (empty($data['site_id'])) {
            $errors['site_id'] = 'Streetlight site is required for streetlight tasks';
        }

        // Validate activity type for streetlight
        if (isset($data['activity'])) {
            $validActivities = [
                'Survey',
                'Installation',
                'Testing',
                'Commissioning',
                'Maintenance'
            ];

            if (!in_array($data['activity'], $validActivities)) {
                $errors['activity'] = 'Invalid activity type for streetlight task';
            }
        }

        return $errors;
    }

    /**
     * Calculate task progress percentage based on pole completion
     * 
     * @param object $task
     * @return float
     */
    public function calculateProgress(object $task): float
    {
        // For streetlight tasks, calculate based on pole progress
        $totalPoles = $task->poles()->count();

        if ($totalPoles === 0) {
            // Fallback to status-based progress
            $status = TaskStatus::from($task->status);
            
            return match($status) {
                TaskStatus::PENDING => 0.0,
                TaskStatus::IN_PROGRESS => 50.0,
                TaskStatus::BLOCKED => 50.0,
                TaskStatus::COMPLETED => 100.0,
            };
        }

        // Calculate based on installation status
        $installedPoles = $task->poles()->where('isInstallationDone', true)->count();
        
        return ($installedPoles / $totalPoles) * 100;
    }

    /**
     * Get progress metrics for streetlight tasks
     * 
     * @param object $task
     * @return array
     */
    public function getProgressMetrics(object $task): array
    {
        $totalPoles = $task->poles()->count();
        $surveyedPoles = $task->poles()->where('isSurveyDone', true)->count();
        $installedPoles = $task->poles()->where('isInstallationDone', true)->count();

        return [
            'task_id' => $task->id,
            'task_name' => $task->activity ?? 'Streetlight Task',
            'status' => $task->status,
            'progress_percentage' => $this->calculateProgress($task),
            'total_poles' => $totalPoles,
            'surveyed_poles' => $surveyedPoles,
            'installed_poles' => $installedPoles,
            'remaining_poles' => $totalPoles - $installedPoles,
            'survey_percentage' => $totalPoles > 0 ? ($surveyedPoles / $totalPoles) * 100 : 0,
            'installation_percentage' => $totalPoles > 0 ? ($installedPoles / $totalPoles) * 100 : 0,
            'start_date' => $task->start_date,
            'end_date' => $task->end_date,
            'is_overdue' => $task->end_date && now()->greaterThan($task->end_date) 
                && $task->status !== TaskStatus::COMPLETED->value,
        ];
    }

    /**
     * Get required fields for streetlight tasks
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
            'activity' => $data['activity'] ?? 'Survey',
            'status' => $data['status'] ?? TaskStatus::PENDING->value,
            'start_date' => $data['start_date'] ?? now(),
            'end_date' => $data['end_date'] ?? null,
            'materials_consumed' => $data['materials_consumed'] ?? null,
            'billed' => $data['billed'] ?? false,
        ];
    }

    /**
     * Get task type identifier
     * 
     * @return string
     */
    public function getTaskType(): string
    {
        return 'streetlight';
    }
}
