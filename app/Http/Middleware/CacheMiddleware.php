<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $cacheKey = null, int $ttl = 300): mixed
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Generate cache key if not provided
        if (!$cacheKey) {
            $cacheKey = $this->generateCacheKey($request);
        }

        // Check if data exists in cache
        $cachedResponse = Cache::get($cacheKey);
        
        if ($cachedResponse) {
            Log::info("Cache hit for key: {$cacheKey}");
            return response()->json($cachedResponse);
        }

        // Process request
        $response = $next($request);

        // Cache successful responses
        if ($response->getStatusCode() === 200) {
            $responseData = $response->getData(true);
            
            // Only cache if response has data
            if (!empty($responseData)) {
                Cache::put($cacheKey, $responseData, $ttl);
                Log::info("Cached response for key: {$cacheKey}, TTL: {$ttl}s");
            }
        }

        return $response;
    }

    /**
     * Generate cache key from request
     */
    private function generateCacheKey(Request $request): string
    {
        $user = $request->user();
        $userId = $user ? $user->id : 'guest';
        $orgId = $user && $user->org_id ? $user->org_id : 'no-org';
        
        $key = sprintf(
            'api:%s:%s:%s:%s',
            $request->path(),
            $userId,
            $orgId,
            md5($request->getQueryString() ?? '')
        );

        return $key;
    }
}
