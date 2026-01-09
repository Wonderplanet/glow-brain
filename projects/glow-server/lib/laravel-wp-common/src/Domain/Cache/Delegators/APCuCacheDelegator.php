<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Cache\Delegators;

use Illuminate\Database\Eloquent\Builder;
use WonderPlanet\Domain\Cache\Services\APCuService;

/**
 * キャッシュ機能を提供するDelegator
 */
class APCuCacheDelegator
{
    /**
     * コンストラクタ
     *
     * @param APCuService $apcuService
     */
    public function __construct(
        private readonly APCuService $apcuService,
    ) {
    }

    /**
     * 全件のAPCuキャッシュを取得、作成
     *  キャッシュキーは自動生成
     * @param string $modelClass
     * @return mixed
     */
    public function getAll(string $modelClass): mixed
    {
        return $this->apcuService->getAll($modelClass);
    }

    /**
     * 任意条件のAPCuキャッシュを作成、取得する
     * キャッシュキーは引数で渡したものを使用
     * @param string $modelClass
     * @param string $customSuffixKey
     * @param callable $closure
     * @return mixed
     */
    public function getByCustomSearchConditionsWithCustomKey(
        string $modelClass,
        string $customSuffixKey,
        callable $closure,
    ): mixed {
        return $this->apcuService->getByCustomSearchConditionsWithCustomKey(
            $modelClass,
            $customSuffixKey,
            $closure
        );
    }

    /**
     * 任意条件のAPCuキャッシュを作成、取得する
     * キャッシュキーは$builderから自動生成
     * @param Builder<covariant \Illuminate\Database\Eloquent\Model> $builder
     * @return mixed
     */
    public function getByCustomSearchConditions(Builder $builder): mixed
    {
        return $this->apcuService->getByCustomSearchConditions($builder);
    }
}
