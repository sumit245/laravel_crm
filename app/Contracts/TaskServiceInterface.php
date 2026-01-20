<?php

namespace App\Contracts;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * Task Service Interface
 * 
 * Defines contract for task business logic operations
 */
interface TaskServiceInterface extends ServiceInterface
{
    /**
     * Create a new task
     * 
     * @param array $data
     * @return Model
     */
    public function createTask(array $data): Model;

    /**
     * Update existing task
     * 
     * @param int $taskId
     * @param array $data
     * @return Model
     */
    public function updateTask(int $taskId, array $data): Model;

    /**
     * Assign engineer to task
     * 
     * @param int $taskId
     * @param int $engineerId
     * @return Model
     */
    public function assignEngineer(int $taskId, int $engineerId): Model;

    /**
     * Assign vendor to task
     * 
     * @param int $taskId
     * @param int $vendorId
     * @return Model
     */
    public function assignVendor(int $taskId, int $vendorId): Model;

    /**
     * Update task status with state machine validation
     * 
     * @param int $taskId
     * @param TaskStatus $newStatus
     * @param array $additionalData
     * @return Model
     */
    public function updateTaskStatus(int $taskId, TaskStatus $newStatus, array $additionalData = []): Model;

    /**
     * Record task progress
     * 
     * @param int $taskId
     * @param array $progressData
     * @return Model
     */
    public function recordProgress(int $taskId, array $progressData): Model;

    /**
     * Cancel task
     * 
     * @param int $taskId
     * @param string $reason
     * @param int $cancelledBy
     * @return Model
     */
    public function cancelTask(int $taskId, string $reason, int $cancelledBy): Model;

    /**
     * Reassign task to different engineer
     * 
     * @param int $taskId
     * @param int $newEngineerId
     * @param string|null $reason
     * @return Model
     */
    public function reassignTask(int $taskId, int $newEngineerId, ?string $reason = null): Model;

    /**
     * Escalate blocked task to manager
     * 
     * @param int $taskId
     * @param string $escalationReason
     * @return Model
     */
    public function escalateTask(int $taskId, string $escalationReason): Model;

    /**
     * Delete task
     * 
     * @param int $taskId
     * @return bool
     */
    public function deleteTask(int $taskId): bool;

    /**
     * Create bulk tasks for multiple sites
     * 
     * @param int $projectId
     * @param array $siteIds
     * @param array $taskData
     * @param int $createdBy
     * @return void
     */
    public function createBulkTasks(int $projectId, array $siteIds, array $taskData, int $createdBy): void;

    /**
     * Get tasks by project
     * 
     * @param int $projectId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTasksByProject(int $projectId);

    /**
     * Get task details by ID
     * 
     * @param int $taskId
     * @param int|null $projectType
     * @return array
     */
    public function getTaskDetails(int $taskId, ?int $projectType = null): array;

    /**
     * Find task by ID
     * 
     * @param int $taskId
     * @return Model|null
     */
    public function findById(int $taskId): ?Model;

    /**
     * Get available engineers for a project
     * 
     * @param int $projectId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableEngineers(int $projectId);

    /**
     * Get available vendors for a project
     * 
     * @param int $projectId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableVendors(int $projectId);

    /**
     * Get available project managers for a project
     * 
     * @param int $projectId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableManagers(int $projectId);

    /**
     * Get available sites for a project
     * 
     * @param int $projectId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableSites(int $projectId);
}
