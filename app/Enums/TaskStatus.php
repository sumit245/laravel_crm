<?php

namespace App\Enums;

/**
 * Task Status Enumeration
 * 
 * Defines all possible task statuses and their transitions
 */
enum TaskStatus: string
{
    case PENDING = 'Pending';
    case IN_PROGRESS = 'In Progress';
    case BLOCKED = 'Blocked';
    case COMPLETED = 'Completed';

    /**
     * Get status label
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Get status color for UI
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::IN_PROGRESS => 'info',
            self::BLOCKED => 'danger',
            self::COMPLETED => 'success',
        };
    }

    /**
     * Get allowed transitions from current status
     */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::PENDING => [self::IN_PROGRESS],
            self::IN_PROGRESS => [self::COMPLETED, self::BLOCKED],
            self::BLOCKED => [self::IN_PROGRESS],
            self::COMPLETED => [],
        };
    }

    /**
     * Check if transition to target status is allowed
     */
    public function canTransitionTo(self $targetStatus): bool
    {
        return in_array($targetStatus, $this->allowedTransitions());
    }

    /**
     * Check if status is terminal (no further transitions)
     */
    public function isTerminal(): bool
    {
        return empty($this->allowedTransitions());
    }

    /**
     * Check if status indicates active work
     */
    public function isActive(): bool
    {
        return $this === self::IN_PROGRESS;
    }

    /**
     * Get all status options for dropdowns
     */
    public static function options(): array
    {
        return array_reduce(self::cases(), function ($carry, $case) {
            $carry[$case->value] = $case->label();
            return $carry;
        }, []);
    }
}
