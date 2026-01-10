<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Cache\Utils;

use Illuminate\Support\Facades\Cache;

/**
 * Redisキャッシュユーティリティ
 */
class RedisCacheUtility
{
    /**
     * キャッシュを取得
     * @param string $key
     * @return mixed
     */
    public static function getCache(string $key): mixed
    {
        return Cache::get($key);
    }

    /**
     * キャッシュを保存
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function saveCache(string $key, mixed $value): void
    {
        Cache::forever($key, $value);
    }

    /**
     * TTL付きでキャッシュを保存
     * @param string $key
     * @param mixed $value
     * @param int $ttlSeconds TTL（秒）
     * @return void
     */
    public static function saveCacheWithTtl(string $key, mixed $value, int $ttlSeconds): void
    {
        Cache::put($key, $value, $ttlSeconds);
    }

    /**
     * キャッシュを削除
     * @param string $key
     * @return void
     */
    public static function deleteCache(string $key): void
    {
        Cache::forget($key);
    }
}
