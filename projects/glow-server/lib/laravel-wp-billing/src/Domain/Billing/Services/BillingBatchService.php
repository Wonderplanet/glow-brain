<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services;

use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Billing\Repositories\LogStoreRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreInfoRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Billing\Traits\BillingCollectTrait;
use WonderPlanet\Domain\Billing\Traits\BillingPurchaseTrait;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Billing\Utils\StoreUtility;
use WonderPlanet\Domain\Currency\Delegators\CurrencyInternalAdminDelegator;
use WonderPlanet\Domain\Currency\Delegators\CurrencyInternalDelegator;
use WonderPlanet\Domain\Currency\Entities\CollectPaidCurrencyBatchTrigger;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;
use WonderPlanet\Domain\Currency\Models\MstStoreProduct;
use WonderPlanet\Domain\Currency\Models\OprProduct;
use WonderPlanet\Domain\Currency\Repositories\MstStoreProductRepository;
use WonderPlanet\Domain\Currency\Repositories\OprProductRepository;

class BillingBatchService
{
    use BillingPurchaseTrait;
    use BillingCollectTrait;
    use FakeStoreReceiptTrait;

    private const RECEIPT_PAYLOAD_GRANT_BATCH = 'GrantByBatch';
    private const RECEIPT_PAYLOAD_COLLECT_BATCH = 'CollectByBatch';

    public function __construct(
        private LogStoreRepository $logStoreRepository,
        private MstStoreProductRepository $mstStoreProductRepository,
        private OprProductRepository $oprProductRepository,
        private CurrencyInternalDelegator $currencyInternalDelegator,
        private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository,
        private UsrStoreInfoRepository $usrStoreInfoRepository,
        private CurrencyInternalAdminDelegator $currencyInternalAdminDelegator,
    ) {
    }

    /**
     * 購入処理(バッチ用)
     * 管理画面用の処理とは下記が異なる
     *  Triggerはここで生成している
     *  mstStoreProductIdとstoreProductIdはproductSubIdを元に取得している
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
     * @param string $loggingProductSubName
     * @param callable $callback
     * @param bool $isSandbox
     * @return void
     * @throws WpBillingException
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
        string $loggingProductSubName,
        callable $callback,
        bool $isSandbox
    ): void {
        // productSubIdから必要な商品idを取得
        $oprProduct = $this->getOprProductById($productSubId);
        $mstStoreProductId = $oprProduct->getMstStoreProductId();
        $mstStoreProduct = $this->getMstStoreProductById($mstStoreProductId);
        $storeProductId = $mstStoreProduct->getProductIdByBillingPlatform($billingPlatform);

        // configからbundleIdを取得(AppStore:bundleId、GoogleStore:packageName)
        $bundleId = StoreUtility::getBundleIdOrPackageName($isSandbox, $billingPlatform);

        $purchaseToken = 'dummy_purchase_token';

        // ダミーのレシート情報を取得
        $receiptStr = $this->makeReceiptBatch(self::RECEIPT_PAYLOAD_GRANT_BATCH);

        // 購入処理実行
        $this->executePurchase(
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
            $bundleId,
            $purchaseToken,
            $receiptStr,
            $trigger,
            $loggingProductSubName,
            $callback,
            $isSandbox
        );
    }

    /**
     * @param string $productSubId
     * @return OprProduct
     * @throws \Exception
     */
    private function getOprProductById(string $productSubId): OprProduct
    {
        $oprProduct = $this->oprProductRepository->findById($productSubId);
        if (is_null($oprProduct)) {
            throw new WpBillingException(
                "opr_products not found productSubId={$productSubId}",
                ErrorCode::OPR_PRODUCT_NOT_FOUND
            );
        }

        return $oprProduct;
    }

    /**
     * @param string $mstStoreProductId
     * @return MstStoreProduct
     * @throws \Exception
     */
    private function getMstStoreProductById(string $mstStoreProductId): MstStoreProduct
    {
        $mstStoreProduct = $this->mstStoreProductRepository->findById($mstStoreProductId);
        if (is_null($mstStoreProduct)) {
            throw new WpBillingException(
                "mst_store_products not found mstStoreProductId={$mstStoreProductId}",
                ErrorCode::MST_STORE_PRODUCT_NOT_FOUND
            );
        }

        return $mstStoreProduct;
    }


    /**
     * バッチで登録する用のレシート情報を作成する
     *
     * @return string
     */
    private function makeReceiptBatch(string $payload): string
    {
        // トランザクションIDは重複しなければ良いので、操作と紐付けやすくリクエストIDを使用
        $requestUniqueIdData = CurrencyCommon::getRequestUniqueIdData();
        $uniqueId = $requestUniqueIdData->getRequestIdType()->value . ':' . $requestUniqueIdData->getRequestId();

        return <<< EOM
        {
            "Payload":"{$payload}",
            "Store":"batch",
            "TransactionID":"{$uniqueId}"
        }
        EOM;
    }

    /**
     * 購入情報を返品した状態にする
     *
     * @param string $userId
     * @param string $usrStoreProductHistoryId
     * @param string $deviceId
     * @param string $receiptBundleId
     * @param string $receiptPurchaseToken
     * @param string $receiptUniqueId
     * @param string $triggerDetail
     * @return void
     * @throws WpBillingException
     */
    public function returnedPurchaseByBatch(
        string $userId,
        string $usrStoreProductHistoryId,
        string $deviceId,
        string $receiptBundleId,
        string $receiptPurchaseToken,
        string $receiptUniqueId,
        string $triggerDetail
    ): void {
        $receiptStr = $this->makeReceiptBatch(self::RECEIPT_PAYLOAD_COLLECT_BATCH);

        // Trigger生成
        //  残高集計ツールで固定したTriggerTypeが必須なのでここで指定している
        $trigger = new CollectPaidCurrencyBatchTrigger(
            $usrStoreProductHistoryId,
            $triggerDetail
        );

        // 回収処理実行
        $this->executeCollect(
            $userId,
            $usrStoreProductHistoryId,
            $deviceId,
            $receiptBundleId,
            $receiptPurchaseToken,
            $receiptUniqueId,
            $trigger,
            $receiptStr
        );
    }
}
