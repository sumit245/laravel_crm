<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Contract for site data access. Defines methods for querying sites with pole aggregates,
 * filtering by project and district, and bulk operations.
 *
 * Data Flow:
 *   SiteService → SiteRepository (implements this) → Eloquent queries with joins → Site
 *   + Pole data
 *
 * @business-domain Site Management
 * @package App\Contracts
 */
interface SiteRepositoryInterface extends RepositoryInterface
{
    /**
     * Find by project.
     *
     * @param  int  $projectId  The project identifier
     * @param  array  $with  
     * @return Collection;  Collection of results
     */
    public function findByProject(int $projectId, array $with = []): Collection;
    /**
     * Find by district.
     *
     * @param  string  $district  The district identifier or name
     * @param  array  $with  
     * @return Collection;  Collection of results
     */
    public function findByDistrict(string $district, array $with = []): Collection;
    /**
     * Find by engineer.
     *
     * @param  int  $engineerId  
     * @param  array  $with  
     * @return Collection;  Collection of results
     */
    public function findByEngineer(int $engineerId, array $with = []): Collection;
    /**
     * Find by installation status.
     *
     * @param  string  $status  The status value
     * @param  array  $with  
     * @return Collection;  Collection of results
     */
    public function findByInstallationStatus(string $status, array $with = []): Collection;
    /**
     * Get the sites with tasks.
     *
     * @param  int  $projectId  The project identifier
     * @return Collection;  Collection of results
     */
    public function getSitesWithTasks(int $projectId): Collection;
    /**
     * Find with full relations.
     *
     * @param  int  $id  The resource identifier
     * @return ?Model;  
     */
    public function findWithFullRelations(int $id): ?Model;
}
