<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Repositories;

use WonderPlanet\Domain\Billing\Models\LogCloseStoreTransaction;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;

/**
 * 購入トランザクション終了ログを管理するRepository
 */
class LogCloseStoreTransactionRepository
{
    /**
     * 購入情報ログを登録する
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param string $productId
     * @param string $mstStoreProductId
     * @param string $productSubId
     * @param string $productSubName
     * @param string $rawReceipt
     * @param string $rawPriceString
     * @param string $currencyCode
     * @param string $receiptUniqueId
     * @param string $receiptBundleId
     * @param string $deviceId
     * @param string $usrStoreProductHistoryId
     * @param string $logStoreId
     * @param string $purchasePrice
     * @param bool $isSandbox
     * @param Trigger $trigger
     * @return string $id
     */
    public function insertCloseStoreTransactionLog(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        string $productId,
        string $mstStoreProductId,
        string $productSubId,
        string $productSubName,
        string $rawReceipt,
        string $rawPriceString,
        string $currencyCode,
        string $receiptUniqueId,
        string $receiptBundleId,
        string $deviceId,
        string $usrStoreProductHistoryId,
        string $logStoreId,
        string $purchasePrice,
        bool $isSandbox,
        Trigger $trigger
    ): string {
        $requestUniqueIdData = CurrencyCommon::getRequestUniqueIdData();
        $logInstance = new LogCloseStoreTransaction();

        $logInstance->usr_user_id = $userId;
        $logInstance->platform_product_id = $productId;
        $logInstance->mst_store_product_id = $mstStoreProductId;
        $logInstance->product_sub_id = $productSubId;
        $logInstance->product_sub_name = $productSubName;
        $logInstance->raw_receipt = $rawReceipt;
        $logInstance->raw_price_string = $rawPriceString;
        $logInstance->currency_code = $currencyCode;
        $logInstance->receipt_unique_id = $receiptUniqueId;
        $logInstance->receipt_bundle_id = $receiptBundleId;
        $logInstance->os_platform = $osPlatform;
        $logInstance->billing_platform = $billingPlatform;
        $logInstance->device_id = $deviceId;
        $logInstance->purchase_price = $purchasePrice;
        $logInstance->is_sandbox = (int)$isSandbox;
        $logInstance->log_store_id = $logStoreId;
        $logInstance->usr_store_product_history_id = $usrStoreProductHistoryId;
        $logInstance->trigger_type = $trigger->triggerType;
        $logInstance->trigger_name = $trigger->triggerName;
        $logInstance->trigger_id = $trigger->triggerId;
        $logInstance->trigger_detail = $trigger->triggerDetail;
        $logInstance->request_id_type = $requestUniqueIdData->getRequestIdType()->value;
        $logInstance->request_id = $requestUniqueIdData->getRequestId();
        $logInstance->nginx_request_id = CurrencyCommon::getFrontRequestId();

        $logInstance->save();

        return $logInstance->id;
    }
}
