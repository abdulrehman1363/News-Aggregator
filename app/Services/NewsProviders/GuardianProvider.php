<?php

namespace App\Services\NewsProviders;

use App\Facades\Http;
use App\Services\NewsProviders\Config\ProviderConfig;
use App\Services\NewsProviders\Support\QueryBuilder;
use Illuminate\Support\Facades\Log;

class GuardianProvider implements NewsProviderInterface
{
    private ProviderConfig $config;

    public function __construct()
    {
        $this->config = ProviderConfig::fromConfig('guardian');
    }

    public function fetchArticles(array $params = []): array
    {
        try {
            if (!$this->config->isValid()) {
                Log::warning('Guardian API is not properly configured');
                return [];
            }

            // Build query parameters using QueryBuilder
            $queryParams = QueryBuilder::make()
                ->add('api-key', $this->config->apiKey)
                ->add('show-fields', 'thumbnail,bodyText,byline')
                ->add('page-size', $params['pageSize'] ?? $this->config->defaultPageSize)
                ->add('page', $params['page'] ?? 1)
                ->addIfPresent('q', $params['keyword'] ?? null)
                ->addIfPresent('section', $params['category'] ?? null)
                ->addIfPresent('from-date', $params['from'] ?? null)
                ->addIfPresent('to-date', $params['to'] ?? null)
                ->toArray();

            // Use Http Facade for the request
            $response = Http::get(
                $this->config->baseUrl . '/search',
                $queryParams,
                $this->config->timeout
            );

            // If request failed, return empty array
            if ($response === false) {
                return [];
            }

            // Validate response format
            if (!isset($response['response']['results']) || !is_array($response['response']['results'])) {
                Log::error('Guardian API returned invalid response format');
                return [];
            }

            return $this->transform($response['response']['results']);
        } catch (\Exception $e) {
            Log::error('Guardian API exception', [
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
                'title' => $article['webTitle'] ?? 'Untitled',
                'description' => $article['fields']['bodyText'] ?? null,
                'content' => $article['fields']['bodyText'] ?? null,
                'url' => $article['webUrl'] ?? null,
                'image_url' => $article['fields']['thumbnail'] ?? null,
                'author_name' => $article['fields']['byline'] ?? 'Unknown',
                'published_at' => $article['webPublicationDate'] ?? now(),
                'category' => $article['sectionName'] ?? null,
            ];
        }, $articles);
    }

    public function getProviderName(): string
    {
        return 'The Guardian';
    }

    public function getProviderIdentifier(): string
    {
        return 'guardian';
    }
}
