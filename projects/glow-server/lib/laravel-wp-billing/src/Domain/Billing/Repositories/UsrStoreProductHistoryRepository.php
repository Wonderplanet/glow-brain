<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Repositories;

use Illuminate\Support\Collection;
use WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory;

/**
 * ユーザーのショップ購入履歴を管理するRepository
 */
class UsrStoreProductHistoryRepository
{
    /**
     * idからストア購入履歴を取得する
     *
     * @param string $id
     * @return UsrStoreProductHistory|null
     */
    public function findById(string $id): ?UsrStoreProductHistory
    {
        return UsrStoreProductHistory::query()
            ->find($id);
    }

    /**
     * レシートIDとプラットフォームからユーザーのショップ購入履歴を取得する
     *
     * @param string $receiptUniqueId
     * @param string $billingPlatform
     * @return UsrStoreProductHistory|null
     */
    public function findByReceiptUniqueIdAndBillingPlatform(
        string $receiptUniqueId,
        string $billingPlatform
    ): ?UsrStoreProductHistory {
        return UsrStoreProductHistory::query()
            ->where('receipt_unique_id', $receiptUniqueId)
            ->where('billing_platform', $billingPlatform)
            ->first();
    }

    /**
     * 特定の課金プラットフォームにあるreceipt_unique_idをユーザーIDとレシートID配列を条件に検索
     *
     * @param string $userId
     * @param string $billingPlatform
     * @param array<int, string> $receiptUniqueIds
     * @return \Illuminate\Support\Collection<int, UsrStoreProductHistory>
     */
    public function findByUserIdAndReceiptUniqueIdsFromBillingPlatform(
        string $userId,
        string $billingPlatform,
        array $receiptUniqueIds
    ): Collection {
        return UsrStoreProductHistory::query()
            ->where('usr_user_id', $userId)
            ->where('billing_platform', $billingPlatform)
            ->whereIn('receipt_unique_id', $receiptUniqueIds)
            ->get();
    }

    /**
     * ユーザーのショップ購入履歴を登録する
     *
     * @param string $userId
     * @param string $deviceId
     * @param integer $age
     * @param string $receiptUniqueId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param string $productSubId
     * @param string $platformProductId
     * @param string $mstStoreProductId
     * @param string $currencyCode
     * @param string $receiptBundleId
     * @param string $receiptPurchaseToken
     * @param integer $paidAmount
     * @param integer $freeAmount
     * @param string $purchasePrice
     * @param string $pricePerAmount
     * @param integer $vipPoint
     * @param boolean $isSandbox
     * @return string $usrStoreProductHistoryId
     */
    public function insertStoreProductHistory(
        string $userId,
        string $deviceId,
        int $age,
        string $receiptUniqueId,
        string $osPlatform,
        string $billingPlatform,
        string $productSubId,
        string $platformProductId,
        string $mstStoreProductId,
        string $currencyCode,
        string $receiptBundleId,
        string $receiptPurchaseToken,
        int $paidAmount,
        int $freeAmount,
        string $purchasePrice,
        string $pricePerAmount,
        int $vipPoint,
        bool $isSandbox
    ): string {
        $usrStoreProductHistory = new UsrStoreProductHistory();

        $usrStoreProductHistory->usr_user_id = $userId;
        $usrStoreProductHistory->device_id = $deviceId;
        $usrStoreProductHistory->age = $age;
        $usrStoreProductHistory->receipt_unique_id = $receiptUniqueId;
        $usrStoreProductHistory->os_platform = $osPlatform;
        $usrStoreProductHistory->billing_platform = $billingPlatform;
        $usrStoreProductHistory->product_sub_id = $productSubId;
        $usrStoreProductHistory->platform_product_id = $platformProductId;
        $usrStoreProductHistory->mst_store_product_id = $mstStoreProductId;
        $usrStoreProductHistory->currency_code = $currencyCode;
        $usrStoreProductHistory->receipt_bundle_id = $receiptBundleId;
        $usrStoreProductHistory->receipt_purchase_token = $receiptPurchaseToken;
        $usrStoreProductHistory->paid_amount = $paidAmount;
        $usrStoreProductHistory->free_amount = $freeAmount;
        $usrStoreProductHistory->purchase_price = $purchasePrice;
        $usrStoreProductHistory->price_per_amount = $pricePerAmount;
        $usrStoreProductHistory->vip_point = $vipPoint;
        $usrStoreProductHistory->is_sandbox = (int)$isSandbox;

        $usrStoreProductHistory->save();

        return $usrStoreProductHistory->id;
    }

    /**
     * ユーザーの獲得したVIPポイントを履歴から集計する
     *
     * sandboxのものは集計しない
     *
     * @param string $userId
     * @return integer
     */
    public function sumVipPoint(string $userId): int
    {
        // vip_pointは整数値なので、intにキャストして返す
        return (int)UsrStoreProductHistory::query()
            ->where('usr_user_id', $userId)
            ->where('is_sandbox', 0)
            ->sum('vip_point');
    }

    /**
     * ユーザーのショップ購入履歴を論理削除する
     *
     * @param string $userId
     * @return void
     */
    public function softDeleteByUserId(string $userId): void
    {
        UsrStoreProductHistory::query()
            ->where('usr_user_id', $userId)
            ->delete();
    }

    /**
     * ユーザーのショップ購入履歴があればtrueを返す
     *
     * @param string $userId
     * @return boolean
     */
    public function hasStoreProductHistory(string $userId): bool
    {
        return UsrStoreProductHistory::query()
            ->where('usr_user_id', $userId)
            ->exists();
    }

    /**
     * WebStore用のショップ購入履歴を登録する
     *
     * @param string $userId
     * @param int $orderId
     * @param string|null $invoiceId
     * @param string $transactionId
     * @param string $osPlatform
     * @param string $deviceId
     * @param integer $age
     * @param string $productSubId
     * @param string $platformProductId
     * @param string $mstStoreProductId
     * @param string|null $currencyCode
     * @param string $receiptBundleId
     * @param string $receiptPurchaseToken
     * @param integer $paidAmount
     * @param integer $freeAmount
     * @param string $purchasePrice
     * @param string $pricePerAmount
     * @param integer $vipPoint
     * @param boolean $isSandbox
     * @param string $billingPlatform
     * @return string $usrStoreProductHistoryId
     */
    public function insertStoreProductHistoryForWebStore(
        string $userId,
        string $receiptUniqueId,
        int $orderId,
        ?string $invoiceId,
        string $transactionId,
        string $osPlatform,
        string $deviceId,
        int $age,
        string $productSubId,
        string $platformProductId,
        string $mstStoreProductId,
        ?string $currencyCode,
        string $receiptBundleId,
        string $receiptPurchaseToken,
        int $paidAmount,
        int $freeAmount,
        string $purchasePrice,
        string $pricePerAmount,
        int $vipPoint,
        bool $isSandbox,
        string $billingPlatform
    ): string {
        $usrStoreProductHistory = new UsrStoreProductHistory();

        $usrStoreProductHistory->usr_user_id = $userId;
        $usrStoreProductHistory->receipt_unique_id = $receiptUniqueId;
        $usrStoreProductHistory->order_id = $orderId;
        $usrStoreProductHistory->invoice_id = $invoiceId;
        $usrStoreProductHistory->transaction_id = $transactionId;
        $usrStoreProductHistory->os_platform = $osPlatform;
        $usrStoreProductHistory->device_id = $deviceId;
        $usrStoreProductHistory->age = $age;
        $usrStoreProductHistory->product_sub_id = $productSubId;
        $usrStoreProductHistory->platform_product_id = $platformProductId;
        $usrStoreProductHistory->mst_store_product_id = $mstStoreProductId;
        $usrStoreProductHistory->currency_code = $currencyCode;
        $usrStoreProductHistory->receipt_bundle_id = $receiptBundleId;
        $usrStoreProductHistory->receipt_purchase_token = $receiptPurchaseToken;
        $usrStoreProductHistory->paid_amount = $paidAmount;
        $usrStoreProductHistory->free_amount = $freeAmount;
        $usrStoreProductHistory->purchase_price = $purchasePrice;
        $usrStoreProductHistory->price_per_amount = $pricePerAmount;
        $usrStoreProductHistory->vip_point = $vipPoint;
        $usrStoreProductHistory->is_sandbox = (int)$isSandbox;
        $usrStoreProductHistory->billing_platform = $billingPlatform;

        $usrStoreProductHistory->save();

        return $usrStoreProductHistory->id;
    }

    /**
     * order_idで重複チェック（べき等性）
     *
     * @param int $orderId
     * @return bool
     */
    public function existsByOrderId(int $orderId): bool
    {
        return UsrStoreProductHistory::query()
            ->where('order_id', $orderId)
            ->exists();
    }
}
