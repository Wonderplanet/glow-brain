<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Traits;

use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Currency\Entities\Trigger;

/**
 * 管理ツールの有償一次通貨回収処理とバッチ実行で使用する共通処理をまとめたTrait
 */
trait BillingCollectTrait
{
    /**
     * 回収処理を実行する
     * 管理ツール、バッチ処理で共通の処理となる
     * usrStoreInfoのpaid_price(課金額上限)は更新しない
     *
     * @param string  $usrStoreProductHistoryId
     * @param string  $deviceId
     * @param string  $receiptBundleId
     * @param string  $receiptPurchaseToken
     * @param string  $receiptUniqueId
     * @param Trigger $trigger
     * @param string  $receiptStr
     * @return void
     * @throws WpBillingException
     */
    private function executeCollect(
        string $userId,
        string $usrStoreProductHistoryId,
        string $deviceId,
        string $receiptBundleId,
        string $receiptPurchaseToken,
        string $receiptUniqueId,
        Trigger $trigger,
        string $receiptStr
    ): void {
        $usrStoreProductHistory = $this->usrStoreProductHistoryRepository
            ->findById($usrStoreProductHistoryId);
        if (is_null($usrStoreProductHistory)) {
            // ショップ購入履歴が取得できなかったらエラー
            throw new WpBillingException(
                "usr_store_product_history not found historyId={$usrStoreProductHistoryId}",
                ErrorCode::USR_STORE_PRODUCT_HISTORY_NOT_FOUND
            );
        }
        if ($usrStoreProductHistory->usr_user_id !== $userId) {
            // ショップ購入履歴のユーザーと入力したuserIdが一致しないエラー
            throw new WpBillingException(
                "unmatched userId history_user_id={$usrStoreProductHistory->usr_user_id}, "
                . "userId={$userId}",
                ErrorCode::UNMATCHED_USR_STORE_PRODUCT_HISTORY_USER_ID
            );
        }

        // usrStoreInfoを取得
        $usrStoreInfo = $this->usrStoreInfoRepository->findByUserId($userId);
        if (is_null($usrStoreInfo)) {
            throw new WpBillingException(
                "usr_store_info not found userId={$userId}",
                ErrorCode::SHOP_INFO_NOT_FOUND
            );
        }

        // 有償一次通貨の所持情報を更新
        //  購入分差し引く
        //  usrCurrencySummaryを更新
        //  回収したusrCurrencyPaidのデータを取得
        $usrCurrencyPaid = $this->currencyInternalAdminDelegator->collectCurrencyPaid(
            $userId,
            $usrStoreProductHistory->billing_platform,
            $usrStoreProductHistory->receipt_unique_id,
            $receiptUniqueId,
            (bool) $usrStoreProductHistory->is_sandbox,
            $trigger
        );

        // 無償一次通貨も付与されていた場合は減算する
        if ($usrStoreProductHistory->free_amount > 0) {
            $this->currencyInternalAdminDelegator
                ->collectFreeCurrencyByCollectPaid(
                    $userId,
                    $usrStoreProductHistory->os_platform,
                    $usrStoreProductHistory->free_amount,
                    $trigger
                );
        }

        // usr_store_product_historyにマイナス値のデータを記録
        $this->usrStoreProductHistoryRepository
            ->insertStoreProductHistory(
                $userId,
                $deviceId,
                $usrStoreProductHistory->age,
                $receiptUniqueId,
                $usrStoreProductHistory->os_platform,
                $usrStoreProductHistory->billing_platform,
                $usrStoreProductHistory->product_sub_id,
                $usrStoreProductHistory->platform_product_id,
                $usrStoreProductHistory->mst_store_product_id,
                $usrStoreProductHistory->currency_code,
                $receiptBundleId,
                $receiptPurchaseToken,
                -1 * $usrStoreProductHistory->paid_amount,
                -1 * $usrStoreProductHistory->free_amount,
                $usrStoreProductHistory->purchase_price,
                $usrStoreProductHistory->price_per_amount,
                -1 * $usrStoreProductHistory->vip_point,
                (bool) $usrStoreProductHistory->is_sandbox
            );

        // vipポイントを更新
        $this->refreshTotalVipPoint($userId);

        // log_storeにマイナス値を記録
        //  $rawPriceStringは本来ならストアから送られてくるが、今回は回収の為の登録となるのでpurchase_priceをそのまま登録している
        $rawPriceString = $usrStoreProductHistory->purchase_price;
        //  $loggingProductSubNameは$productSubIdと同じ値で設定
        $loggingProductSubName = $productSubId = $usrStoreProductHistory->product_sub_id;
        $this->logStoreRepository
            ->insertStoreLog(
                $userId,
                $deviceId,
                $usrStoreProductHistory->os_platform,
                $usrStoreProductHistory->billing_platform,
                $usrStoreProductHistory->age,
                $usrCurrencyPaid->seq_no,
                $usrStoreProductHistory->platform_product_id,
                $usrStoreProductHistory->mst_store_product_id,
                $productSubId,
                $loggingProductSubName,
                $receiptStr,
                $rawPriceString,
                $usrStoreProductHistory->currency_code,
                $receiptUniqueId,
                $receiptBundleId,
                -1 * $usrStoreProductHistory->paid_amount,
                -1 * $usrStoreProductHistory->free_amount,
                bcmul('-1', $usrStoreProductHistory->purchase_price),
                $usrStoreProductHistory->price_per_amount,
                -1 * $usrStoreProductHistory->vip_point,
                (bool) $usrStoreProductHistory->is_sandbox,
                $trigger
            );
    }
}
