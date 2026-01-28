<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Cache\Fasades;

use WonderPlanet\Domain\Common\Facades\BaseFacade;

//phpcs:disable -- コメントが120行を超えてしまうため無視
/**
 * キャッシュ機構のファサード
 * mngテーブルデータ用のキャッシュ関数
 *
 * RedisCacheDelegatorのメソッドを呼び出す
 *
 * @method static mixed getRedisCacheByCustomSearchConditionsWithCustomKey(string $modelClass, string $customSuffixKey, callable $closure)
 * @method static mixed getRedisCacheByCustomSearchConditions(\Illuminate\Database\Eloquent\Builder<covariant \Illuminate\Database\Eloquent\Model> $builder)
 * @method static mixed getRedisCacheAll(string $modelClass, bool $isDeleteCache = false)
 *
 * @see \WonderPlanet\Domain\Cache\Delegators\RedisCacheDelegator
 */
// phpcs:enable
class RedisCache extends BaseFacade
{
    public const FACADE_ACCESSOR = 'laravel-wp-common-cache-redis';
}
