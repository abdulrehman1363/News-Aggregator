<?php

namespace App\Services\Http;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpClient
{
    /**
     * Make an HTTP GET request with automatic error handling
     *
     * @param string $url
     * @param array $params
     * @param int $timeout
     * @return array|false Returns array on success, false on failure
     */
    public function get(string $url, array $params = [], int $timeout = 30): array|false
    {
        try {
            $response = Http::timeout($timeout)->get($url, $params);

            // Check for successful response
            if ($response->successful()) {
                return $response->json() ?? [];
            }

            // Log failed requests with details
            $this->logHttpError($url, $response->status(), $response->body());

            return false;
        } catch (\Exception $e) {
            Log::error('HTTP request exception', [
                'url' => $url,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Log HTTP errors with context
     */
    private function logHttpError(string $url, int $status, string $body): void
    {
        Log::error('HTTP request failed', [
            'url' => $url,
            'status' => $status,
            'body' => strlen($body) > 500 ? substr($body, 0, 500) . '...' : $body,
        ]);
    }
}
