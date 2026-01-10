<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Delegators;

use WonderPlanet\Domain\Currency\Entities\RequestIdDataEntity;
use WonderPlanet\Domain\Currency\Services\CurrencyRequestIdService;

/**
 * 通貨の共通Delegator
 *
 * 主に課金・通貨基盤内で使用する
 * Facadeから呼び出すためDelegatorを作成
 */
class CurrencyCommonDelegator
{
    public function __construct(
        private readonly CurrencyRequestIdService $currencyRequestIdService,
    ) {
    }

    /**
     * APIリクエストごとにユニークになるIDを取得する
     *
     * @return RequestIdDataEntity
     */
    public function getRequestUniqueIdData(): RequestIdDataEntity
    {
        return $this->currencyRequestIdService->getRequestUniqueIdData();
    }

    /**
     * nginxなどのフロントにあるシステムからのリクエストIDを取得する
     *
     * @return string
     */
    public function getFrontRequestId(): string
    {
        return $this->currencyRequestIdService->getFrontRequestId();
    }
}
