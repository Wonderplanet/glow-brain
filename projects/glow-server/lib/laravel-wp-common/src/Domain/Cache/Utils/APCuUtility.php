<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Cache\Utils;

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
     * キャッシュを保存
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function saveCache(string $key, mixed $value): void
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
