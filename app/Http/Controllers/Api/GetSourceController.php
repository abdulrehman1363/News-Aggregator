<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SourceResource;
use App\Services\SourceService;
use Illuminate\Http\JsonResponse;

class GetSourceController extends Controller
{
    public function __construct(
        protected SourceService $sourceService
    ) {}

    public function __invoke(int $id): JsonResponse
    {
        $source = $this->sourceService->getSourceById($id);

        return (new SourceResource($source))
            ->response()
            ->setStatusCode(200);
    }
}
