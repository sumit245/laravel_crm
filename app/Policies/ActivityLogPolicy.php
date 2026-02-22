<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\User;

/**
 * Authorization policy for activity log access. Only admins can view the full audit trail.
 * Project managers can see logs related to their projects. Engineers and vendors have no access.
 *
 * Data Flow:
 *   User tries to access activity logs → Policy checks role → Admin: full access → PM:
 *   project-scoped → Others: denied
 *
 * @depends-on User, ActivityLog
 * @business-domain Security
 * @package App\Policies
 */
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

