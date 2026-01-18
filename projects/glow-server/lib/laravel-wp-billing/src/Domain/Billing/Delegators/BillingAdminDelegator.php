<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Delegators;

use WonderPlanet\Domain\Billing\Services\BillingAdminService;
use WonderPlanet\Domain\Billing\Services\BillingService;
use WonderPlanet\Domain\Billing\Utils\Excel\BillingLogReport;
use WonderPlanet\Domain\Currency\Entities\Trigger;

class BillingAdminDelegator
{
    public function __construct(
        private BillingService $billingService,
        private BillingAdminService $billingAdminService,
    ) {
    }

    /**
     * 購入許可情報を登録する
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param string $productId
     * @param string $mstStoreProductId
     * @param string $productSubId
     * @param string $deviceId
     * @param string $triggerDetail
     */
    public function insertAllowanceAndLog(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        string $productId,
        string $mstStoreProductId,
        string $productSubId,
        string $deviceId,
        string $triggerDetail
    ): void {
        $this->billingService->insertAllowanceAndLog(
            $userId,
            $osPlatform,
            $billingPlatform,
            $productId,
            $mstStoreProductId,
            $productSubId,
            $deviceId,
            $triggerDetail
        );
    }

    /**
     * LogStoresの初回レコードと現在年月から選択オプションを生成して取得
     *
     * @return mixed[]
     */
    public function getYearMonthOptions(): array
    {
        return $this->billingAdminService->getYearMonthOptions();
    }

    /**
     * 課金ログレポートオブジェクトを取得
     *
     * @param string $year
     * @param string $month
     * @param bool $isIncludeSandbox
     * @param int $limit
     * @return BillingLogReport
     */
    public function getBillingLogReport(
        string $year,
        string $month,
        bool $isIncludeSandbox,
        int $limit
    ): BillingLogReport {
        return $this->billingAdminService
            ->getBillingLogReport(
                $year,
                $month,
                $isIncludeSandbox,
                $limit
            );
    }

    /**
     * 購入処理(管理画面用)
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param string $deviceId
     * @param string $storeProductId
     * @param string $mstStoreProductId
     * @param string $productSubId
     * @param string $purchasePrice
     * @param string $rawPriceString
     * @param int $vipPoint
     * @param string $currencyCode
     * @param string $receiptUniqueId
     * @param Trigger $trigger
     * @param string $loggingProductSubName
     * @param callable $callback
     * @param bool $isSandbox
     * @return void
     * @throws \WonderPlanet\Domain\Billing\Exceptions\WpBillingException
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
     */
    public function purchasedByTool(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        string $deviceId,
        string $storeProductId,
        string $mstStoreProductId,
        string $productSubId,
        string $purchasePrice,
        string $rawPriceString,
        int $vipPoint,
        string $currencyCode,
        string $receiptUniqueId,
        Trigger $trigger,
        string $loggingProductSubName,
        callable $callback,
        bool $isSandbox
    ): void {
        $this->billingAdminService
            ->purchasedByTool(
                $userId,
                $osPlatform,
                $billingPlatform,
                $deviceId,
                $storeProductId,
                $mstStoreProductId,
                $productSubId,
                $purchasePrice,
                $rawPriceString,
                $vipPoint,
                $currencyCode,
                $receiptUniqueId,
                $trigger,
                $loggingProductSubName,
                $callback,
                $isSandbox
            );
    }

    /**
     * 購入情報を返品した状態にする
     *
     * @return void
     */
    public function returnedPurchase(
        string $userId,
        string $historyId,
        string $deviceId,
        string $receiptBundleId,
        string $receiptPurchaseToken,
        string $receiptUniqueId,
        Trigger $trigger,
    ): void {
        $this->billingAdminService->returnedPurchase(
            $userId,
            $historyId,
            $deviceId,
            $receiptBundleId,
            $receiptPurchaseToken,
            $receiptUniqueId,
            $trigger
        );
    }
}
