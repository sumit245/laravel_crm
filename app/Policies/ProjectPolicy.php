<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;
use App\Enums\UserRole;

/**
 * Project Policy
 * 
 * Authorization logic for project operations
 */
class ProjectPolicy
{
    /**
     * Determine if user can view any projects
     */
    public function viewAny(User $user): bool
    {
        $role = UserRole::fromValue($user->role);
        
        // All authenticated users can view projects they have access to
        return true;
    }

    /**
     * Determine if user can view the project
     */
    public function view(User $user, Project $project): bool
    {
        $role = UserRole::fromValue($user->role);

        // Admin can view all projects
        if ($role->isAdmin()) {
            return true;
        }

        // Check if user is assigned to this project
        return $user->projects->contains($project->id) || 
               $user->project_id === $project->id;
    }

    /**
     * Determine if user can create projects
     */
    public function create(User $user): bool
    {
        $role = UserRole::fromValue($user->role);
        
        return $role->canManageProjects();
    }

    /**
     * Determine if user can update the project
     */
    public function update(User $user, Project $project): bool
    {
        $role = UserRole::fromValue($user->role);

        // Admin can update any project
        if ($role->isAdmin()) {
            return true;
        }

        // Project Manager can update their assigned projects
        if ($role === UserRole::PROJECT_MANAGER) {
            return $user->projects->contains($project->id);
        }

        return false;
    }

    /**
     * Determine if user can delete the project
     */
    public function delete(User $user, Project $project): bool
    {
        $role = UserRole::fromValue($user->role);
        
        // Only admin can delete projects
        return $role->isAdmin();
    }

    /**
     * Determine if user can assign staff to project
     */
    public function assignStaff(User $user, Project $project): bool
    {
        $role = UserRole::fromValue($user->role);

        // Admin can assign staff to any project
        if ($role->isAdmin()) {
            return true;
        }

        // Project Manager can assign staff to their projects
        if ($role === UserRole::PROJECT_MANAGER) {
            return $user->projects->contains($project->id);
        }

        return false;
    }

    /**
     * Determine if user can view project statistics
     */
    public function viewStatistics(User $user, Project $project): bool
    {
        // Same as view permission
        return $this->view($user, $project);
    }

    /**
     * Determine if user can remove staff from project
     */
    public function removeStaff(User $user, Project $project): bool
    {
        $role = UserRole::fromValue($user->role);

        // Admin can remove any staff
        if ($role->isAdmin()) {
            return true;
        }

        // Project Manager can remove staff from their projects
        if ($role === UserRole::PROJECT_MANAGER) {
            return $user->projects->contains($project->id);
        }

        return false;
    }
}
