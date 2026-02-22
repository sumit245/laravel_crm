<?php

namespace App\Contracts;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Contract for task data access. Defines methods for querying tasks with staff and site
 * relationships, filtering by status, and aggregating completion statistics.
 *
 * Data Flow:
 *   TaskService → TaskRepository (implements this) → Eloquent queries → Task data with
 *   relationships
 *
 * @business-domain Field Operations
 * @package App\Contracts
 */
interface TaskRepositoryInterface extends RepositoryInterface
{
    /**
     * Find tasks by project
     * 
     * @param int $projectId
     * @param array $with Relationships to eager load
     * @return Collection
     */
    public function findByProject(int $projectId, array $with = []): Collection;

    /**
     * Find tasks query by project
     * 
     * @param int $projectId
     * @return Builder
     */
    public function findQueryByProject(int $projectId): Builder;

    /**
     * Find tasks assigned to specific engineer
     * 
     * @param int $engineerId
     * @param array $with Relationships to eager load
     * @return Collection
     */
    public function findByEngineer(int $engineerId, array $with = []): Collection;

    /**
     * Find tasks assigned to specific vendor
     * 
     * @param int $vendorId
     * @param array $with Relationships to eager load
     * @return Collection
     */
    public function findByVendor(int $vendorId, array $with = []): Collection;

    /**
     * Find tasks by status
     * 
     * @param TaskStatus $status
     * @param array $with Relationships to eager load
     * @return Collection
     */
    public function findByStatus(TaskStatus $status, array $with = []): Collection;

    /**
     * Find tasks within date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @param array $with Relationships to eager load
     * @return Collection
     */
    public function findInDateRange(string $startDate, string $endDate, array $with = []): Collection;

    /**
     * Get tasks with material consumption relationships
     * 
     * @param int|null $projectId
     * @return Collection
     */
    public function getTasksWithMaterials(?int $projectId = null): Collection;

    /**
     * Get tasks by type (rooftop vs streetlight)
     * 
     * @param string $taskType 'rooftop' or 'streetlight'
     * @param array $with Relationships to eager load
     * @return Collection
     */
    public function getTasksByType(string $taskType, array $with = []): Collection;

    /**
     * Get pending tasks for user based on role
     * 
     * @param int $userId
     * @param string $userRole
     * @return Collection
     */
    public function getPendingTasksForUser(int $userId, string $userRole): Collection;

    /**
     * Find task with full relationships loaded
     * 
     * @param int $id
     * @return Model|null
     */
    public function findWithFullRelations(int $id): ?Model;

    /**
     * Get overdue tasks
     * 
     * @param int|null $projectId
     * @return Collection
     */
    public function getOverdueTasks(?int $projectId = null): Collection;

    /**
     * Get task counts by status for a project
     * 
     * @param int $projectId
     * @return array
     */
    public function getTaskCountsByStatus(int $projectId): array;
}
