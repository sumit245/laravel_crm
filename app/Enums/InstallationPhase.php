<?php

namespace App\Enums;

/**
 * Defines the phases of streetlight pole installation: Survey (initial site visit with GPS
 * capture), Installation (physical mounting of equipment), and Commissioning (power-on and RMS
 * registration).
 *
 * Data Flow:
 *   Pole goes through phases: Survey → Installation → Commissioning → Each phase updates
 *   different fields on the Pole record
 *
 * @business-domain Field Operations
 * @package App\Enums
 */
enum InstallationPhase: string
{
    case NOT_STARTED = 'Not Started';
    case IN_PROGRESS = 'In Progress';
    case COMPLETED = 'Completed';

    /**
     * Get phase label
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Get phase color for UI
     */
    public function color(): string
    {
        return match($this) {
            self::NOT_STARTED => 'secondary',
            self::IN_PROGRESS => 'warning',
            self::COMPLETED => 'success',
        };
    }

    /**
     * Get completion percentage
     */
    public function percentage(): int
    {
        return match($this) {
            self::NOT_STARTED => 0,
            self::IN_PROGRESS => 50,
            self::COMPLETED => 100,
        };
    }

    /**
     * Check if phase is complete
     */
    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Get all phase options for dropdowns
     */
    public static function options(): array
    {
        return array_reduce(self::cases(), function ($carry, $case) {
            $carry[$case->value] = $case->label();
            return $carry;
        }, []);
    }
}
