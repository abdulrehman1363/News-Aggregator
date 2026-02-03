<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array|false get(string $url, array $params = [], int $timeout = 30)
 * 
 * @see \App\Services\Http\HttpClient
 */
class Http extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'http.client';
    }
}
