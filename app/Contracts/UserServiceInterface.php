<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * User Service Interface
 * 
 * Defines contract for user management operations
 */
interface UserServiceInterface extends ServiceInterface
{
    /**
     * Create a new user
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User;

    /**
     * Update an existing user
     *
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateUser(int $userId, array $data): bool;

    /**
     * Delete a user
     *
     * @param int $userId
     * @return bool
     */
    public function deleteUser(int $userId): bool;

    /**
     * Get user by ID with relationships
     *
     * @param int $userId
     * @return User|null
     */
    public function getUserWithRelations(int $userId): ?User;

    /**
     * Assign user to project
     *
     * @param int $userId
     * @param int $projectId
     * @param string $role
     * @return bool
     */
    public function assignToProject(int $userId, int $projectId, string $role): bool;

    /**
     * Get users by role for dropdown
     *
     * @param int $role
     * @return Collection
     */
    public function getUsersByRole(int $role): Collection;

    /**
     * Disable user login
     *
     * @param int $userId
     * @return bool
     */
    public function disableUserLogin(int $userId): bool;

    /**
     * Enable user login
     *
     * @param int $userId
     * @return bool
     */
    public function enableUserLogin(int $userId): bool;

    /**
     * Change user password
     *
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function changePassword(int $userId, string $newPassword): bool;
}
