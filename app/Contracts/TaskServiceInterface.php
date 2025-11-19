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
}
