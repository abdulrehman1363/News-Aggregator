<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Resources\UserPreferenceResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GetUserPreferencesController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function __invoke(): JsonResponse
    {
        $preferences = $this->userService->getPreferences(Auth::id());

        if (!$preferences) {
            return (new MessageResource('No preferences found'))
                ->response()
                ->setStatusCode(404);
        }

        return (new UserPreferenceResource($preferences))
            ->response()
            ->setStatusCode(200);
    }
}
