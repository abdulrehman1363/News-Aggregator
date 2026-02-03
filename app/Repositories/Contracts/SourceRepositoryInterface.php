<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface SourceRepositoryInterface
{
    public function findOrFail(int $id): Model;

    public function firstOrCreateByIdentifier(string $identifier, array $data): Model;

    public function getActive(): Collection;
}
