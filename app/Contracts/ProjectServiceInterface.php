<?php

namespace App\Contracts;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

/**
 * Project Service Interface
 * 
 * Defines contract for project business logic operations
 */
interface ProjectServiceInterface extends ServiceInterface
{
    /**
     * Create a new project
     *
     * @param array $data
     * @return Project
     */
    public function createProject(array $data): Project;

    /**
     * Update an existing project
     *
     * @param int $projectId
     * @param array $data
     * @return bool
     */
    public function updateProject(int $projectId, array $data): bool;

    /**
     * Delete a project
     *
     * @param int $projectId
     * @return bool
     */
    public function deleteProject(int $projectId): bool;

    /**
     * Get project with all related data
     *
     * @param int $projectId
     * @return Project|null
     */
    public function getProjectWithRelations(int $projectId): ?Project;

    /**
     * Assign staff to project
     *
     * @param int $projectId
     * @param int $userId
     * @param string $role
     * @return bool
     */
    public function assignStaffToProject(int $projectId, int $userId, string $role): bool;

    /**
     * Remove staff from project
     *
     * @param int $projectId
     * @param int $userId
     * @return bool
     */
    public function removeStaffFromProject(int $projectId, int $userId): bool;

    /**
     * Get projects accessible by user
     *
     * @param int $userId
     * @param int $userRole
     * @return Collection
     */
    public function getProjectsForUser(int $userId, int $userRole): Collection;

    /**
     * Get project statistics
     *
     * @param int $projectId
     * @return array
     */
    public function getProjectStatistics(int $projectId): array;
}
