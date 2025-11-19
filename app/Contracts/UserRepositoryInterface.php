<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * User Repository Interface
 * 
 * Defines contract for user data access operations
 */
interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Find user by email
     *
     * @param string $email
     * @return Model|null
     */
    public function findByEmail(string $email): ?Model;

    /**
     * Find user by username
     *
     * @param string $username
     * @return Model|null
     */
    public function findByUsername(string $username): ?Model;

    /**
     * Get users by role
     *
     * @param int $role
     * @return Collection
     */
    public function getUsersByRole(int $role): Collection;

    /**
     * Get users by project
     *
     * @param int $projectId
     * @return Collection
     */
    public function getUsersByProject(int $projectId): Collection;

    /**
     * Get users managed by a manager
     *
     * @param int $managerId
     * @return Collection
     */
    public function getUsersByManager(int $managerId): Collection;

    /**
     * Get vendors assigned to a site engineer
     *
     * @param int $engineerId
     * @return Collection
     */
    public function getVendorsByEngineer(int $engineerId): Collection;

    /**
     * Update user's last online timestamp
     *
     * @param int $userId
     * @return bool
     */
    public function updateLastOnline(int $userId): bool;

    /**
     * Get active users (login not disabled)
     *
     * @return Collection
     */
    public function getActiveUsers(): Collection;
}
