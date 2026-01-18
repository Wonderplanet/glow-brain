<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Admin\Operators;

use Illuminate\Support\Facades\Cache;

class CacheOperator
{
    private const DEFAULT_CACHE_LIMIT = 60;
    protected int $cache_limit = 0;

    public function __construct(int $cache_limit = self::DEFAULT_CACHE_LIMIT)
    {
        $this->cache_limit = $cache_limit;
    }

    public function getApcCache(string $key): mixed
    {
        return Cache::store('apc')->get($key, null);
    }

    public function saveForeverApcCache(string $key, mixed $value): void
    {
        Cache::store('apc')->forever($key, $value);
    }

    public function saveApcCache(string $key, mixed $value): void
    {
        Cache::store('apc')->put($key, $value, $this->cache_limit);
    }

    public function deleteApcCache(string $key): void
    {
        Cache::store('apc')->forget($key);
    }
}
