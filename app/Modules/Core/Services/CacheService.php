<?php

declare(strict_types=1);

namespace App\Modules\Core\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Centralized cache invalidation for API responses.
 *
 * Each "scope" corresponds to a group of related endpoints.
 * When data changes, call invalidate() with the relevant scope(s).
 *
 * Uses Redis cache tags for reliable, atomic invalidation.
 */
class CacheService
{
    /**
     * Default TTL in seconds (5 minutes).
     */
    public const DEFAULT_TTL = 300;

    /**
     * Long TTL for expensive queries (15 minutes).
     */
    public const LONG_TTL = 900;

    /**
     * Get data from cache or compute it.
     *
     * @template T
     *
     * @param  \Closure(): T  $callback
     * @return T
     */
    public static function remember(string $key, int $ttl, \Closure $callback): mixed
    {
        $scope = self::extractScope($key);
        $tags = $scope ? [$scope, 'api'] : ['api'];

        return Cache::tags($tags)->remember($key, $ttl, $callback);
    }

    /**
     * Build a cache key for a specific scope + params.
     */
    public static function key(string $scope, array $params = []): string
    {
        $suffix = $params === [] ? '' : ':'.md5(serialize($params));

        return "api:{$scope}{$suffix}";
    }

    /**
     * Invalidate all cached data for the given scope(s).
     */
    public static function invalidate(string ...$scopes): void
    {
        foreach ($scopes as $scope) {
            Cache::tags([$scope])->flush();
        }

        // Always bust the dashboard since it aggregates across scopes
        if (! in_array('dashboard', $scopes, true)) {
            Cache::tags(['dashboard'])->flush();
        }
    }

    /**
    /**
     * Invalidate everything (e.g. after seeding or major changes).
     */
    public static function flush(): void
    {
        Cache::tags(['api'])->flush();
    }

    /**
     * Extract scope from a cache key.
     */
    private static function extractScope(string $key): ?string
    {
        if (! str_starts_with($key, 'api:')) {
            return null;
        }

        $withoutPrefix = substr($key, 4);

        return str_contains($withoutPrefix, ':')
            ? substr($withoutPrefix, 0, strpos($withoutPrefix, ':'))
            : $withoutPrefix;
    }
}
