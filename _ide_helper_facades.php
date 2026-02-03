<?php

namespace Illuminate\Support\Facades {
    /**
     * @method static array|false get(string $url, array $params = [], int $timeout = 30)
     * 
     * @see \App\Services\Http\HttpClient
     */
    class Http extends Facade {}
}

namespace App\Facades {
    /**
     * @method static array|false get(string $url, array $params = [], int $timeout = 30)
     * 
     * @see \App\Services\Http\HttpClient
     */
    class Http extends \Illuminate\Support\Facades\Facade {}
}
