<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Repositories\Contracts\SourceRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SourceService
{
    public function __construct(
        protected SourceRepositoryInterface $repository
    ) {}

    public function getAllSources(): Collection
    {
        try {
            return $this->repository->getActive();
        } catch (\Exception $e) {
            Log::error('Failed to get all sources', ['error' => $e->getMessage()]);
            throw ServiceException::operationFailed('retrieve sources', $e);
        }
    }

    public function getSourceById(int $id): Model
    {
        try {
            return $this->repository->findOrFail($id);
        } catch (\Exception $e) {
            Log::error('Failed to get source by ID', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e; // Re-throw repository exceptions as-is
        }
    }

    public function getOrCreateSource(string $identifier, string $name): Model
    {
        try {
            return $this->repository->firstOrCreateByIdentifier($identifier, [
                'name' => $name,
                'is_active' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get or create source', [
                'identifier' => $identifier,
                'name' => $name,
                'error' => $e->getMessage(),
            ]);
            throw ServiceException::operationFailed('get or create source', $e);
        }
    }
}
