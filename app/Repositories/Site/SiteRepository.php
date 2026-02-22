<?php

namespace App\Repositories\Site;

use App\Contracts\SiteRepositoryInterface;
use App\Models\Site;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Data access layer for sites/panchayats. Provides queries for sites with pole aggregation,
 * district filtering, and ward-level statistics.
 *
 * Data Flow:
 *   SiteService → SiteRepository → Eloquent queries with sub-queries for pole counts →
 *   Site data with statistics
 *
 * @depends-on Site, Streetlight, Pole
 * @business-domain Site Management
 * @package App\Repositories\Site
 */
class SiteRepository extends BaseRepository implements SiteRepositoryInterface
{
    /**
     * Create a new SiteRepository instance.
     *
     * @param  Site  $model  
     */
    public function __construct(Site $model)
    {
        parent::__construct($model);
    }

    /**
     * Find by project.
     *
     * @param  int  $projectId  The project identifier
     * @param  array  $with  
     * @return Collection  Collection of results
     */
    public function findByProject(int $projectId, array $with = []): Collection
    {
        return $this->model->with($with ?: ['project', 'tasks'])
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find by district.
     *
     * @param  string  $district  The district identifier or name
     * @param  array  $with  
     * @return Collection  Collection of results
     */
    public function findByDistrict(string $district, array $with = []): Collection
    {
        return $this->model->with($with ?: ['project', 'tasks'])
            ->where('district', $district)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find by engineer.
     *
     * @param  int  $engineerId  
     * @param  array  $with  
     * @return Collection  Collection of results
     */
    public function findByEngineer(int $engineerId, array $with = []): Collection
    {
        return $this->model->with($with ?: ['project', 'tasks'])
            ->where('site_engineer', $engineerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find by installation status.
     *
     * @param  string  $status  The status value
     * @param  array  $with  
     * @return Collection  Collection of results
     */
    public function findByInstallationStatus(string $status, array $with = []): Collection
    {
        return $this->model->with($with ?: ['project', 'tasks'])
            ->where('installation_status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the sites with tasks.
     *
     * @param  int  $projectId  The project identifier
     * @return Collection  Collection of results
     */
    public function getSitesWithTasks(int $projectId): Collection
    {
        return $this->model->with(['tasks', 'project'])
            ->where('project_id', $projectId)
            ->whereHas('tasks')
            ->get();
    }

    /**
     * Find with full relations.
     *
     * @param  int  $id  The resource identifier
     * @return ?Model  
     */
    public function findWithFullRelations(int $id): ?Model
    {
        return $this->model->with(['project', 'tasks', 'tasks.engineer', 'tasks.vendor'])
            ->find($id);
    }
}
