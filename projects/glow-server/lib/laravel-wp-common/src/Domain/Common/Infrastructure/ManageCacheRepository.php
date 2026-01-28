<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Infrastructure;

use Illuminate\Database\Eloquent\Builder;
use WonderPlanet\Domain\Cache\Fasades\RedisCache;

class ManageCacheRepository
{
    public function __construct()
    {
    }

    /**
     * 全件のキャッシュを取得、作成
     * キャッシュキーは自動生成
     *
     * @param string $modelClass
     * @return mixed
     */
    public function get(string $modelClass): mixed
    {
        return RedisCache::getRedisCacheAll($modelClass);
    }


    /**
     * 任意条件のキャッシュを作成、取得する
     * キャッシュキーは引数で渡したものを使用
     *
     * @param string $modelClass
     * @param string $customSuffixKey
     * @param callable $closure
     * @return mixed
     */
    public function getRedisCacheByCustomSearchConditionsWithCustomKey(
        string $modelClass,
        string $customSuffixKey,
        callable $closure,
    ): mixed {
        return RedisCache::getRedisCacheByCustomSearchConditionsWithCustomKey(
            $modelClass,
            $customSuffixKey,
            $closure
        );
    }

    /**
     * 任意条件のキャッシュを作成、取得する
     * キャッシュキーは$builderから自動生成
     *
     * @param Builder<covariant \Illuminate\Database\Eloquent\Model> $builder
     * @return mixed
     */
    public function getRedisCacheByCustomSearchConditions(Builder $builder): mixed
    {
        return RedisCache::getRedisCacheByCustomSearchConditions($builder);
    }
}
