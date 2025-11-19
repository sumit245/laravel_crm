<?php

namespace App\Repositories\Task;

use App\Contracts\TaskRepositoryInterface;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\StreetlightTask;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Task Repository
 * 
 * Handles all task data access operations for both Task and StreetlightTask models
 */
class TaskRepository extends BaseRepository implements TaskRepositoryInterface
{
    /**
     * Create new TaskRepository instance
     * 
     * @param Task $model
     */
    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    /**
     * Find tasks by project
     * 
     * @param int $projectId
     * @param array $with Relationships to eager load
     * @return Collection
     */
    public function findByProject(int $projectId, array $with = []): Collection
    {
        $defaultWith = ['engineer', 'vendor', 'manager', 'project', 'site'];
        $relations = !empty($with) ? $with : $defaultWith;

        return $this->model
            ->with($relations)
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find tasks assigned to specific engineer
     * 
     * @param int $engineerId
     * @param array $with Relationships to eager load
     * @return Collection
     */
    public function findByEngineer(int $engineerId, array $with = []): Collection
    {
        $defaultWith = ['vendor', 'manager', 'project', 'site'];
        $relations = !empty($with) ? $with : $defaultWith;

        return $this->model
            ->with($relations)
            ->where('engineer_id', $engineerId)
            ->orderBy('start_date', 'asc')
            ->get();
    }

    /**
     * Find tasks assigned to specific vendor
     * 
     * @param int $vendorId
     * @param array $with Relationships to eager load
     * @return Collection
     */
    public function findByVendor(int $vendorId, array $with = []): Collection
    {
        $defaultWith = ['engineer', 'manager', 'project', 'site'];
        $relations = !empty($with) ? $with : $defaultWith;

        return $this->model
            ->with($relations)
            ->where('vendor_id', $vendorId)
            ->orderBy('start_date', 'asc')
            ->get();
    }

    /**
     * Find tasks by status
     * 
     * @param TaskStatus $status
     * @param array $with Relationships to eager load
     * @return Collection
     */
    public function findByStatus(TaskStatus $status, array $with = []): Collection
    {
        $defaultWith = ['engineer', 'vendor', 'project', 'site'];
        $relations = !empty($with) ? $with : $defaultWith;

        return $this->model
            ->with($relations)
            ->where('status', $status->value)
            ->orderBy('start_date', 'asc')
            ->get();
    }

    /**
     * Find tasks within date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @param array $with Relationships to eager load
     * @return Collection
     */
    public function findInDateRange(string $startDate, string $endDate, array $with = []): Collection
    {
        $defaultWith = ['engineer', 'vendor', 'project'];
        $relations = !empty($with) ? $with : $defaultWith;

        return $this->model
            ->with($relations)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->orderBy('start_date', 'asc')
            ->get();
    }

    /**
     * Get tasks with material consumption relationships
     * 
     * @param int|null $projectId
     * @return Collection
     */
    public function getTasksWithMaterials(?int $projectId = null): Collection
    {
        $query = $this->model
            ->with(['engineer', 'vendor', 'project', 'site'])
            ->whereNotNull('materials_consumed');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get tasks by type (rooftop vs streetlight)
     * 
     * @param string $taskType 'rooftop' or 'streetlight'
     * @param array $with Relationships to eager load
     * @return Collection
     */
    public function getTasksByType(string $taskType, array $with = []): Collection
    {
        if ($taskType === 'streetlight') {
            $defaultWith = ['engineer', 'vendor', 'manager', 'project', 'site', 'poles'];
            $relations = !empty($with) ? $with : $defaultWith;

            return StreetlightTask::with($relations)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Default to rooftop tasks
        $defaultWith = ['engineer', 'vendor', 'manager', 'project', 'site'];
        $relations = !empty($with) ? $with : $defaultWith;

        return $this->model
            ->with($relations)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get pending tasks for user based on role
     * 
     * @param int $userId
     * @param string $userRole
     * @return Collection
     */
    public function getPendingTasksForUser(int $userId, string $userRole): Collection
    {
        $query = $this->model->with(['engineer', 'vendor', 'project', 'site']);

        // Filter based on user role
        switch ($userRole) {
            case 'Site Engineer':
            case '1':
                $query->where('engineer_id', $userId);
                break;
            case 'Vendor':
            case '3':
                $query->where('vendor_id', $userId);
                break;
            case 'Project Manager':
            case '2':
                $query->where('manager_id', $userId);
                break;
            default:
                // Admin sees all tasks
                break;
        }

        return $query
            ->whereIn('status', [TaskStatus::PENDING->value, TaskStatus::IN_PROGRESS->value])
            ->orderBy('start_date', 'asc')
            ->get();
    }

    /**
     * Find task with full relationships loaded
     * 
     * @param int $id
     * @return Model|null
     */
    public function findWithFullRelations(int $id): ?Model
    {
        return $this->model
            ->with([
                'engineer',
                'vendor',
                'manager',
                'project',
                'site',
                'project.stores'
            ])
            ->find($id);
    }

    /**
     * Get overdue tasks
     * 
     * @param int|null $projectId
     * @return Collection
     */
    public function getOverdueTasks(?int $projectId = null): Collection
    {
        $query = $this->model
            ->with(['engineer', 'vendor', 'project', 'site'])
            ->where('end_date', '<', now())
            ->whereNotIn('status', [TaskStatus::COMPLETED->value]);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return $query->orderBy('end_date', 'asc')->get();
    }

    /**
     * Get task counts by status for a project
     * 
     * @param int $projectId
     * @return array
     */
    public function getTaskCountsByStatus(int $projectId): array
    {
        $counts = $this->model
            ->where('project_id', $projectId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses are present
        return [
            TaskStatus::PENDING->value => $counts[TaskStatus::PENDING->value] ?? 0,
            TaskStatus::IN_PROGRESS->value => $counts[TaskStatus::IN_PROGRESS->value] ?? 0,
            TaskStatus::BLOCKED->value => $counts[TaskStatus::BLOCKED->value] ?? 0,
            TaskStatus::COMPLETED->value => $counts[TaskStatus::COMPLETED->value] ?? 0,
        ];
    }
}
