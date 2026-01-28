<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Cache\Fasades;

use WonderPlanet\Domain\Common\Facades\BaseFacade;

//phpcs:disable -- コメントが120行を超えてしまうため無視
/**
 * キャッシュ機構のファサード
 * mstやoprテーブルデータ用のキャッシュ関数
 *
 * CacheDelegatorのメソッドを呼び出す
 *
 * @method static mixed getAll(string $modelClass)
 * @method static mixed getByCustomSearchConditionsWithCustomKey(string $modelClass, string $customSuffixKey, callable $closure)
 * @method static mixed getByCustomSearchConditions(\Illuminate\Database\Eloquent\Builder<covariant \Illuminate\Database\Eloquent\Model> $builder)
 *
 * @see \WonderPlanet\Domain\Cache\Delegators\APCuCacheDelegator
 */
// phpcs:enable
class APCuCache extends BaseFacade
{
    public const FACADE_ACCESSOR = 'laravel-wp-common-cache-apcu';
}
