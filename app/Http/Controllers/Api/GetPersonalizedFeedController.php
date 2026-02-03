<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCollection;
use App\Http\Resources\MessageResource;
use App\Services\ArticleService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GetPersonalizedFeedController extends Controller
{
    public function __construct(
        protected ArticleService $articleService,
        protected UserService $userService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $preferences = $this->userService->getPreferences(Auth::id());

        if (!$preferences) {
            return (new MessageResource('No preferences set. Please set your preferences first.'))
                ->response()
                ->setStatusCode(404);
        }

        $filters = [
            'per_page' => $request->input('per_page', 15),
        ];

        if ($preferences->preferred_sources) {
            $filters['sources'] = $preferences->preferred_sources;
        }

        if ($preferences->preferred_categories) {
            $filters['categories'] = $preferences->preferred_categories;
        }

        if ($preferences->preferred_authors) {
            $filters['authors'] = $preferences->preferred_authors;
        }

        $articles = $this->articleService->search($filters);

        return (new ArticleCollection($articles))
            ->response()
            ->setStatusCode(200);
    }
}
