<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Project Repository Interface
 * 
 * Defines contract for project data access operations
 */
interface ProjectRepositoryInterface extends RepositoryInterface
{
    /**
     * Find project by work order number
     *
     * @param string $workOrderNumber
     * @return Model|null
     */
    public function findByWorkOrderNumber(string $workOrderNumber): ?Model;

    /**
     * Get all projects for a specific user based on their role
     *
     * @param int $userId
     * @param int $userRole
     * @return Collection
     */
    public function getAllForUser(int $userId, int $userRole): Collection;

    /**
     * Get projects by type
     *
     * @param int $projectType
     * @return Collection
     */
    public function getByType(int $projectType): Collection;

    /**
     * Get projects by state
     *
     * @param int $stateId
     * @return Collection
     */
    public function getByState(int $stateId): Collection;

    /**
     * Get projects within date range
     *
     * @param array $dateRange
     * @return Collection
     */
    public function getProjectsInDateRange(array $dateRange): Collection;
}
