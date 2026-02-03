<?php

namespace App\Repositories;

use App\Exceptions\RepositoryException;
use App\Models\UserPreference;
use App\Repositories\Contracts\UserPreferenceRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UserPreferenceRepository implements UserPreferenceRepositoryInterface
{
    public function __construct(
        protected UserPreference $model
    ) {}

    public function findByUserId(int $userId): ?Model
    {
        try {
            return $this->model->where('user_id', $userId)->first();
        } catch (\Exception $e) {
            Log::error('Failed to find user preferences', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw RepositoryException::queryFailed('UserPreferenceRepository', 'findByUserId', $e);
        }
    }

    public function updateOrCreate(array $attributes, array $values): Model
    {
        try {
            return $this->model->updateOrCreate($attributes, $values);
        } catch (\Exception $e) {
            Log::error('Failed to updateOrCreate user preferences', [
                'attributes' => $attributes,
                'values' => $values,
                'error' => $e->getMessage(),
            ]);
            throw RepositoryException::createFailed('UserPreference', $e);
        }
    }

    public function delete(int $userId): bool
    {
        try {
            return $this->model->where('user_id', $userId)->delete();
        } catch (\Exception $e) {
            Log::error('Failed to delete user preferences', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw RepositoryException::deleteFailed('UserPreference', $userId, $e);
        }
    }
}
