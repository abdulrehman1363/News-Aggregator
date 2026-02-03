<?php

namespace App\Repositories;

use App\Exceptions\RepositoryException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        protected User $model
    ) {}

    public function findByEmail(string $email): ?Model
    {
        try {
            return $this->model->where('email', $email)->first();
        } catch (\Exception $e) {
            Log::error('Failed to find user by email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            throw RepositoryException::queryFailed('UserRepository', 'findByEmail', $e);
        }
    }

    public function create(array $data): Model
    {
        try {
            return $this->model->create($data);
        } catch (\Exception $e) {
            Log::error('Failed to create user', [
                'data' => array_diff_key($data, ['password' => '']), // Don't log password
                'error' => $e->getMessage(),
            ]);
            throw RepositoryException::createFailed('User', $e);
        }
    }
}
