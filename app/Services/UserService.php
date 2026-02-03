<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\UserPreferenceRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected UserPreferenceRepositoryInterface $preferenceRepository
    ) {}

    public function register(array $data): Model
    {
        try {
            // Check if email already exists
            $existingUser = $this->userRepository->findByEmail($data['email']);
            
            if ($existingUser) {
                throw ServiceException::invalidData('Email already exists');
            }

            return $this->userRepository->create($data);
        } catch (ServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to register user', [
                'email' => $data['email'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw ServiceException::operationFailed('register user', $e);
        }
    }

    public function findByEmail(string $email): ?Model
    {
        try {
            return $this->userRepository->findByEmail($email);
        } catch (\Exception $e) {
            Log::error('Failed to find user by email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            throw ServiceException::operationFailed('find user by email', $e);
        }
    }

    public function getPreferences(int $userId): ?Model
    {
        try {
            return $this->preferenceRepository->findByUserId($userId);
        } catch (\Exception $e) {
            Log::error('Failed to get user preferences', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw ServiceException::operationFailed('retrieve user preferences', $e);
        }
    }

    public function updatePreferences(int $userId, array $data): Model
    {
        try {
            return $this->preferenceRepository->updateOrCreate(
                ['user_id' => $userId],
                $data
            );
        } catch (\Exception $e) {
            Log::error('Failed to update user preferences', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw ServiceException::operationFailed('update user preferences', $e);
        }
    }

    public function deletePreferences(int $userId): bool
    {
        try {
            return $this->preferenceRepository->delete($userId);
        } catch (\Exception $e) {
            Log::error('Failed to delete user preferences', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw ServiceException::operationFailed('delete user preferences', $e);
        }
    }
}
