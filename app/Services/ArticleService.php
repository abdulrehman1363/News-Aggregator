<?php

namespace App\Services;

use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\AuthorRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\SourceRepositoryInterface;
use App\Services\NewsProviders\Contracts\NewsProviderInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArticleService
{
    public function __construct(
        protected ArticleRepositoryInterface $articleRepository,
        protected SourceRepositoryInterface $sourceRepository,
        protected CategoryRepositoryInterface $categoryRepository,
        protected AuthorRepositoryInterface $authorRepository
    ) {}

    /**
     * Fetch and store articles from a news provider using bulk insert
     */
    public function fetchAndStore(NewsProviderInterface $provider, array $params = []): int
    {
        try {
            // Get or create the source
            $source = $this->sourceRepository->firstOrCreateByIdentifier(
                $provider->getProviderIdentifier(),
                [
                    'name' => $provider->getProviderName(),
                    'is_active' => true,
                ]
            );

            if (!$source->is_active) {
                Log::info("Source {$source->name} is not active, skipping");
                return 0;
            }

            // Fetch articles from the provider
            $articlesData = $provider->fetchArticles($params);

            if (empty($articlesData)) {
                Log::info("No articles fetched from {$source->name}");
                return 0;
            }

            // Use bulk insert for better performance
            $storedCount = $this->bulkStoreArticles($articlesData, $source);

            Log::info("Fetched and stored {$storedCount} articles from {$source->name}");
            return $storedCount;
        } catch (\Exception $e) {
            Log::error('Error fetching and storing articles', [
                'provider' => $provider->getProviderName(),
                'message' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Search articles with filters
     */
    public function search(array $filters = []): LengthAwarePaginator
    {
        return $this->articleRepository->search($filters);
    }

    /**
     * Get article by ID
     */
    public function getById(int $id): Model
    {
        return $this->articleRepository->findOrFail($id);
    }

    /**
     * Bulk store articles for better performance
     */
    private function bulkStoreArticles(array $articlesData, Model $source): int
    {
        try {
            // Filter out invalid articles and get existing URLs
            $validArticles = array_filter($articlesData, fn($article) => !empty($article['url']));

            if (empty($validArticles)) {
                return 0;
            }

            $urls = array_column($validArticles, 'url');
            $existingUrls = $this->articleRepository->getExistingUrls($urls);

            // Filter out existing articles
            $newArticles = array_filter($validArticles, fn($article) => !in_array($article['url'], $existingUrls));

            if (empty($newArticles)) {
                Log::info("All articles already exist in database");
                return 0;
            }

            // Prepare authors and categories in bulk
            $authorMap = $this->prepareAuthors($newArticles);
            $categoryMap = $this->prepareCategories($newArticles);

            // Prepare articles for bulk insert
            $articlesToInsert = [];
            $now = now();

            foreach ($newArticles as $articleData) {
                $authorId = null;
                if (!empty($articleData['author_name'])) {
                    $authorKey = strtolower(trim($articleData['author_name']));
                    $authorId = $authorMap[$authorKey] ?? null;
                }

                $categoryId = null;
                if (!empty($articleData['category'])) {
                    $categoryKey = Str::slug($articleData['category']);
                    $categoryId = $categoryMap[$categoryKey] ?? null;
                }

                $articlesToInsert[] = [
                    'title' => $articleData['title'],
                    'description' => $articleData['description'],
                    'content' => $articleData['content'],
                    'url' => $articleData['url'],
                    'image_url' => $articleData['image_url'],
                    'source_id' => $source->id,
                    'category_id' => $categoryId,
                    'author_id' => $authorId,
                    'published_at' => $articleData['published_at'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Bulk insert articles
            if (!empty($articlesToInsert)) {
                $this->articleRepository->bulkInsert($articlesToInsert);
                return count($articlesToInsert);
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Error in bulk store articles', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 0;
        }
    }

    /**
     * Prepare authors in bulk and return a map of name => id
     */
    private function prepareAuthors(array $articles): array
    {
        $authorNames = [];

        foreach ($articles as $article) {
            if (!empty($article['author_name'])) {
                $authorNames[] = trim($article['author_name']);
            }
        }

        if (empty($authorNames)) {
            return [];
        }

        $authorNames = array_unique($authorNames);

        // Get existing authors
        $existingAuthors = $this->authorRepository->getByNames($authorNames)
            ->keyBy(fn($author) => strtolower($author->name));

        // Create new authors
        $newAuthorNames = array_filter($authorNames, function($name) use ($existingAuthors) {
            return !isset($existingAuthors[strtolower($name)]);
        });

        if (!empty($newAuthorNames)) {
            $now = now();
            $authorsToInsert = array_map(function($name) use ($now) {
                return [
                    'name' => $name,
                    'email' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $newAuthorNames);

            $this->authorRepository->bulkInsert($authorsToInsert);

            // Fetch newly created authors
            $newAuthors = $this->authorRepository->getByNames($newAuthorNames);
            foreach ($newAuthors as $author) {
                $existingAuthors[strtolower($author->name)] = $author;
            }
        }

        // Return map of lowercase name => id
        return $existingAuthors->mapWithKeys(function($author) {
            return [strtolower($author->name) => $author->id];
        })->toArray();
    }

    /**
     * Prepare categories in bulk and return a map of slug => id
     */
    private function prepareCategories(array $articles): array
    {
        $categoryNames = [];

        foreach ($articles as $article) {
            if (!empty($article['category'])) {
                $categoryNames[] = trim($article['category']);
            }
        }

        if (empty($categoryNames)) {
            return [];
        }

        $categoryNames = array_unique($categoryNames);
        $categorySlugs = array_map(fn($name) => Str::slug($name), $categoryNames);

        // Get existing categories
        $existingCategories = $this->categoryRepository->all()
            ->filter(fn($cat) => in_array($cat->slug, $categorySlugs))
            ->keyBy('slug');

        // Create new categories
        $newCategories = [];
        $now = now();

        foreach ($categoryNames as $name) {
            $slug = Str::slug($name);
            if (!isset($existingCategories[$slug])) {
                $newCategories[] = [
                    'name' => $name,
                    'slug' => $slug,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($newCategories)) {
            $this->categoryRepository->bulkInsert($newCategories);

            // Fetch newly created categories
            $newCategorySlugs = array_column($newCategories, 'slug');
            $fetchedCategories = $this->categoryRepository->all()
                ->filter(fn($cat) => in_array($cat->slug, $newCategorySlugs));

            foreach ($fetchedCategories as $category) {
                $existingCategories[$category->slug] = $category;
            }
        }

        // Return map of slug => id
        return $existingCategories->mapWithKeys(function($category) {
            return [$category->slug => $category->id];
        })->toArray();
    }
}
