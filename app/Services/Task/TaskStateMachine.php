<?php

namespace App\Services\Task;

use App\Contracts\TaskStateMachineInterface;
use App\Enums\TaskStatus;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Task State Machine
 * 
 * Manages task state transitions with validation and logging
 */
class TaskStateMachine implements TaskStateMachineInterface
{
    /**
     * Validate if transition is allowed
     * 
     * @param TaskStatus $currentStatus
     * @param TaskStatus $targetStatus
     * @return bool
     */
    public function validateTransition(TaskStatus $currentStatus, TaskStatus $targetStatus): bool
    {
        return $currentStatus->canTransitionTo($targetStatus);
    }

    /**
     * Execute state transition with validation and logging
     * 
     * @param object $task
     * @param TaskStatus $targetStatus
     * @param array $additionalData
     * @return object
     * @throws InvalidArgumentException
     */
    public function executeTransition(object $task, TaskStatus $targetStatus, array $additionalData = []): object
    {
        $currentStatus = TaskStatus::from($task->status);

        // Validate transition
        if (!$this->validateTransition($currentStatus, $targetStatus)) {
            throw new InvalidArgumentException(
                "Invalid task status transition from {$currentStatus->value} to {$targetStatus->value}"
            );
        }

        // Validate required data for specific transitions
        $this->validateTransitionRequirements($currentStatus, $targetStatus, $additionalData);

        // Store previous status for logging
        $previousStatus = $currentStatus;

        // Update task status
        $task->status = $targetStatus->value;

        // Add additional data if provided
        if (isset($additionalData['progress_notes'])) {
            $task->description = $additionalData['progress_notes'];
        }

        if (isset($additionalData['image'])) {
            $task->image = $additionalData['image'];
        }

        if (isset($additionalData['approved_by'])) {
            $task->approved_by = $additionalData['approved_by'];
        }

        // Save the task
        $task->save();

        // Log state change
        $this->logStateChange(
            $task->id,
            $previousStatus,
            $targetStatus,
            auth()->id() ?? 0,
            $additionalData['notes'] ?? null
        );

        return $task;
    }

    /**
     * Get available transitions from current status
     * 
     * @param TaskStatus $currentStatus
     * @return array
     */
    public function getAvailableTransitions(TaskStatus $currentStatus): array
    {
        return $currentStatus->allowedTransitions();
    }

    /**
     * Check if transition requires approval
     * 
     * @param TaskStatus $currentStatus
     * @param TaskStatus $targetStatus
     * @return bool
     */
    public function requiresApproval(TaskStatus $currentStatus, TaskStatus $targetStatus): bool
    {
        // Completion requires manager approval
        if ($targetStatus === TaskStatus::COMPLETED) {
            return true;
        }

        return false;
    }

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
    ): void {
        Log::info('Task Status Changed', [
            'task_id' => $taskId,
            'from_status' => $fromStatus->value,
            'to_status' => $toStatus->value,
            'user_id' => $userId,
            'notes' => $notes,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Validate requirements for specific transitions
     * 
     * @param TaskStatus $currentStatus
     * @param TaskStatus $targetStatus
     * @param array $additionalData
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateTransitionRequirements(
        TaskStatus $currentStatus,
        TaskStatus $targetStatus,
        array $additionalData
    ): void {
        // Completing a task requires progress notes
        if ($targetStatus === TaskStatus::COMPLETED) {
            if (empty($additionalData['progress_notes']) && empty($additionalData['description'])) {
                throw new InvalidArgumentException(
                    'Progress notes are required when completing a task'
                );
            }
        }

        // Blocking a task requires blocker description
        if ($targetStatus === TaskStatus::BLOCKED) {
            if (empty($additionalData['blocker_description']) && empty($additionalData['description'])) {
                throw new InvalidArgumentException(
                    'Blocker description is required when marking task as blocked'
                );
            }
        }
    }
}
