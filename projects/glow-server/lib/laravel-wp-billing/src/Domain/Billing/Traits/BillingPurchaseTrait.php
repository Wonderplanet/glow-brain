<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Traits;

use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Models\MstStoreProduct;
use WonderPlanet\Domain\Currency\Models\OprProduct;

/**
 * apiの商品購入と管理ツールの有償一次通貨付与処理で使用する共通処理をまとめたTrait
 */
trait BillingPurchaseTrait
{
    /**
     * @var array<string> 有償一次通貨を加算する通貨コードのリスト
     */
    private const ADD_PAID_CURRENCY_CODES = ['JPY'];

    /**
     * 購入処理を実行する
     * 購入api、有償通貨付与管理ツールで共通の処理となる
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
     * @param string $bundleId
     * @param string $purchaseToken
     * @param string $receiptStr
     * @param Trigger $trigger
     * @param string $loggingProductSubName
     * @param callable $callback
     * @param bool $isSandbox
     * @return void
     * @throws WpBillingException
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
     */
    private function executePurchase(
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
        string $bundleId,
        string $purchaseToken,
        string $receiptStr,
        Trigger $trigger,
        string $loggingProductSubName,
        callable $callback,
        bool $isSandbox
    ): void {
        // 対応するマスタの検証と付与数をマスタから取得
        $oprProduct = $this->oprProductRepository->findById($productSubId);
        $mstStoreProduct = $this->mstStoreProductRepository->findById($mstStoreProductId);
        $this->verifyPurchaseStoreProduct($billingPlatform, $storeProductId, $mstStoreProduct, $oprProduct);
        $paidAmount = $oprProduct->paid_amount;
        // 購入済とログ発行
        $this->savePurchaseAndInsertLog(
            $userId,
            $osPlatform,
            $billingPlatform,
            $paidAmount,
            $deviceId,
            $storeProductId,
            $mstStoreProductId,
            $productSubId,
            $purchasePrice,
            $rawPriceString,
            $vipPoint,
            $currencyCode,
            $receiptUniqueId,
            $bundleId,
            $purchaseToken,
            $receiptStr,
            $trigger,
            $loggingProductSubName,
            $callback,
            $isSandbox
        );

        // VIPポイントの合計を更新
        $this->refreshTotalVipPoint($userId);
    }

    /**
     * 指定されたパラメータが適切なマスタデータを取得できることを確認する
     *
     * - oprProductとmstStoreProductのmst_store_product_idが一致すること
     * - mstStoreProductのプラットフォーム向けproduct_idと$productIdが一致すること
     *
     * 問題があれば例外を投げる
     *
     * $mstStoreProductと$oprProductはnullの場合エラーになる。
     * 引数でnull許容しているのは、findByIdの結果をそのまま渡す想定のため
     *
     * @param string $billingPlatform
     * @param string $productId
     * @param MstStoreProduct|null $mstStoreProduct
     * @param OprProduct|null $oprProduct
     * @return void
     * @throws WpBillingException
     */
    private function verifyPurchaseStoreProduct(
        string $billingPlatform,
        string $productId,
        ?MstStoreProduct $mstStoreProduct,
        ?OprProduct $oprProduct
    ): void {
        // 渡されたマスタオブジェクトがnullであればエラー
        if (is_null($oprProduct)) {
            throw new WpBillingException("opr_product not found", ErrorCode::OPR_PRODUCT_NOT_FOUND);
        }
        if (is_null($mstStoreProduct)) {
            throw new WpBillingException("mst_store_product not found", ErrorCode::MST_STORE_PRODUCT_NOT_FOUND);
        }

        // mst_store_product_idが一致すること
        if ($mstStoreProduct->id !== $oprProduct->mst_store_product_id) {
            throw new WpBillingException(
                "mst_store_product_id not match, mst:{$mstStoreProduct->id} "
                . "opr:{$oprProduct->mst_store_product_id}",
                ErrorCode::ALLOWANCE_AND_OPR_PRODUCT_NOT_MATCH
            );
        }
        // mst_store_productのプラットフォーム向けproduct_idと$productIdが一致すること
        $mstProductId = $mstStoreProduct->getProductIdByBillingPlatform($billingPlatform);
        if ($mstProductId !== $productId) {
            throw new WpBillingException(
                "mst_store_product_id not match, mst:{$mstProductId} billing:{$productId}",
                ErrorCode::ALLOWANCE_AND_MST_STORE_PRODUCT_NOT_MATCH
            );
        }
    }

    /**
     * ショップ情報の累計課金額を加算する
     *
     * - レコードが作成されていない場合はエラーとする (購入前に年齢確認が行われてる必要があるため)
     * - currnecyCodeがJPYの場合のみ加算する
     *   日本向けの措置なので、日本円のみ対象とする
     * - renotify_atがnullの場合は加算しない
     *
     * 価格は共通で固定小数点数の文字列で扱っているため、ここでもstringとする
     *
     * @param string $userId
     * @param string $currencyCode
     * @param string $purchasePrice
     * @return void
     */
    private function addStoreInfoPaidPrice(string $userId, string $currencyCode, string $purchasePrice): void
    {
        // 対象がADD_PAID_CURRENCY_CODESに存在しない場合は加算しない
        if (!in_array($currencyCode, self::ADD_PAID_CURRENCY_CODES)) {
            return;
        }

        // ショップ情報の取得
        $usrStoreInfo = $this->usrStoreInfoRepository->findByUserId($userId);
        if (is_null($usrStoreInfo)) {
            throw new WpBillingException("usr_store_info not found", ErrorCode::SHOP_INFO_NOT_FOUND);
        }

        // 年齢確認日が設定されていない場合は加算しない
        if (is_null($usrStoreInfo->renotify_at)) {
            return;
        }
        $this->usrStoreInfoRepository->incrementPaidPrice($userId, $purchasePrice);
    }

    /**
     * VIPポイントの合計を更新する
     *
     * @param string $userId
     * @return void
     */
    private function refreshTotalVipPoint(string $userId): void
    {
        // VIPポイントを集計
        //   購入した履歴から集計しているのみのため、返金・回収などの考慮はされていない
        $vipPoint = $this->usrStoreProductHistoryRepository->sumVipPoint($userId);

        // summaryに反映
        $this->usrStoreInfoRepository->updateTotalVipPoint($userId, $vipPoint);
    }

    /**
     * 購入済みとそのログを発行する処理
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
     * @param string $bundleId
     * @param string $purchaseToken
     * @param string $receiptStr
     * @param \WonderPlanet\Domain\Currency\Entities\Trigger $trigger
     * @param string $loggingProductSubName
     * @param callable $callback
     * @param bool $isSandbox
     * @return array<mixed> [$usrStoreProductHistoryId, $logStoreId]
     */
    private function savePurchaseAndInsertLog(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $paidAmount,
        string $deviceId,
        string $storeProductId,
        string $mstStoreProductId,
        string $productSubId,
        string $purchasePrice,
        string $rawPriceString,
        int $vipPoint,
        string $currencyCode,
        string $receiptUniqueId,
        string $bundleId,
        string $purchaseToken,
        string $receiptStr,
        Trigger $trigger,
        string $loggingProductSubName,
        callable $callback,
        bool $isSandbox,
    ): array {
        // 有償一次通貨の付与とログの挿入、summaryの更新を行う
        $usrCurrencyPaid = $this->currencyInternalDelegator->addCurrencyPaid(
            $userId,
            $osPlatform,
            $billingPlatform,
            $paidAmount,
            $currencyCode,
            $purchasePrice,
            $vipPoint,
            $receiptUniqueId,
            $isSandbox,
            $trigger,
        );

        // paidPricePerAmountは登録時に計算しているので、登録後のオブジェクトから取得する
        $paidPricePerAmount = $usrCurrencyPaid->price_per_amount;

        // seqNoは付与後の値を取得する
        $seqNo = $usrCurrencyPaid->seq_no;

        // store_infoの更新
        $this->addStoreInfoPaidPrice($userId, $currencyCode, $purchasePrice);

        // 付与成功後のコールバック実行
        $callback();

        // store_product_historyの登録
        // free_amountは課金基盤側の制御としてセットで配布する場合を記録する。
        // ただし、現在は課金基盤側で無償一次通貨の配布機能はないため、常に0となる。
        // (callbackなどプロダクト側で配布される無償一次通貨についてはカウント対象外のパラメータとなっている)
        $freeAmount = 0;
        // 登録時の年齢設定を取得
        $usrStoreInfo = $this->usrStoreInfoRepository->findByUserId($userId);
        $age = $usrStoreInfo->age;
        $usrStoreProductHistoryId = $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            $userId,
            $deviceId,
            $age,
            $receiptUniqueId,
            $osPlatform,
            $billingPlatform,
            $productSubId,
            $storeProductId,
            $mstStoreProductId,
            $currencyCode,
            $bundleId,
            $purchaseToken,
            $paidAmount,
            $freeAmount,
            $purchasePrice,
            $paidPricePerAmount,
            $vipPoint,
            $isSandbox
        );

        // ショップログの追加
        $logStoreId = $this->logStoreRepository->insertStoreLog(
            $userId,
            $deviceId,
            $osPlatform,
            $billingPlatform,
            $age,
            $seqNo,
            $storeProductId,
            $mstStoreProductId,
            $productSubId,
            $loggingProductSubName,
            $receiptStr,
            $rawPriceString,
            $currencyCode,
            $receiptUniqueId,
            $bundleId,
            $paidAmount,
            $freeAmount,
            $purchasePrice,
            $paidPricePerAmount,
            $vipPoint,
            $isSandbox,
            $trigger,
        );

        return [$usrStoreProductHistoryId, $logStoreId];
    }
}
