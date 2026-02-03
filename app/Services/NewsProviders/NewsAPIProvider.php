<?php

namespace App\Services\NewsProviders;

use App\Facades\Http;
use App\Services\NewsProviders\Config\ProviderConfig;
use App\Services\NewsProviders\Contracts\NewsProviderInterface;
use App\Services\NewsProviders\Support\QueryBuilder;
use Illuminate\Support\Facades\Log;

class NewsAPIProvider implements NewsProviderInterface
{
    private ProviderConfig $config;

    public function __construct()
    {
        $this->config = ProviderConfig::fromConfig('newsapi');
    }

    public function fetchArticles(array $params = []): array
    {
        try {
            if (!$this->config->isValid()) {
                Log::warning('NewsAPI is not properly configured');
                return [];
            }

            // Build query parameters using QueryBuilder
            $queryParams = QueryBuilder::make()
                ->add('apiKey', $this->config->apiKey)
                ->add('language', $this->config->language)
                ->add('pageSize', $params['pageSize'] ?? $this->config->defaultPageSize)
                ->add('page', $params['page'] ?? 1)
                ->addIfPresent('q', $params['keyword'] ?? null)
                ->addIfPresent('category', $params['category'] ?? null)
                ->addIfPresent('from', $params['from'] ?? null)
                ->addIfPresent('to', $params['to'] ?? null)
                ->toArray();

            $endpoint = !empty($params['keyword']) ? '/everything' : '/top-headlines';

            // Use Http Facade for the request
            $response = Http::get(
                $this->config->baseUrl . $endpoint,
                $queryParams,
                $this->config->timeout
            );

            // If request failed, return empty array
            if ($response === false) {
                return [];
            }

            // Validate response format
            if (!isset($response['articles']) || !is_array($response['articles'])) {
                Log::error('NewsAPI returned invalid response format');
                return [];
            }

            return $this->transform($response['articles']);
        } catch (\Exception $e) {
            Log::error('NewsAPI exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function transform(array $articles): array
    {
        return array_map(function ($article) {
            return [
                'title' => $article['title'] ?? 'Untitled',
                'description' => $article['description'] ?? null,
                'content' => $article['content'] ?? null,
                'url' => $article['url'] ?? null,
                'image_url' => $article['urlToImage'] ?? null,
                'author_name' => $article['author'] ?? 'Unknown',
                'published_at' => $article['publishedAt'] ?? now(),
                'category' => null,
            ];
        }, $articles);
    }

    public function getProviderName(): string
    {
        return 'NewsAPI';
    }

    public function getProviderIdentifier(): string
    {
        return 'newsapi';
    }
}
