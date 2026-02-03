<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only compress JSON responses
        if (!$request->expectsJson()) {
            return $response;
        }

        // Check if client accepts gzip
        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (!str_contains($acceptEncoding, 'gzip')) {
            return $response;
        }

        // Get the original content
        $content = $response->getContent();
        
        // Only compress if content is large enough (> 1KB)
        if (strlen($content) < 1024) {
            return $response;
        }

        // Compress the content
        $compressed = gzencode($content, 6); // Compression level 6 (balanced)
        
        if ($compressed === false) {
            return $response;
        }

        // Set the compressed content and headers
        $response->setContent($compressed);
        $response->header('Content-Encoding', 'gzip');
        $response->header('Content-Length', strlen($compressed));

        return $response;
    }
}
