<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface AuthorRepositoryInterface
{
    public function getByNames(array $names): Collection;

    public function bulkInsert(array $data): bool;
}
