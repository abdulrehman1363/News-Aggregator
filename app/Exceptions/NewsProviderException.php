<?php

namespace App\Exceptions;

use Exception;

class NewsProviderException extends Exception
{
    public static function fetchFailed(string $provider, \Throwable $previous = null): self
    {
        return new self(
            "Unable to fetch news articles at this time. Please try again later.",
            500,
            $previous
        );
    }

    public static function invalidApiKey(string $provider): self
    {
        return new self(
            "News service is temporarily unavailable.",
            503
        );
    }

    public static function rateLimitExceeded(string $provider): self
    {
        return new self(
            "Too many requests. Please try again later.",
            429
        );
    }

    public static function invalidResponse(string $provider): self
    {
        return new self(
            "Unable to process news data. Please try again later.",
            500
        );
    }
}
