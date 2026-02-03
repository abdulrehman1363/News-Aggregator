<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $this->userService->register([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return (new AuthResource([
            'user' => $user,
            'token' => $token,
            'message' => 'User registered successfully',
        ]))
            ->response()
            ->setStatusCode(201);
    }
}
