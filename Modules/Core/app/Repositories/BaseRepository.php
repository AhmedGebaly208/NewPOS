<?php

namespace Modules\Core\Repositories;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Repositories\Contracts\RepositoryInterface;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    /**
     * BaseRepository constructor.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records.
     */
    public function all(array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)->get($columns);
    }

    /**
     * Paginate records.
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    /**
     * Find a record by ID.
     */
    public function find(int|string $id, array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)->find($id, $columns);
    }

    /**
     * Find a record by specific field.
     */
    public function findBy(string $field, mixed $value, array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)
            ->where($field, '=', $value)
            ->first($columns);
    }

    /**
     * Find records by multiple conditions.
     */
    public function findWhere(array $where, array $columns = ['*'], array $relations = [])
    {
        $query = $this->model->with($relations);

        foreach ($where as $field => $value) {
            if (is_array($value)) {
                [$field, $operator, $val] = $value;
                $query->where($field, $operator, $val);
            } else {
                $query->where($field, '=', $value);
            }
        }

        return $query->get($columns);
    }

    /**
     * Create a new record.
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update a record.
     */
    public function update(int|string $id, array $data)
    {
        $record = $this->find($id);
        
        if ($record) {
            $record->update($data);
            return $record->fresh();
        }

        return null;
    }

    /**
     * Delete a record.
     */
    public function delete(int|string $id)
    {
        $record = $this->find($id);
        
        if ($record) {
            return $record->delete();
        }

        return false;
    }

    /**
     * Restore a soft deleted record.
     */
    public function restore(int|string $id)
    {
        return $this->model->withTrashed()->find($id)?->restore();
    }

    /**
     * Force delete a record.
     */
    public function forceDelete(int|string $id)
    {
        $record = $this->model->withTrashed()->find($id);
        
        if ($record) {
            return $record->forceDelete();
        }

        return false;
    }

    /**
     * Count records.
     */
    public function count(array $where = [])
    {
        if (empty($where)) {
            return $this->model->count();
        }

        return $this->model->where($where)->count();
    }

    /**
     * Check if record exists.
     */
    public function exists(array $where)
    {
        return $this->model->where($where)->exists();
    }

    /**
     * Get model instance.
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Set model instance.
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;
        return $this;
    }
}
