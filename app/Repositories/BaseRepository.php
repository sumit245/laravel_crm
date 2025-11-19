<?php

namespace App\Repositories;

use App\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Base Repository
 * 
 * Abstract base class for all repository implementations.
 * Provides common CRUD operations and query building methods.
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The model instance
     *
     * @var Model
     */
    protected Model $model;

    /**
     * BaseRepository constructor
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id, array $relations = []): ?Model
    {
        $query = $this->model->newQuery();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(string $column, mixed $value, array $relations = []): ?Model
    {
        $query = $this->model->newQuery();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->where($column, $value)->first();
    }

    /**
     * {@inheritDoc}
     */
    public function all(array $relations = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): bool
    {
        $model = $this->findById($id);

        if (!$model) {
            return false;
        }

        return $model->update($data);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $model = $this->findById($id);

        if (!$model) {
            return false;
        }

        return $model->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(int $perPage = 15, array $relations = []): mixed
    {
        $query = $this->model->newQuery();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Find models where column is in array of values
     *
     * @param string $column
     * @param array $values
     * @param array $relations
     * @return Collection
     */
    protected function findWhereIn(string $column, array $values, array $relations = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->whereIn($column, $values)->get();
    }

    /**
     * Find models where column is not in array of values
     *
     * @param string $column
     * @param array $values
     * @param array $relations
     * @return Collection
     */
    protected function findWhereNotIn(string $column, array $values, array $relations = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->whereNotIn($column, $values)->get();
    }

    /**
     * Find models within a date range
     *
     * @param string $column
     * @param array $dateRange
     * @param array $relations
     * @return Collection
     */
    protected function findBetweenDates(string $column, array $dateRange, array $relations = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->whereBetween($column, $dateRange)->get();
    }
}
