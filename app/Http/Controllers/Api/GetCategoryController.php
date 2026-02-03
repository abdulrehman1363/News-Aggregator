<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class GetCategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function __invoke(int $id): JsonResponse
    {
        $category = $this->categoryService->getCategoryById($id);

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(200);
    }
}
