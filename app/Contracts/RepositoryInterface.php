<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository Interface
 * 
 * Base contract for all repository implementations.
 * Provides standard CRUD operations and common query methods.
 */
interface RepositoryInterface
{
    /**
     * Find a model by its primary key
     *
     * @param int $id
     * @param array $relations Relations to eager load
     * @return Model|null
     */
    public function findById(int $id, array $relations = []): ?Model;

    /**
     * Find a model by specific column value
     *
     * @param string $column
     * @param mixed $value
     * @param array $relations
     * @return Model|null
     */
    public function findBy(string $column, mixed $value, array $relations = []): ?Model;

    /**
     * Get all models
     *
     * @param array $relations
     * @return Collection
     */
    public function all(array $relations = []): Collection;

    /**
     * Create a new model
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update an existing model
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a model
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get paginated results
     *
     * @param int $perPage
     * @param array $relations
     * @return mixed
     */
    public function paginate(int $perPage = 15, array $relations = []): mixed;

    /**
     * Count total records
     *
     * @return int
     */
    public function count(): int;
}
