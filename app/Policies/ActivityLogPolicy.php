<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogPolicy
{
    /**
     * Determine whether the user can view any activity logs.
     */
    public function viewAny(User $user): bool
    {
        $role = UserRole::fromValue($user->role);

        // Only admin and reporting/vertical heads see global logs
        return in_array($role, [
            UserRole::ADMIN,
            UserRole::REPORTING_MANAGER,
            UserRole::VERTICAL_HEAD,
        ], true);
    }

    /**
     * Determine whether the user can view a specific activity log entry.
     */
    public function view(User $user, ActivityLog $log): bool
    {
        return $this->viewAny($user);
    }
}

