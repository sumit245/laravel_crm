<?php

namespace App\Repositories\User;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Data access layer for users/staff. Provides role-filtered queries, project-scoped user lists,
 * and performance data aggregation queries.
 *
 * Data Flow:
 *   UserService → UserRepository → Role-filtered Eloquent queries → User data with
 *   assignments
 *
 * @depends-on User, Role
 * @business-domain Staff & HR
 * @package App\Repositories\User
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritDoc}
     */
    public function findByEmail(string $email): ?Model
    {
        return $this->findBy('email', $email);
    }

    /**
     * {@inheritDoc}
     */
    public function findByUsername(string $username): ?Model
    {
        return $this->findBy('username', $username);
    }

    /**
     * {@inheritDoc}
     */
    public function getUsersByRole(int $role): Collection
    {
        return $this->model->newQuery()
            ->where('role', $role)
            ->orderBy('firstName')
            ->orderBy('lastName')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getUsersByProject(int $projectId): Collection
    {
        return $this->model->newQuery()
            ->where('project_id', $projectId)
            ->with(['usercategory', 'projectManager', 'siteEngineer'])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getUsersByManager(int $managerId): Collection
    {
        return $this->model->newQuery()
            ->where('manager_id', $managerId)
            ->with(['usercategory', 'projects'])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getVendorsByEngineer(int $engineerId): Collection
    {
        return $this->model->newQuery()
            ->where('site_engineer_id', $engineerId)
            ->where('role', \App\Enums\UserRole::VENDOR->value) // Vendor role
            ->with(['usercategory'])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function updateLastOnline(int $userId): bool
    {
        return $this->update($userId, [
            'lastOnline' => now()
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveUsers(): Collection
    {
        return $this->model->newQuery()
            ->where('disableLogin', false)
            ->orWhereNull('disableLogin')
            ->with(['usercategory'])
            ->get();
    }

    /**
     * Get user with full relationships
     *
     * @param int $id
     * @return Model|null
     */
    public function findWithFullRelations(int $id): ?Model
    {
        return $this->findById($id, [
            'usercategory',
            'projects',
            'projectManager',
            'siteEngineer',
            'verticalHead',
            'siteEngineers',
            'vendors',
            'meetings'
        ]);
    }
}
