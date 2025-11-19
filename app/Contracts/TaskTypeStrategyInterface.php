<?php

namespace App\Contracts;

/**
 * Task Type Strategy Interface
 * 
 * Defines contract for handling different task types (rooftop vs streetlight)
 */
interface TaskTypeStrategyInterface
{
    /**
     * Get the task model class for this strategy
     * 
     * @return string
     */
    public function getTaskModel(): string;

    /**
     * Validate task data specific to this type
     * 
     * @param array $data
     * @return array Validation errors if any
     */
    public function validateTaskData(array $data): array;

    /**
     * Calculate task progress percentage
     * 
     * @param object $task
     * @return float
     */
    public function calculateProgress(object $task): float;

    /**
     * Get progress metrics for this task type
     * 
     * @param object $task
     * @return array
     */
    public function getProgressMetrics(object $task): array;

    /**
     * Get required fields for this task type
     * 
     * @return array
     */
    public function getRequiredFields(): array;

    /**
     * Prepare task data for storage
     * 
     * @param array $data
     * @return array
     */
    public function prepareTaskData(array $data): array;

    /**
     * Get task type identifier
     * 
     * @return string
     */
    public function getTaskType(): string;
}
