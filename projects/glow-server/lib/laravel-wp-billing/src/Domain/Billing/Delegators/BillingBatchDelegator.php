<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Delegators;

use WonderPlanet\Domain\Billing\Services\BillingBatchService;
use WonderPlanet\Domain\Currency\Entities\Trigger;

/**
 * コマンド実行からBillingを操作する為のDelegator
 */
class BillingBatchDelegator
{
    public function __construct(
        private BillingBatchService $billingBatchService,
    ) {
    }

    /**
     * 有償一次通貨付与処理
     *  商品購入と同等の処理を実行して有償一次通貨を付与する
     *  管理画面の有償一次通貨付与(本番環境)と同様の処理
     *  バッチで複数のユーザーに付与したい場合などで使用
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param string $deviceId
     * @param string $productSubId
     * @param string $purchasePrice
     * @param string $rawPriceString
     * @param int $vipPoint
     * @param string $currencyCode
     * @param string $receiptUniqueId
     * @param Trigger $trigger
     * @param string $productSubName
     * @param callable $callback
     * @param bool $isSandbox
     * @return void
     * @throws \WonderPlanet\Domain\Billing\Exceptions\WpBillingException
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
     */
    public function purchasedByBatch(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        string $deviceId,
        string $productSubId,
        string $purchasePrice,
        string $rawPriceString,
        int $vipPoint,
        string $currencyCode,
        string $receiptUniqueId,
        Trigger $trigger,
        string $productSubName,
        callable $callback,
        bool $isSandbox
    ): void {
        $this->billingBatchService
            ->purchasedByBatch(
                $userId,
                $osPlatform,
                $billingPlatform,
                $deviceId,
                $productSubId,
                $purchasePrice,
                $rawPriceString,
                $vipPoint,
                $currencyCode,
                $receiptUniqueId,
                $trigger,
                $productSubName,
                $callback,
                $isSandbox
            );
    }

    /**
     * 有償一次通貨回収処理
     *  購入した有償一次通貨を返品した状態にする
     *  管理画面の有償一次通貨回収と同様の処理
     *  バッチで複数のユーザーから回収したい場合などで使用
     *
     * @param string $userId
     * @param string $historyId
     * @param string $deviceId
     * @param string $receiptBundleId
     * @param string $receiptPurchaseToken
     * @param string $receiptUniqueId
     * @param string $triggerDetail
     * @return void
     * @throws \Exception
     */
    public function returnedPurchaseByBatch(
        string $userId,
        string $historyId,
        string $deviceId,
        string $receiptBundleId,
        string $receiptPurchaseToken,
        string $receiptUniqueId,
        string $triggerDetail,
    ): void {
        $this->billingBatchService->returnedPurchaseByBatch(
            $userId,
            $historyId,
            $deviceId,
            $receiptBundleId,
            $receiptPurchaseToken,
            $receiptUniqueId,
            $triggerDetail
        );
    }
}
