<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface ArticleRepositoryInterface
{
    public function findOrFail(int $id): Model;

    public function bulkInsert(array $data): bool;

    public function getExistingUrls(array $urls): array;

    public function search(array $filters): LengthAwarePaginator;
}
