<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Cache\Delegators;

use Illuminate\Database\Eloquent\Builder;
use WonderPlanet\Domain\Cache\Services\RedisCacheService;

/**
 * キャッシュ機能を提供するDelegator
 */
class RedisCacheDelegator
{
    /**
     * コンストラクタ
     */
    public function __construct(
        private readonly RedisCacheService $redisCacheService,
    ) {
    }

    /**
     * 全件のRedisキャッシュを取得、作成
     * キャッシュキーは自動生成
     * $isDeleteCacheがtrueの時、キャッシュを削除してから取得を行う
     *
     * @param string $modelClass
     * @param bool $isDeleteCache
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    public function getRedisCacheAll(string $modelClass, bool $isDeleteCache = false)
    {
        return $this->redisCacheService->getRedisCacheAll($modelClass, $isDeleteCache);
    }

    /**
     * 任意条件のRedisキャッシュを作成、取得する
     * キャッシュキーは引数で渡したものを使用
     *
     * @param string $modelClass
     * @param string $customSuffixKey
     * @param callable $closure
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    public function getRedisCacheByCustomSearchConditionsWithCustomKey(
        string $modelClass,
        string $customSuffixKey,
        callable $closure,
    ) {
        return $this->redisCacheService->getRedisCacheByCustomSearchConditionsWithCustomKey(
            $modelClass,
            $customSuffixKey,
            $closure
        );
    }

    /**
     * 任意条件のRedisキャッシュを作成、取得する
     * キャッシュキーは$builderから自動生成
     *
     * @param Builder<covariant \Illuminate\Database\Eloquent\Model> $builder
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    public function getRedisCacheByCustomSearchConditions(Builder $builder)
    {
        return $this->redisCacheService->getRedisCacheByCustomSearchConditions($builder);
    }
}
