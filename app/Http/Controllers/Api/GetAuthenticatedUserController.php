<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetAuthenticatedUserController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return (new UserResource($request->user()))
            ->response()
            ->setStatusCode(200);
    }
}
