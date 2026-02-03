<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;

class GetArticleController extends Controller
{
    public function __construct(
        protected ArticleService $articleService
    ) {}

    public function __invoke(int $id): JsonResponse
    {
        $article = $this->articleService->getById($id);

        return (new ArticleResource($article))
            ->response()
            ->setStatusCode(200);
    }
}
