<?php

namespace App\Policies;

use App\Models\Stores;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Auth\Access\Response;

class StorePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view stores (filtered by project assignment in controller)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Stores $stores): bool
    {
        // Users can view stores for projects they're assigned to
        $role = UserRole::fromValue($user->role);
        
        if ($role === UserRole::ADMIN) {
            return true; // Admin can view all stores
        }
        
        // Check if user is assigned to the store's project
        return $stores->project && $stores->project->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     * 
     * @param User $user
     * @param int|null $projectId If provided, checks if creating from project tab context
     * @return bool
     */
    public function create(User $user, ?int $projectId = null): bool
    {
        $role = UserRole::fromValue($user->role);
        
        // Only Admin can create stores
        if ($role !== UserRole::ADMIN) {
            return false;
        }
        
        // If projectId is provided (from project tab), ensure it's valid
        // Admin can create stores for any project from sidebar (projectId = null)
        // Admin can create stores for specific project from project tab (projectId provided)
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Stores $stores): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Stores $stores): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Stores $stores): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Stores $stores): bool
    {
        //
    }
}
