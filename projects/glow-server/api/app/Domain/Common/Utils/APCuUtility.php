<?php

declare(strict_types=1);

namespace App\Domain\Common\Utils;

use Illuminate\Support\Facades\Cache;

/**
 * APCuユーティリティ
 */
class APCuUtility
{
    /**
     * キャッシュを取得
     * @param string $key
     * @return mixed
     */
    public static function getCache(string $key): mixed
    {
        return Cache::store('apc')->get($key);
    }

    /**
     * @param int $ttl キャッシュの有効期限（秒）
     */
    public static function saveCacheWithTTL(string $key, mixed $value, int $ttl): void
    {
        Cache::store('apc')->put($key, $value, $ttl);
    }

    public static function saveCacheForever(string $key, mixed $value): void
    {
        Cache::store('apc')->forever($key, $value);
    }

    /**
     * キャッシュを削除
     * @param string $key
     * @return void
     */
    public static function deleteCache(string $key): void
    {
        Cache::store('apc')->forget($key);
    }
}
