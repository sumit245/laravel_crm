<?php

namespace App\Repositories\Project;

use App\Contracts\ProjectRepositoryInterface;
use App\Enums\UserRole;
use App\Models\Project;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Project Repository
 * 
 * Handles all data access operations for projects
 */
class ProjectRepository extends BaseRepository implements ProjectRepositoryInterface
{
    /**
     * ProjectRepository constructor
     */
    public function __construct(Project $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritDoc}
     */
    public function findByWorkOrderNumber(string $workOrderNumber): ?Model
    {
        return $this->findBy('work_order_number', $workOrderNumber);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllForUser(int $userId, int $userRole): Collection
    {
        $role = UserRole::fromValue($userRole);

        // Admin can see all projects
        if ($role === UserRole::ADMIN) {
            return $this->all(['stores', 'sites', 'streetlights']);
        }

        // Project Manager sees projects they're assigned to
        if ($role === UserRole::PROJECT_MANAGER) {
            return $this->model->newQuery()
                ->whereHas('users', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->with(['stores', 'sites', 'streetlights'])
                ->get();
        }

        // Other roles see projects based on their project_id
        return $this->model->newQuery()
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['stores', 'sites', 'streetlights'])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByType(int $projectType): Collection
    {
        return $this->model->newQuery()
            ->where('project_type', $projectType)
            ->with(['stores', 'sites', 'streetlights'])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByState(int $stateId): Collection
    {
        return $this->model->newQuery()
            ->where('project_in_state', $stateId)
            ->with(['stores', 'sites', 'streetlights'])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getProjectsInDateRange(array $dateRange): Collection
    {
        return $this->findBetweenDates('start_date', $dateRange, ['stores', 'sites', 'streetlights']);
    }

    /**
     * Get project with full relationships
     *
     * @param int $id
     * @return Model|null
     */
    public function findWithFullRelations(int $id): ?Model
    {
        return $this->findById($id, [
            'stores',
            'sites.districtRelation',
            'sites.stateRelation',
            'sites.engineerRelation',
            'sites.vendorRelation',
            'streetlights',
            'tasks',
            'users'
        ]);
    }
}
