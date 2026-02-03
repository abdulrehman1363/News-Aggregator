<?php

namespace App\Repositories;

use App\Exceptions\RepositoryException;
use App\Models\Source;
use App\Repositories\Contracts\SourceRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SourceRepository implements SourceRepositoryInterface
{
    public function __construct(
        protected Source $model
    ) {}

    public function findOrFail(int $id): Model
    {
        try {
            return $this->model->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw RepositoryException::notFound('Source', $id);
        } catch (\Exception $e) {
            Log::error('Failed to find source', ['id' => $id, 'error' => $e->getMessage()]);
            throw RepositoryException::queryFailed('SourceRepository', 'findOrFail', $e);
        }
    }

    public function firstOrCreateByIdentifier(string $identifier, array $data): Model
    {
        try {
            return $this->model->firstOrCreate(
                ['api_identifier' => $identifier],
                $data
            );
        } catch (\Exception $e) {
            Log::error('Failed to firstOrCreate source', [
                'identifier' => $identifier,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw RepositoryException::createFailed('Source', $e);
        }
    }

    public function getActive(): Collection
    {
        try {
            return $this->model
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to fetch active sources', ['error' => $e->getMessage()]);
            throw RepositoryException::queryFailed('SourceRepository', 'getActive', $e);
        }
    }
}
