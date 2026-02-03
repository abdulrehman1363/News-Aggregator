<?php

namespace App\Repositories;

use App\Exceptions\RepositoryException;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        protected Category $model
    ) {}

    public function all(): Collection
    {
        try {
            return $this->model->orderBy('name')->get();
        } catch (\Exception $e) {
            Log::error('Failed to fetch all categories', ['error' => $e->getMessage()]);
            throw RepositoryException::queryFailed('CategoryRepository', 'all', $e);
        }
    }

    public function findOrFail(int $id): Model
    {
        try {
            return $this->model->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw RepositoryException::notFound('Category', $id);
        } catch (\Exception $e) {
            Log::error('Failed to find category', ['id' => $id, 'error' => $e->getMessage()]);
            throw RepositoryException::queryFailed('CategoryRepository', 'findOrFail', $e);
        }
    }

    public function firstOrCreate(array $attributes, array $values = []): Model
    {
        try {
            return $this->model->firstOrCreate($attributes, $values);
        } catch (\Exception $e) {
            Log::error('Failed to firstOrCreate category', [
                'attributes' => $attributes,
                'values' => $values,
                'error' => $e->getMessage(),
            ]);
            throw RepositoryException::createFailed('Category', $e);
        }
    }

    public function bulkInsert(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        try {
            DB::table('categories')->insert($data);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to bulk insert categories', [
                'count' => count($data),
                'error' => $e->getMessage(),
            ]);
            throw RepositoryException::createFailed('Categories (bulk)', $e);
        }
    }
}
