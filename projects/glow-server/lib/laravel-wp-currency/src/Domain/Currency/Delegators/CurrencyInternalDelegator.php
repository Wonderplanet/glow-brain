<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Delegators;

use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Models\UsrCurrencyPaid;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

/**
 * 課金・通過基盤内の内部向けDelegator
 * 主にwp-billingから呼び出される
 *
 */
class CurrencyInternalDelegator
{
    public function __construct(
        private CurrencyService $currencyService,
    ) {
    }

    /**
     * 有償一次通貨を追加する
     *
     * currency_summaryの更新も行う
     *
     * 課金ライブラリ内で使用する想定のため、Eloquentモデルを返す
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
     * @return UsrCurrencyPaid
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
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
    ): UsrCurrencyPaid {
        return $this->currencyService->addCurrencyPaid(
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

    /**
     * 一次通貨を追加する際のバリデーション
     *
     * @param string $userId
     * @param integer $addPaidAmount
     * @param integer $addFreeAmount
     * @return void
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
     */
    public function validateAddCurrency(string $userId, int $addPaidAmount, int $addFreeAmount)
    {
        $this->currencyService->validateAddCurrency($userId, $addPaidAmount, $addFreeAmount);
    }

    /**
     * WebStore用に有償一次通貨を追加する
     *
     * currency_summaryの更新も行う
     *
     * 課金ライブラリ内で使用する想定のため、Eloquentモデルを返す
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param int $amount
     * @param string $currencyCode
     * @param string $purchasePrice 購入価格
     * @param string $receiptUniqueId べき等性キーとなるレシートID（複数アイテム対応のため連番付き）
     * @param bool $isSandbox サンドボックスフラグ
     * @param Trigger $trigger
     * @return UsrCurrencyPaid
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
     */
    public function addPaidCurrencyForWebStore(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $amount,
        string $currencyCode,
        string $purchasePrice,
        string $receiptUniqueId,
        bool $isSandbox,
        Trigger $trigger,
    ): UsrCurrencyPaid {
        return $this->currencyService->addCurrencyPaid(
            $userId,
            $osPlatform,
            $billingPlatform,
            $amount,
            $currencyCode,
            $purchasePrice,
            0,
            $receiptUniqueId,
            $isSandbox,
            $trigger,
        );
    }
}
