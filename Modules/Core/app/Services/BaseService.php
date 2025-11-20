<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Repositories\Contracts\RepositoryInterface;

abstract class BaseService
{
    protected RepositoryInterface $repository;

    /**
     * BaseService constructor.
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all records.
     */
    public function getAll(array $relations = [])
    {
        try {
            return $this->repository->all(['*'], $relations);
        } catch (\Exception $e) {
            Log::error('Error in getAll: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get paginated records.
     */
    public function getPaginated(int $perPage = 15, array $relations = [])
    {
        try {
            return $this->repository->paginate($perPage, ['*'], $relations);
        } catch (\Exception $e) {
            Log::error('Error in getPaginated: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find a record by ID.
     */
    public function findById(int|string $id, array $relations = [])
    {
        try {
            return $this->repository->find($id, ['*'], $relations);
        } catch (\Exception $e) {
            Log::error('Error in findById: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new record.
     */
    public function create(array $data)
    {
        DB::beginTransaction();
        
        try {
            $record = $this->repository->create($data);
            DB::commit();
            
            return $record;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in create: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a record.
     */
    public function update(int|string $id, array $data)
    {
        DB::beginTransaction();
        
        try {
            $record = $this->repository->update($id, $data);
            DB::commit();
            
            return $record;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in update: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a record.
     */
    public function delete(int|string $id)
    {
        DB::beginTransaction();
        
        try {
            $result = $this->repository->delete($id);
            DB::commit();
            
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in delete: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Restore a soft deleted record.
     */
    public function restore(int|string $id)
    {
        DB::beginTransaction();
        
        try {
            $result = $this->repository->restore($id);
            DB::commit();
            
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in restore: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get repository instance.
     */
    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
