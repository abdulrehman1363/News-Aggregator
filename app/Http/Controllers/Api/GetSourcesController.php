<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SourceResource;
use App\Services\SourceService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetSourcesController extends Controller
{
    public function __construct(
        protected SourceService $sourceService
    ) {}

    public function __invoke(): AnonymousResourceCollection
    {
        $sources = $this->sourceService->getAllSources();

        return SourceResource::collection($sources);
    }
}
