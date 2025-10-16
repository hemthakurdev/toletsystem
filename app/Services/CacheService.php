<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Cache keys constants
     */
    const PROPERTIES_CACHE_KEY = 'properties:list';
    const TENANTS_CACHE_KEY = 'tenants:list';
    const INVOICES_CACHE_KEY = 'invoices:list';
    const ANALYTICS_CACHE_KEY = 'analytics:overview';
    const USER_STATS_CACHE_KEY = 'user:stats';
    const PROPERTY_STATS_CACHE_KEY = 'property:stats';

    /**
     * Cache TTL constants (in seconds)
     */
    const SHORT_TTL = 300;    // 5 minutes
    const MEDIUM_TTL = 1800;  // 30 minutes
    const LONG_TTL = 3600;    // 1 hour
    const VERY_LONG_TTL = 86400; // 24 hours

    /**
     * Get cached data or execute callback and cache result
     */
    public function remember(string $key, int $ttl, callable $callback, array $tags = []): mixed
    {
        try {
            if (!empty($tags)) {
                return Cache::tags($tags)->remember($key, $ttl, $callback);
            }
            
            return Cache::remember($key, $ttl, $callback);
        } catch (\Exception $e) {
            Log::error("Cache error for key {$key}: " . $e->getMessage());
            return $callback();
        }
    }

    /**
     * Cache data with tags
     */
    public function putWithTags(string $key, mixed $value, int $ttl, array $tags = []): bool
    {
        try {
            if (!empty($tags)) {
                return Cache::tags($tags)->put($key, $value, $ttl);
            }
            
            return Cache::put($key, $value, $ttl);
        } catch (\Exception $e) {
            Log::error("Cache put error for key {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalidate cache by tags
     */
    public function invalidateByTags(array $tags): bool
    {
        try {
            Cache::tags($tags)->flush();
            Log::info("Cache invalidated for tags: " . implode(', ', $tags));
            return true;
        } catch (\Exception $e) {
            Log::error("Cache invalidation error for tags " . implode(', ', $tags) . ": " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalidate cache by pattern
     */
    public function invalidateByPattern(string $pattern): bool
    {
        try {
            // This would require Redis or similar cache driver
            // For now, we'll log the pattern for manual cleanup
            Log::info("Cache invalidation requested for pattern: {$pattern}");
            return true;
        } catch (\Exception $e) {
            Log::error("Cache pattern invalidation error for {$pattern}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        try {
            $driver = config('cache.default');
            
            return [
                'driver' => $driver,
                'status' => 'active',
                'memory_usage' => $this->getMemoryUsage(),
                'hit_rate' => $this->getHitRate(),
                'total_keys' => $this->getTotalKeys(),
            ];
        } catch (\Exception $e) {
            Log::error("Cache stats error: " . $e->getMessage());
            return [
                'driver' => 'unknown',
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Clear all cache
     */
    public function clearAll(): bool
    {
        try {
            Cache::flush();
            Log::info("All cache cleared");
            return true;
        } catch (\Exception $e) {
            Log::error("Cache clear error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Warm up cache with frequently accessed data
     */
    public function warmUp(): bool
    {
        try {
            // This would preload frequently accessed data
            Log::info("Cache warm-up initiated");
            
            // Example: Preload user statistics, property counts, etc.
            // Implementation would depend on specific application needs
            
            return true;
        } catch (\Exception $e) {
            Log::error("Cache warm-up error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get memory usage (mock implementation)
     */
    private function getMemoryUsage(): string
    {
        // This would be implemented based on cache driver
        return 'N/A';
    }

    /**
     * Get cache hit rate (mock implementation)
     */
    private function getHitRate(): string
    {
        // This would be implemented based on cache driver
        return 'N/A';
    }

    /**
     * Get total cache keys (mock implementation)
     */
    private function getTotalKeys(): int
    {
        // This would be implemented based on cache driver
        return 0;
    }

    /**
     * Cache organization-specific data
     */
    public function rememberOrgData(int $orgId, string $key, int $ttl, callable $callback): mixed
    {
        $cacheKey = "org:{$orgId}:{$key}";
        $tags = ["org:{$orgId}"];
        
        return $this->remember($cacheKey, $ttl, $callback, $tags);
    }

    /**
     * Invalidate organization cache
     */
    public function invalidateOrgCache(int $orgId): bool
    {
        return $this->invalidateByTags(["org:{$orgId}"]);
    }

    /**
     * Cache user-specific data
     */
    public function rememberUserData(int $userId, string $key, int $ttl, callable $callback): mixed
    {
        $cacheKey = "user:{$userId}:{$key}";
        $tags = ["user:{$userId}"];
        
        return $this->remember($cacheKey, $ttl, $callback, $tags);
    }

    /**
     * Invalidate user cache
     */
    public function invalidateUserCache(int $userId): bool
    {
        return $this->invalidateByTags(["user:{$userId}"]);
    }
}
