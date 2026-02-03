<?php

namespace App\Services\NewsProviders;

use App\Facades\Http;
use App\Services\NewsProviders\Config\ProviderConfig;
use App\Services\NewsProviders\Support\QueryBuilder;
use Illuminate\Support\Facades\Log;

class NYTimesProvider implements NewsProviderInterface
{
    private ProviderConfig $config;
    private bool $isSearchResult = false;

    public function __construct()
    {
        $this->config = ProviderConfig::fromConfig('nytimes');
    }

    public function fetchArticles(array $params = []): array
    {
        try {
            if (!$this->config->isValid()) {
                Log::warning('NYTimes API is not properly configured');
                return [];
            }

            $this->isSearchResult = !empty($params['keyword']);

            // Build query parameters using QueryBuilder
            $queryBuilder = QueryBuilder::make()
                ->add('api-key', $this->config->apiKey)
                ->addIfPresent('q', $params['keyword'] ?? null);

            // Add date filters with NYTimes-specific format (YYYYMMDD)
            if (!empty($params['from'])) {
                $queryBuilder->add('begin_date', str_replace('-', '', $params['from']));
            }

            if (!empty($params['to'])) {
                $queryBuilder->add('end_date', str_replace('-', '', $params['to']));
            }

            $endpoint = $this->isSearchResult
                ? '/search/v2/articlesearch.json'
                : '/topstories/v2/home.json';

            // Use Http Facade for the request
            $response = Http::get(
                $this->config->baseUrl . $endpoint,
                $queryBuilder->toArray(),
                $this->config->timeout
            );

            // If request failed, return empty array
            if ($response === false) {
                return [];
            }

            $articles = $this->isSearchResult
                ? ($response['response']['docs'] ?? [])
                : ($response['results'] ?? []);

            // Validate response format
            if (!is_array($articles)) {
                Log::error('NYTimes API returned invalid response format');
                return [];
            }

            return $this->transform($articles);
        } catch (\Exception $e) {
            Log::error('NYTimes API exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function transform(array $articles): array
    {
        return array_map(function ($article) {
            if ($this->isSearchResult) {
                return [
                    'title' => $article['headline']['main'] ?? 'Untitled',
                    'description' => $article['abstract'] ?? null,
                    'content' => $article['lead_paragraph'] ?? null,
                    'url' => $article['web_url'] ?? null,
                    'image_url' => $this->getImageUrl($article),
                    'author_name' => $this->getAuthorName($article),
                    'published_at' => $article['pub_date'] ?? now(),
                    'category' => $article['section_name'] ?? null,
                ];
            }

            return [
                'title' => $article['title'] ?? 'Untitled',
                'description' => $article['abstract'] ?? null,
                'content' => $article['abstract'] ?? null,
                'url' => $article['url'] ?? null,
                'image_url' => $this->getImageUrl($article),
                'author_name' => $article['byline'] ?? 'Unknown',
                'published_at' => $article['published_date'] ?? now(),
                'category' => $article['section'] ?? null,
            ];
        }, $articles);
    }

    private function getImageUrl(array $article): ?string
    {
        if (empty($article['multimedia']) || !is_array($article['multimedia']) || count($article['multimedia']) === 0) {
            return null;
        }

        $imageUrl = $article['multimedia'][0]['url'] ?? null;

        if (!$imageUrl) {
            return null;
        }

        // For search results, prepend NYTimes domain if not already there
        if ($this->isSearchResult && !str_starts_with($imageUrl, 'http')) {
            return 'https://www.nytimes.com/' . $imageUrl;
        }

        return $imageUrl;
    }

    private function getAuthorName(array $article): string
    {
        if (!empty($article['byline']['original'])) {
            return $article['byline']['original'];
        }

        if (!empty($article['byline']['person']) && is_array($article['byline']['person']) && count($article['byline']['person']) > 0) {
            $person = $article['byline']['person'][0];
            $name = trim(($person['firstname'] ?? '') . ' ' . ($person['lastname'] ?? ''));
            
            if (!empty($name)) {
                return $name;
            }
        }

        return 'Unknown';
    }

    public function getProviderName(): string
    {
        return 'New York Times';
    }

    public function getProviderIdentifier(): string
    {
        return 'nytimes';
    }
}
