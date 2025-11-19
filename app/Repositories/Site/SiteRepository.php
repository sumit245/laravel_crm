<?php

namespace App\Repositories\Site;

use App\Contracts\SiteRepositoryInterface;
use App\Models\Site;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SiteRepository extends BaseRepository implements SiteRepositoryInterface
{
    public function __construct(Site $model)
    {
        parent::__construct($model);
    }

    public function findByProject(int $projectId, array $with = []): Collection
    {
        return $this->model->with($with ?: ['project', 'tasks'])
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByDistrict(string $district, array $with = []): Collection
    {
        return $this->model->with($with ?: ['project', 'tasks'])
            ->where('district', $district)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByEngineer(int $engineerId, array $with = []): Collection
    {
        return $this->model->with($with ?: ['project', 'tasks'])
            ->where('site_engineer', $engineerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByInstallationStatus(string $status, array $with = []): Collection
    {
        return $this->model->with($with ?: ['project', 'tasks'])
            ->where('installation_status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getSitesWithTasks(int $projectId): Collection
    {
        return $this->model->with(['tasks', 'project'])
            ->where('project_id', $projectId)
            ->whereHas('tasks')
            ->get();
    }

    public function findWithFullRelations(int $id): ?Model
    {
        return $this->model->with(['project', 'tasks', 'tasks.engineer', 'tasks.vendor'])
            ->find($id);
    }
}
