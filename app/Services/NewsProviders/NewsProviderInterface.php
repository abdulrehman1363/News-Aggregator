<?php

namespace App\Services\NewsProviders;

interface NewsProviderInterface
{
    /**
     * Fetch articles from the news provider
     *
     * @param array $params Query parameters (keyword, category, from, to, page, pageSize)
     * @return array
     */
    public function fetchArticles(array $params = []): array;

    /**
     * Transform raw articles from provider format to standardized format
     *
     * @param array $articles Raw articles from the provider
     * @return array Transformed articles in standardized format
     */
    public function transform(array $articles): array;

    /**
     * Get the provider name
     *
     * @return string
     */
    public function getProviderName(): string;

    /**
     * Get the provider identifier
     *
     * @return string
     */
    public function getProviderIdentifier(): string;
}
