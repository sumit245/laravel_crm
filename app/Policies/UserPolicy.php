<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\UserRole;

/**
 * User Policy
 * 
 * Authorization logic for user operations
 */
class UserPolicy
{
    /**
     * Determine if user can view any users
     */
    public function viewAny(User $user): bool
    {
        $role = UserRole::fromValue($user->role);
        
        // Admin, Project Manager, and HR Manager can view users
        return in_array($role, [
            UserRole::ADMIN,
            UserRole::PROJECT_MANAGER,
            UserRole::HR_MANAGER
        ]);
    }

    /**
     * Determine if user can view the specific user
     */
    public function view(User $user, User $targetUser): bool
    {
        $role = UserRole::fromValue($user->role);

        // Users can view themselves
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Admin can view all users
        if ($role->isAdmin()) {
            return true;
        }

        // Project Manager can view their team members
        if ($role === UserRole::PROJECT_MANAGER) {
            return $targetUser->manager_id === $user->id ||
                   $targetUser->project_id === $user->project_id;
        }

        // HR Manager can view all users
        if ($role === UserRole::HR_MANAGER) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can create users
     */
    public function create(User $user): bool
    {
        $role = UserRole::fromValue($user->role);
        
        // Admin and HR Manager can create users
        return in_array($role, [UserRole::ADMIN, UserRole::HR_MANAGER]);
    }

    /**
     * Determine if user can update the specific user
     */
    public function update(User $user, User $targetUser): bool
    {
        $role = UserRole::fromValue($user->role);

        // Users can update themselves (limited fields)
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Admin can update all users
        if ($role->isAdmin()) {
            return true;
        }

        // HR Manager can update users
        if ($role === UserRole::HR_MANAGER) {
            return true;
        }

        // Project Manager can update their team members
        if ($role === UserRole::PROJECT_MANAGER) {
            return $targetUser->manager_id === $user->id;
        }

        return false;
    }

    /**
     * Determine if user can delete the specific user
     */
    public function delete(User $user, User $targetUser): bool
    {
        $role = UserRole::fromValue($user->role);
        
        // Only admin can delete users
        // Cannot delete yourself
        return $role->isAdmin() && $user->id !== $targetUser->id;
    }

    /**
     * Determine if user can disable login for target user
     */
    public function disableLogin(User $user, User $targetUser): bool
    {
        $role = UserRole::fromValue($user->role);
        
        // Admin can disable any user except themselves
        if ($role->isAdmin() && $user->id !== $targetUser->id) {
            return true;
        }

        // HR Manager can disable users
        if ($role === UserRole::HR_MANAGER && $user->id !== $targetUser->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can change password for target user
     */
    public function changePassword(User $user, User $targetUser): bool
    {
        // Users can change their own password
        if ($user->id === $targetUser->id) {
            return true;
        }

        $role = UserRole::fromValue($user->role);
        
        // Admin can change any password
        return $role->isAdmin();
    }
}
