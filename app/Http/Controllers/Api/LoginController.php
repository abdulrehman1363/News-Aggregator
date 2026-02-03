<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\MessageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->validated())) {
            return (new MessageResource('Invalid credentials'))
                ->response()
                ->setStatusCode(401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return (new AuthResource([
            'user' => $user,
            'token' => $token,
            'message' => 'Login successful',
        ]))
            ->response()
            ->setStatusCode(200);
    }
}
