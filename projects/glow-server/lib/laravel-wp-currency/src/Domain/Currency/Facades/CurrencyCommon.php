<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Facades;

use App\Domain\Common\Facades\BaseFacade;

/**
 * 通貨基盤の共通処理をまとめたFacade
 *
 * リクエストIDなどを取り扱うので、Repositoryなどからも呼び出される
 * Facadeとして用意するために内容をDelegatorでまとめる
 *
 * @method static \WonderPlanet\Domain\Currency\Entities\RequestIdDataEntity getRequestUniqueIdData()
 * @method static string getFrontRequestId()
 *
 * @see \WonderPlanet\Domain\Currency\Delegators\CurrencyCommonDelegator
 */
class CurrencyCommon extends BaseFacade
{
    public const FACADE_ACCESSOR = 'wp-facade-currency-common';
}
