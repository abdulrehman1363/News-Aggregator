<?php

namespace App\Services\NewsProviders\Config;

readonly class ProviderConfig
{
    public function __construct(
        public string $apiKey,
        public string $baseUrl,
        public int $timeout = 30,
        public int $defaultPageSize = 50,
        public string $language = 'en'
    ) {}

    /**
     * Create configuration from Laravel config
     */
    public static function fromConfig(string $provider): self
    {
        return new self(
            apiKey: config("services.{$provider}.key") ?? '',
            baseUrl: config("services.{$provider}.base_url") ?? '',
            timeout: config("services.{$provider}.timeout", 30),
            defaultPageSize: config("services.{$provider}.page_size", 50),
            language: config("services.{$provider}.language", 'en')
        );
    }

    /**
     * Check if the provider is properly configured
     */
    public function isValid(): bool
    {
        return !empty($this->apiKey) && !empty($this->baseUrl);
    }
}
