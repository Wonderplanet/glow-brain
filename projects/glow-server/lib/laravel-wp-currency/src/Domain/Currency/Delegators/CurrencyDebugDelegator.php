<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Delegators;

use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Services\CurrencyDebugService;

/**
 * デバッグ機能用のDelegator
 *
 * 本番環境では使用しない想定となっている。
 * 開発環境以外で使用するとエラーになる。
 */
class CurrencyDebugDelegator
{
    public function __construct(
        private CurrencyDebugService $currencyDebugService,
    ) {
    }

    /**
     * 有償一次通貨を追加する
     *
     * currency_summaryの更新も行う
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param integer $amount
     * @param string $currencyCode
     * @param string $price
     * @param integer $vipPoint
     * @param string $receiptUniqueId
     * @param boolean $isSandbox
     * @return string 登録したcurrency_paidのID
     */
    public function addCurrencyPaid(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $amount,
        string $currencyCode,
        string $price,
        int $vipPoint,
        string $receiptUniqueId,
        bool $isSandbox,
        Trigger $trigger,
    ): string {
        return $this->currencyDebugService->addCurrencyPaid(
            $userId,
            $osPlatform,
            $billingPlatform,
            $amount,
            $currencyCode,
            $price,
            $vipPoint,
            $receiptUniqueId,
            $isSandbox,
            $trigger,
        );
    }
}
