<?php

namespace App\Contracts;

use App\Enums\TaskStatus;

/**
 * Task State Machine Interface
 * 
 * Defines contract for task state transition management
 */
interface TaskStateMachineInterface
{
    /**
     * Validate if transition is allowed
     * 
     * @param TaskStatus $currentStatus
     * @param TaskStatus $targetStatus
     * @return bool
     */
    public function validateTransition(TaskStatus $currentStatus, TaskStatus $targetStatus): bool;

    /**
     * Execute state transition with validation and logging
     * 
     * @param object $task
     * @param TaskStatus $targetStatus
     * @param array $additionalData
     * @return object
     */
    public function executeTransition(object $task, TaskStatus $targetStatus, array $additionalData = []): object;

    /**
     * Get available transitions from current status
     * 
     * @param TaskStatus $currentStatus
     * @return array
     */
    public function getAvailableTransitions(TaskStatus $currentStatus): array;

    /**
     * Check if transition requires approval
     * 
     * @param TaskStatus $currentStatus
     * @param TaskStatus $targetStatus
     * @return bool
     */
    public function requiresApproval(TaskStatus $currentStatus, TaskStatus $targetStatus): bool;

    /**
     * Log state change history
     * 
     * @param int $taskId
     * @param TaskStatus $fromStatus
     * @param TaskStatus $toStatus
     * @param int $userId
     * @param string|null $notes
     * @return void
     */
    public function logStateChange(
        int $taskId,
        TaskStatus $fromStatus,
        TaskStatus $toStatus,
        int $userId,
        ?string $notes = null
    ): void;
}
