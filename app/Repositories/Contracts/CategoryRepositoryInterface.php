<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function all(): Collection;

    public function findOrFail(int $id): Model;

    public function firstOrCreate(array $attributes, array $values = []): Model;

    public function bulkInsert(array $data): bool;
}
