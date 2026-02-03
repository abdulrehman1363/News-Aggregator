<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface UserPreferenceRepositoryInterface
{
    public function findByUserId(int $userId): ?Model;

    public function updateOrCreate(array $attributes, array $values): Model;

    public function delete(int $userId): bool;
}
