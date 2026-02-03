<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {}

    public function getAllCategories(): Collection
    {
        try {
            return $this->repository->all();
        } catch (\Exception $e) {
            Log::error('Failed to get all categories', ['error' => $e->getMessage()]);
            throw ServiceException::operationFailed('retrieve categories', $e);
        }
    }

    public function getCategoryById(int $id): Model
    {
        try {
            return $this->repository->findOrFail($id);
        } catch (\Exception $e) {
            Log::error('Failed to get category by ID', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e; // Re-throw repository exceptions as-is
        }
    }

    public function getOrCreateBySlug(string $slug, string $name): Model
    {
        try {
            return $this->repository->firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
        } catch (\Exception $e) {
            Log::error('Failed to get or create category', [
                'slug' => $slug,
                'name' => $name,
                'error' => $e->getMessage(),
            ]);
            throw ServiceException::operationFailed('get or create category', $e);
        }
    }
}
