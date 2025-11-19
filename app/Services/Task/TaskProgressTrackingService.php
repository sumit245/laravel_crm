<?php

namespace App\Services\Task;

use App\Contracts\TaskRepositoryInterface;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\StreetlightTask;
use App\Services\BaseService;
use Carbon\Carbon;

/**
 * Task Progress Tracking Service
 * 
 * Monitors and reports on task progress and performance metrics
 */
class TaskProgressTrackingService extends BaseService
{
    /**
     * Create new TaskProgressTrackingService instance
     * 
     * @param TaskRepositoryInterface $repository
     */
    public function __construct(
        protected TaskRepositoryInterface $repository
    ) {
    }

    /**
     * Track survey progress for streetlight projects
     * 
     * @param int $taskId
     * @param array $surveyData
     * @return array
     */
    public function trackSurveyProgress(int $taskId, array $surveyData): array
    {
        $task = StreetlightTask::with('poles')->find($taskId);

        if (!$task) {
            throw new \InvalidArgumentException("Streetlight task with ID {$taskId} not found");
        }

        $totalPoles = $task->poles()->count();
        $surveyedPoles = $task->poles()->where('isSurveyDone', true)->count();

        $progress = $totalPoles > 0 ? ($surveyedPoles / $totalPoles) * 100 : 0;

        return [
            'total_poles' => $totalPoles,
            'surveyed_poles' => $surveyedPoles,
            'remaining_poles' => $totalPoles - $surveyedPoles,
            'progress_percentage' => round($progress, 2),
        ];
    }

    /**
     * Track installation progress
     * 
     * @param int $taskId
     * @param string $taskType 'rooftop' or 'streetlight'
     * @return array
     */
    public function trackInstallationProgress(int $taskId, string $taskType = 'rooftop'): array
    {
        if ($taskType === 'streetlight') {
            $task = StreetlightTask::with('poles')->find($taskId);
            
            if (!$task) {
                throw new \InvalidArgumentException("Streetlight task with ID {$taskId} not found");
            }

            $totalPoles = $task->poles()->count();
            $installedPoles = $task->poles()->where('isInstallationDone', true)->count();

            $progress = $totalPoles > 0 ? ($installedPoles / $totalPoles) * 100 : 0;

            return [
                'total_poles' => $totalPoles,
                'installed_poles' => $installedPoles,
                'remaining_poles' => $totalPoles - $installedPoles,
                'progress_percentage' => round($progress, 2),
            ];
        }

        // Rooftop tasks
        $task = Task::with('site')->find($taskId);
        
        if (!$task) {
            throw new \InvalidArgumentException("Task with ID {$taskId} not found");
        }

        $statusProgress = match($task->status) {
            TaskStatus::PENDING->value => 0,
            TaskStatus::IN_PROGRESS->value => 50,
            TaskStatus::BLOCKED->value => 50,
            TaskStatus::COMPLETED->value => 100,
            default => 0,
        };

        return [
            'status' => $task->status,
            'progress_percentage' => $statusProgress,
            'start_date' => $task->start_date,
            'end_date' => $task->end_date,
        ];
    }

    /**
     * Calculate task completion percentage
     * 
     * @param object $task
     * @return float
     */
    public function calculateTaskCompletion(object $task): float
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
     * Generate progress report for task
     * 
     * @param int $taskId
     * @param string $taskType
     * @return array
     */
    public function generateProgressReport(int $taskId, string $taskType = 'rooftop'): array
    {
        $task = $taskType === 'streetlight' 
            ? StreetlightTask::with(['engineer', 'vendor', 'project', 'poles'])->find($taskId)
            : Task::with(['engineer', 'vendor', 'project', 'site'])->find($taskId);

        if (!$task) {
            throw new \InvalidArgumentException("Task with ID {$taskId} not found");
        }

        $completion = $this->calculateTaskCompletion($task);
        $delayDays = $this->calculateDelayDays($task);

        $report = [
            'task_id' => $task->id,
            'task_name' => $task->task_name ?? $task->activity,
            'status' => $task->status,
            'completion_percentage' => $completion,
            'assigned_engineer' => $task->engineer?->name,
            'assigned_vendor' => $task->vendor?->name,
            'start_date' => $task->start_date,
            'end_date' => $task->end_date,
            'is_delayed' => $delayDays > 0,
            'delay_days' => $delayDays,
        ];

        // Add type-specific metrics
        if ($taskType === 'streetlight' && method_exists($task, 'poles')) {
            $report['pole_metrics'] = $this->trackInstallationProgress($taskId, 'streetlight');
        }

        return $report;
    }

    /**
     * Identify delayed tasks
     * 
     * @param int|null $projectId
     * @return array
     */
    public function identifyDelayedTasks(?int $projectId = null): array
    {
        $overdueTasks = $this->repository->getOverdueTasks($projectId);

        return $overdueTasks->map(function ($task) {
            return [
                'task_id' => $task->id,
                'task_name' => $task->task_name ?? $task->activity,
                'status' => $task->status,
                'end_date' => $task->end_date,
                'delay_days' => $this->calculateDelayDays($task),
                'engineer' => $task->engineer?->name,
                'project' => $task->project?->project_name,
            ];
        })->toArray();
    }

    /**
     * Calculate estimated completion date
     * 
     * @param object $task
     * @return string|null
     */
    public function calculateEstimatedCompletion(object $task): ?string
    {
        if (!$task->start_date || !$task->end_date) {
            return null;
        }

        $start = Carbon::parse($task->start_date);
        $plannedEnd = Carbon::parse($task->end_date);
        $now = Carbon::now();

        if ($task->status === TaskStatus::COMPLETED->value) {
            return $task->end_date;
        }

        $plannedDuration = $start->diffInDays($plannedEnd);
        $elapsedDays = $start->diffInDays($now);

        if ($elapsedDays <= 0 || $plannedDuration <= 0) {
            return $task->end_date;
        }

        $completion = $this->calculateTaskCompletion($task);
        
        if ($completion <= 0) {
            return $task->end_date;
        }

        $estimatedTotalDays = ($elapsedDays / $completion) * 100;
        $estimatedCompletion = $start->copy()->addDays((int)$estimatedTotalDays);

        return $estimatedCompletion->toDateString();
    }

    /**
     * Calculate delay days for task
     * 
     * @param object $task
     * @return int
     */
    protected function calculateDelayDays(object $task): int
    {
        if (!$task->end_date) {
            return 0;
        }

        if ($task->status === TaskStatus::COMPLETED->value) {
            return 0;
        }

        $endDate = Carbon::parse($task->end_date);
        $now = Carbon::now();

        if ($now->greaterThan($endDate)) {
            return $now->diffInDays($endDate);
        }

        return 0;
    }
}
