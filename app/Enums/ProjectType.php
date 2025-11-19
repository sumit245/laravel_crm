<?php

namespace App\Enums;

/**
 * Project Type Enumeration
 * 
 * Defines different types of projects in the system
 */
enum ProjectType: int
{
    case ROOFTOP_SOLAR = 0;
    case STREETLIGHT = 1;

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::ROOFTOP_SOLAR => 'Rooftop Solar Project',
            self::STREETLIGHT => 'Streetlight Project',
        };
    }

    /**
     * Get project type description
     */
    public function description(): string
    {
        return match($this) {
            self::ROOFTOP_SOLAR => 'Solar panel installations on rooftops',
            self::STREETLIGHT => 'Solar streetlight installations in rural/urban areas',
        };
    }

    /**
     * Get the inventory model class for this project type
     */
    public function inventoryModelClass(): string
    {
        return match($this) {
            self::ROOFTOP_SOLAR => \App\Models\Inventory::class,
            self::STREETLIGHT => \App\Models\InventroyStreetLightModel::class,
        };
    }

    /**
     * Get the site model class for this project type
     */
    public function siteModelClass(): string
    {
        return match($this) {
            self::ROOFTOP_SOLAR => \App\Models\Site::class,
            self::STREETLIGHT => \App\Models\Streetlight::class,
        };
    }

    /**
     * Get the task model class for this project type
     */
    public function taskModelClass(): string
    {
        return match($this) {
            self::ROOFTOP_SOLAR => \App\Models\Task::class,
            self::STREETLIGHT => \App\Models\StreetlightTask::class,
        };
    }

    /**
     * Check if agreement details are required
     */
    public function requiresAgreement(): bool
    {
        return $this === self::STREETLIGHT;
    }

    /**
     * Get all project type options for dropdowns
     */
    public static function options(): array
    {
        return array_reduce(self::cases(), function ($carry, $case) {
            $carry[$case->value] = $case->label();
            return $carry;
        }, []);
    }
}
