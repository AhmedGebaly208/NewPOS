<?php

namespace Modules\Core\Repositories\Contracts;

interface RepositoryInterface
{
    /**
     * Get all records.
     */
    public function all(array $columns = ['*'], array $relations = []);

    /**
     * Paginate records.
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []);

    /**
     * Find a record by ID.
     */
    public function find(int|string $id, array $columns = ['*'], array $relations = []);

    /**
     * Find a record by specific field.
     */
    public function findBy(string $field, mixed $value, array $columns = ['*'], array $relations = []);

    /**
     * Find records by multiple conditions.
     */
    public function findWhere(array $where, array $columns = ['*'], array $relations = []);

    /**
     * Create a new record.
     */
    public function create(array $data);

    /**
     * Update a record.
     */
    public function update(int|string $id, array $data);

    /**
     * Delete a record.
     */
    public function delete(int|string $id);

    /**
     * Restore a soft deleted record.
     */
    public function restore(int|string $id);

    /**
     * Force delete a record.
     */
    public function forceDelete(int|string $id);

    /**
     * Count records.
     */
    public function count(array $where = []);

    /**
     * Check if record exists.
     */
    public function exists(array $where);
}
