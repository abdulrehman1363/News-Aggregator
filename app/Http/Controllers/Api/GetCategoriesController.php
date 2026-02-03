<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetCategoriesController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function __invoke(): AnonymousResourceCollection
    {
        $categories = $this->categoryService->getAllCategories();

        return CategoryResource::collection($categories);
    }
}
