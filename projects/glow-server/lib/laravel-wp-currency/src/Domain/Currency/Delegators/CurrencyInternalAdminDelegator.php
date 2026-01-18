<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Delegators;

use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Models\UsrCurrencyPaid;
use WonderPlanet\Domain\Currency\Services\CurrencyAdminService;

/**
 * 管理画面用の課金・通過基盤内の内部向けDelegator
 * 主にwp-billingから呼び出される
 */
class CurrencyInternalAdminDelegator
{
    public function __construct(
        private CurrencyAdminService $currencyAdminService,
    ) {
    }

    /**
     * 有償一次通貨の回収(減算)
     *
     * @param string $userId
     * @param string $billingPlatform
     * @param string $collectTargetReceiptUniqueId
     * @param string $receiptUniqueId
     * @param bool $isSandbox
     * @param Trigger $trigger
     * @return UsrCurrencyPaid
     * @throws \Exception
     */
    public function collectCurrencyPaid(
        string $userId,
        string $billingPlatform,
        string $collectTargetReceiptUniqueId,
        string $receiptUniqueId,
        bool $isSandbox,
        Trigger $trigger
    ): UsrCurrencyPaid {
        return $this->currencyAdminService->collectCurrencyPaid(
            $userId,
            $billingPlatform,
            $collectTargetReceiptUniqueId,
            $receiptUniqueId,
            $isSandbox,
            $trigger
        );
    }

    /**
     * @param string $userId
     * @param string $osPlatform
     * @param int $amount
     * @param Trigger $trigger
     * @return void
     */
    public function collectFreeCurrencyByCollectPaid(
        string $userId,
        string $osPlatform,
        int $amount,
        Trigger $trigger
    ): void {
        $this->currencyAdminService->collectFreeCurrencyByCollectPaid(
            $userId,
            $osPlatform,
            $amount,
            $trigger
        );
    }
}
