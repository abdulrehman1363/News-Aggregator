<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserPreferenceRequest;
use App\Http\Resources\UserPreferenceWithMessageResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class StoreUserPreferencesController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function __invoke(StoreUserPreferenceRequest $request): JsonResponse
    {
        $preferences = $this->userService->updatePreferences(
            Auth::id(),
            $request->validated()
        );

        return (new UserPreferenceWithMessageResource($preferences, 'Preferences updated successfully'))
            ->response()
            ->setStatusCode(200);
    }
}
