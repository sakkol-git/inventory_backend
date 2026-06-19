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
 * Uses Cache Tags if supported, falling back to Tag Versioning if not.
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
     * Check if the current cache store supports native tags.
     */
    public static function supportsTags(): bool
    {
        return Cache::getStore() instanceof \Illuminate\Cache\TaggableStore;
    }

    /**
     * Generate a versioned key for tag fallback emulation.
     */
    private static function getVersionedKey(string $key, array $tags): string
    {
        $versions = [];
        foreach ($tags as $tag) {
            $versions[] = Cache::get("tag_version_{$tag}", 1);
        }
        return $key . '_v_' . implode('_', $versions);
    }

    /**
     * Safely remember a cached value using tags if available, or tag versioning fallback.
     */
    public static function rememberWithTags(array $tags, string $key, int $ttl, \Closure $callback): mixed
    {
        if (self::supportsTags()) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        $versionedKey = self::getVersionedKey($key, $tags);
        return Cache::remember($versionedKey, $ttl, $callback);
    }

    /**
     * Safely flush tags using native tags if available, or tag versioning fallback.
     */
    public static function flushTags(array $tags): void
    {
        if (self::supportsTags()) {
            Cache::tags($tags)->flush();
            return;
        }

        foreach ($tags as $tag) {
            $versionKey = "tag_version_{$tag}";
            if (!Cache::has($versionKey)) {
                Cache::put($versionKey, 1);
            }
            Cache::increment($versionKey);
        }
    }

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

        return self::rememberWithTags($tags, $key, $ttl, $callback);
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
            self::flushTags([$scope]);
        }

        // Always bust the dashboard since it aggregates across scopes
        if (! in_array('dashboard', $scopes, true)) {
            self::flushTags(['dashboard']);
        }
    }

    /**
     * Invalidate everything (e.g. after seeding or major changes).
     */
    public static function flush(): void
    {
        self::flushTags(['api']);
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
