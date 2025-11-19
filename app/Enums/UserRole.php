<?php

namespace App\Enums;

/**
 * User Role Enumeration
 * 
 * Defines all user roles in the system with their corresponding values and labels.
 * Replaces magic numbers throughout the codebase for better maintainability.
 */
enum UserRole: int
{
    case ADMIN = 0;
    case SITE_ENGINEER = 1;
    case PROJECT_MANAGER = 2;
    case VENDOR = 3;
    case STORE_INCHARGE = 4;
    case HR_MANAGER = 5;
    case REPORTING_MANAGER = 6;
    case VERTICAL_HEAD = 7;
    case CLIENT = 10;

    /**
     * Get human-readable label for the role
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::SITE_ENGINEER => 'Site Engineer',
            self::PROJECT_MANAGER => 'Project Manager',
            self::VENDOR => 'Vendor',
            self::STORE_INCHARGE => 'Store Incharge',
            self::HR_MANAGER => 'HR Manager',
            self::REPORTING_MANAGER => 'Reporting Manager',
            self::VERTICAL_HEAD => 'Vertical Head',
            self::CLIENT => 'Client',
        };
    }

    /**
     * Get role description
     */
    public function description(): string
    {
        return match($this) {
            self::ADMIN => 'Full system access with administrative privileges',
            self::SITE_ENGINEER => 'Field engineers managing on-ground operations',
            self::PROJECT_MANAGER => 'Oversees projects and manages site engineers and vendors',
            self::VENDOR => 'External contractors performing installation work',
            self::STORE_INCHARGE => 'Manages inventory and material dispatch',
            self::HR_MANAGER => 'Handles candidate recruitment and HR operations',
            self::REPORTING_MANAGER => 'Reporting Manager',
            self::VERTICAL_HEAD => 'Vertical Head',
            self::CLIENT => 'External stakeholders with limited view access',
        };
    }

    /**
     * Check if role has administrative privileges
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if role can manage projects
     */
    public function canManageProjects(): bool
    {
        return in_array($this, [self::ADMIN, self::PROJECT_MANAGER]);
    }

    /**
     * Check if role can manage inventory
     */
    public function canManageInventory(): bool
    {
        return in_array($this, [self::ADMIN, self::STORE_INCHARGE]);
    }

    /**
     * Check if role is field-based
     */
    public function isFieldRole(): bool
    {
        return in_array($this, [self::SITE_ENGINEER, self::VENDOR]);
    }

    /**
     * Get all role values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all roles as key-value pairs for dropdowns
     */
    public static function options(): array
    {
        return array_reduce(self::cases(), function ($carry, $case) {
            $carry[$case->value] = $case->label();
            return $carry;
        }, []);
    }

    /**
     * Create enum from integer value
     */
    public static function fromValue(int $value): ?self
    {
        return self::tryFrom($value);
    }
}
