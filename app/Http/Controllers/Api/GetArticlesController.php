<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchArticlesRequest;
use App\Http\Resources\ArticleCollection;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;

class GetArticlesController extends Controller
{
    public function __construct(
        private ArticleService $articleService
    ) {}

    public function __invoke(SearchArticlesRequest $request): JsonResponse
    {
        $articles = $this->articleService->search($request->validated());

        return (new ArticleCollection($articles))
            ->response()
            ->setStatusCode(200);
    }
}
