<?php

namespace App\Services\User;

use App\Contracts\UserRepositoryInterface;
use App\Contracts\UserServiceInterface;
use App\Models\User;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * User Management Service
 * 
 * Handles business logic for user management
 */
class UserService extends BaseService implements UserServiceInterface
{
    /**
     * UserService constructor
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * {@inheritDoc}
     */
    public function createUser(array $data): User
    {
        return $this->executeInTransaction(function () use ($data) {
            // Validate user data
            $this->validateUserData($data);

            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Set default values
            $data['disableLogin'] = $data['disableLogin'] ?? false;
            $data['status'] = $data['status'] ?? 'active';

            // Create the user
            $user = $this->userRepository->create($data);

            $this->logInfo('User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);

            return $user;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser(int $userId, array $data): bool
    {
        return $this->executeInTransaction(function () use ($userId, $data) {
            // Validate update data
            $this->validateUserData($data, $userId);

            // Hash password if provided
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                // Don't update password if not provided
                unset($data['password']);
            }

            // Update the user
            $result = $this->userRepository->update($userId, $data);

            if ($result) {
                $this->logInfo('User updated successfully', [
                    'user_id' => $userId
                ]);
            }

            return $result;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser(int $userId): bool
    {
        return $this->executeInTransaction(function () use ($userId) {
            $result = $this->userRepository->delete($userId);

            if ($result) {
                $this->logInfo('User deleted successfully', [
                    'user_id' => $userId
                ]);
            }

            return $result;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getUserWithRelations(int $userId): ?User
    {
        return $this->userRepository->findWithFullRelations($userId);
    }

    /**
     * {@inheritDoc}
     */
    public function assignToProject(int $userId, int $projectId, string $role): bool
    {
        return $this->executeInTransaction(function () use ($userId, $projectId, $role) {
            $user = $this->userRepository->findById($userId);

            if (!$user) {
                throw new \Exception('User not found');
            }

            // Attach user to project with role
            $user->projects()->syncWithoutDetaching([$projectId => ['role' => $role]]);

            $this->logInfo('User assigned to project', [
                'user_id' => $userId,
                'project_id' => $projectId,
                'role' => $role
            ]);

            return true;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getUsersByRole(int $role): Collection
    {
        return $this->userRepository->getUsersByRole($role);
    }

    /**
     * {@inheritDoc}
     */
    public function disableUserLogin(int $userId): bool
    {
        return $this->executeInTransaction(function () use ($userId) {
            $result = $this->userRepository->update($userId, [
                'disableLogin' => true
            ]);

            if ($result) {
                $this->logInfo('User login disabled', ['user_id' => $userId]);
            }

            return $result;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function enableUserLogin(int $userId): bool
    {
        return $this->executeInTransaction(function () use ($userId) {
            $result = $this->userRepository->update($userId, [
                'disableLogin' => false
            ]);

            if ($result) {
                $this->logInfo('User login enabled', ['user_id' => $userId]);
            }

            return $result;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function changePassword(int $userId, string $newPassword): bool
    {
        return $this->executeInTransaction(function () use ($userId, $newPassword) {
            // Validate password strength
            $this->validatePassword($newPassword);

            $result = $this->userRepository->update($userId, [
                'password' => Hash::make($newPassword)
            ]);

            if ($result) {
                $this->logInfo('Password changed successfully', ['user_id' => $userId]);
            }

            return $result;
        });
    }

    /**
     * Validate user data
     *
     * @param array $data
     * @param int|null $userId For updates
     * @throws ValidationException
     */
    protected function validateUserData(array $data, ?int $userId = null): void
    {
        $rules = [
            'email' => 'required|email|unique:users,email' . ($userId ? ",$userId" : ''),
            'username' => 'sometimes|string|unique:users,username' . ($userId ? ",$userId" : ''),
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'contactNo' => 'nullable|string|max:20',
            'role' => 'required|integer|in:0,1,2,3,4,5,10',
            'project_id' => 'nullable|exists:projects,id',
            'manager_id' => 'nullable|exists:users,id',
            'category' => 'nullable|exists:user_categories,id',
        ];

        // Password validation only for creation or when password is provided
        if (!$userId || isset($data['password'])) {
            $rules['password'] = 'required|string|min:8';
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate password strength
     *
     * @param string $password
     * @throws ValidationException
     */
    protected function validatePassword(string $password): void
    {
        $rules = [
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',      // At least one lowercase letter
                'regex:/[A-Z]/',      // At least one uppercase letter
                'regex:/[0-9]/',      // At least one digit
                'regex:/[@$!%*#?&]/', // At least one special character
            ],
        ];

        $validator = Validator::make(['password' => $password], $rules, [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
