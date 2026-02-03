<?php

namespace App\Repositories;

use App\Exceptions\RepositoryException;
use App\Models\Author;
use App\Repositories\Contracts\AuthorRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthorRepository implements AuthorRepositoryInterface
{
    public function __construct(
        protected Author $model
    ) {}

    public function getByNames(array $names): Collection
    {
        try {
            return $this->model->whereIn('name', $names)->get();
        } catch (\Exception $e) {
            Log::error('Failed to get authors by names', [
                'names' => $names,
                'error' => $e->getMessage(),
            ]);
            throw RepositoryException::queryFailed('AuthorRepository', 'getByNames', $e);
        }
    }

    public function bulkInsert(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        try {
            DB::table('authors')->insert($data);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to bulk insert authors', [
                'count' => count($data),
                'error' => $e->getMessage(),
            ]);
            throw RepositoryException::createFailed('Authors (bulk)', $e);
        }
    }
}
