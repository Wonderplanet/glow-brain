<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services;

use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Entities\LogAllowanceAutoInsertEntity;
use WonderPlanet\Domain\Billing\Entities\NullAllowanceCallbackEntity;
use WonderPlanet\Domain\Billing\Entities\StoreReceipt;
use WonderPlanet\Domain\Billing\Entities\UsrStoreAllowanceEntity;
use WonderPlanet\Domain\Billing\Entities\UsrStoreInfoEntity;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingEndTransactionException;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Billing\Repositories\LogAllowanceRepository;
use WonderPlanet\Domain\Billing\Repositories\LogCloseStoreTransactionRepository;
use WonderPlanet\Domain\Billing\Repositories\LogStoreRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreAllowanceRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreInfoRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Billing\Traits\BillingPurchaseTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyInternalDelegator;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Repositories\MstStoreProductRepository;
use WonderPlanet\Domain\Currency\Repositories\OprProductRepository;

/**
 * ユーザーの商品購入関連のService
 */
class BillingService
{
    use BillingPurchaseTrait;

    public function __construct(
        private CurrencyInternalDelegator $currencyInternalDelegator,
        private UsrStoreAllowanceRepository $usrStoreAllowanceRepository,
        private OprProductRepository $oprProductRepository,
        private UsrStoreInfoRepository $usrStoreInfoRepository,
        private BillingStoreService $billingStoreService,
        private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository,
        private MstStoreProductRepository $mstStoreProductRepository,
        private LogAllowanceRepository $logAllowanceRepository,
        private LogStoreRepository $logStoreRepository,
        private LogCloseStoreTransactionRepository $logCloseStoreTransactionRepository,
    ) {
    }

    /**
     * ユーザーのショップ登録情報を取得する
     *
     * 年齢、月の累計支払金額、次回年齢確認日など
     *
     * @param string $userId
     * @return UsrStoreInfoEntity|null
     */
    public function getStoreInfo(string $userId): ?UsrStoreInfoEntity
    {
        $model = $this->usrStoreInfoRepository->findByUserId($userId);
        return $model ? $model->getModelEntity() : null;
    }

    /**
     * ユーザーのショップ登録情報を更新する
     *
     * @param string $userId
     * @param integer $age
     * @param string|null $renotifyAt
     * @return UsrStoreInfoEntity
     */
    public function setStoreInfo(string $userId, int $age, ?string $renotifyAt): UsrStoreInfoEntity
    {
        // このメソッドが呼ばれた時点でpaidPriceは0になる。
        // 次回確認日を設定する際にこれまでの累積課金額をリセットするため。
        $paidPrice = 0;

        // ショップ情報を登録または更新する
        $this->usrStoreInfoRepository->upsertStoreInfo($userId, $age, $paidPrice, $renotifyAt);

        // 登録したショップ情報を取得する
        return $this->getStoreInfo($userId);
    }

    /**
     * 年齢と再通知日時を更新する
     *
     * @param string $userId
     * @param int $age
     * @param ?string $renotifyAt
     * @return void
     */
    public function upsertStoreInfoAge(string $userId, int $age, ?string $renotifyAt): void
    {
        $this->usrStoreInfoRepository->upsertStoreInfoAge($userId, $age, $renotifyAt);
    }

    /**
     * 購入許可情報を登録する
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform 購入ストアプラットフォーム
     * @param string $productId 購入するストアのプロダクトID
     * @param string $productSubId
     * @param string $deviceId ユーザーの使用しているデバイス識別ID
     * @param string $triggerDetail
     * @return UsrStoreAllowanceEntity
     * @throws WpBillingException
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
     */
    public function allowedToPurchase(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        string $productId,
        string $productSubId,
        string $deviceId,
        string $triggerDetail = ""
    ): UsrStoreAllowanceEntity {
        // 許可レコードが存在しているか確認する
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findByUserIdAndProductId(
            $userId,
            $billingPlatform,
            $productId
        );
        if ($usrStoreAllowance) {
            // すでに存在している場合は削除する
            // allowance削除ログの追加
            $this->logAllowanceRepository->insertAllowanceLog(
                $usrStoreAllowance->usr_user_id,
                $usrStoreAllowance->os_platform,
                $usrStoreAllowance->billing_platform,
                $usrStoreAllowance->product_id,
                $usrStoreAllowance->mst_store_product_id,
                $usrStoreAllowance->product_sub_id,
                $usrStoreAllowance->device_id,
                new Trigger(
                    Trigger::TRIGGER_TYPE_ALLOWANCE_DELETE,
                    $usrStoreAllowance->id,
                    '',
                    "auto delete. insert new allowance product_sub_id: {$productSubId}"
                )
            );

            $this->usrStoreAllowanceRepository->deleteStoreAllowance($userId, $usrStoreAllowance->id);
        }

        // 対象のopr_productのmst_store_product_idを取得
        //  データが存在しているかはverifyPurchaseStoreProductで検証するため、ここではfindByIdの結果がnullでも問題ない
        $oprProduct = $this->oprProductRepository->findById($productSubId);
        $mstStoreProduct = $this->mstStoreProductRepository->findById($oprProduct->mst_store_product_id ?? '');
        $this->verifyPurchaseStoreProduct($billingPlatform, $productId, $mstStoreProduct, $oprProduct);
        $mstStoreProductId = $mstStoreProduct->id;

        // 一次通貨の付与が可能か確認する
        // 無償通貨の付与は想定していないため、0とする
        $freeAmount = 0;
        $this->currencyInternalDelegator->validateAddCurrency(
            $userId,
            $oprProduct->paid_amount,
            $freeAmount
        );

        // allowanceとallowanceログ登録
        $this->insertAllowanceAndLog(
            $userId,
            $osPlatform,
            $billingPlatform,
            $productId,
            $mstStoreProductId,
            $productSubId,
            $deviceId,
            $triggerDetail
        );

        // 登録したallowanceを取得する
        //   レスポンスとして返すものになるので、DBから取り直している
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findByUserIdAndProductId(
            $userId,
            $billingPlatform,
            $productId
        );
        return $usrStoreAllowance->getModelEntity();
    }

    /**
     * allowanceとログの登録処理
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
        string $triggerDetail = ''
    ): void {
        // allowanceを登録する
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->insertStoreAllowance(
            $userId,
            $osPlatform,
            $billingPlatform,
            $productId,
            $mstStoreProductId,
            $productSubId,
            $deviceId,
        );

        // allowance登録ログの追加
        //   今回の処理で登録したallowanceのログをとっているので、insertStoreAllowanceの結果を元にログを追加する
        $trigger = new Trigger(Trigger::TRIGGER_TYPE_ALLOWANCE_INSERT, $usrStoreAllowance->id, '', $triggerDetail);
        $this->logAllowanceRepository->insertAllowanceLog(
            $usrStoreAllowance->usr_user_id,
            $usrStoreAllowance->os_platform,
            $usrStoreAllowance->billing_platform,
            $usrStoreAllowance->product_id,
            $usrStoreAllowance->mst_store_product_id,
            $usrStoreAllowance->product_sub_id,
            $usrStoreAllowance->device_id,
            $trigger
        );
    }

    /**
     * 購入許可情報を取得する
     *
     * データが存在している場合、購入許可情報が存在することを意味する
     *
     * @param string $userId
     * @param string $billingPlatform
     * @param string $storeProductId  ストアのプロダクトID
     * @return UsrStoreAllowanceEntity|null
     */
    public function getStoreAllowance(
        string $userId,
        string $billingPlatform,
        string $storeProductId
    ): ?UsrStoreAllowanceEntity {
        $model = $this->usrStoreAllowanceRepository
            ->findByUserIdAndProductId($userId, $billingPlatform, $storeProductId);
        return $model ? $model->getModelEntity() : null;
    }

    /**
     * 購入許可情報を取得する
     * データが存在している場合、購入許可情報が存在することを意味する
     * データがなければ自動生成した購入許可情報を返す
     *
     * @param string $userId
     * @param string $billingPlatform
     * @param string $storeProductId  ストアのプロダクトID
     * @param callable|null $onNullAllowanceCallback
     * @return UsrStoreAllowanceEntity|null
     */
    public function getOrCreateStoreAllowance(
        string $userId,
        string $billingPlatform,
        string $storeProductId,
        ?callable $onNullAllowanceCallback = null
    ): ?UsrStoreAllowanceEntity {
        $model = $this->getStoreAllowance($userId, $billingPlatform, $storeProductId);
        if ($model !== null) {
            return $model;
        }

        if ($onNullAllowanceCallback !== null) {
            // 本来getメソッドで処理するべきではないが、購入許可情報があるはずなのに何らかの問題でnullになる場合の対応
            $nullAllowanceCallbackEntity = $onNullAllowanceCallback();
            // バリデーションチェック
            $errorMessage = "";
            if ($nullAllowanceCallbackEntity === null) {
                $errorMessage = "NullAllowanceCallbackEntity is null";
            } elseif (!$nullAllowanceCallbackEntity instanceof NullAllowanceCallbackEntity) {
                $errorMessage = "return value is not NullAllowanceCallbackEntity";
            }
            if (!blank($errorMessage)) {
                throw new WpBillingException($errorMessage, ErrorCode::INVALID_ALLOWANCE);
            }
            // Json形式のログ情報生成
            $logAllowanceAutoInsertEntity = new LogAllowanceAutoInsertEntity(
                'auto_insert',
                $nullAllowanceCallbackEntity->productId,
                $nullAllowanceCallbackEntity->detail,
            );
            // 購入許可情報自動生成
            return $this->allowedToPurchase(
                $userId,
                $nullAllowanceCallbackEntity->osPlatform,
                $billingPlatform,
                $nullAllowanceCallbackEntity->productId,
                $nullAllowanceCallbackEntity->productSubId,
                $nullAllowanceCallbackEntity->deviceId,
                $logAllowanceAutoInsertEntity->toDetail()
            );
        }

        return null;
    }

    /**
     * レシートのバリデーションを行う
     *
     * レシートチェック後に、レシート情報を含むStoreReceiptオブジェクトを返す
     * このオブジェクトをpurchacedメソッドに渡すことで、購入処理を行う
     *
     * @param string $billingPlatform
     * @param string $productId
     * @param string $receipt
     * @return StoreReceipt
     */
    public function verifyReceipt(string $billingPlatform, string $productId, string $receipt): StoreReceipt
    {
        return $this->billingStoreService->verifyReceipt($billingPlatform, $productId, $receipt);
    }

    /**
     * 商品購入と有償一次通貨の付与を行う
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param string $deviceId ユーザーの使用しているデバイス識別ID
     * @param UsrStoreAllowanceEntity $usrStoreAllowance  対応する購入許可レコード
     * @param string $purchasePrice
     * @param string $rawPriceString
     * @param integer $vipPoint
     * @param string $currencyCode
     * @param StoreReceipt $receipt
     * @param Trigger $trigger
     * @param string $loggingProductSubName ログに記録する商品名
     * @param callable $callback
     * @return boolean
     * @throws \WonderPlanet\Domain\Billing\Exceptions\WpBillingException
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
     */
    public function purchased(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        string $deviceId,
        UsrStoreAllowanceEntity $usrStoreAllowance,
        string $purchasePrice,
        string $rawPriceString,
        int $vipPoint,
        string $currencyCode,
        StoreReceipt $receipt,
        Trigger $trigger,
        string $loggingProductSubName,
        callable $callback
    ): bool {
        // 対応する許可レコードのID
        $usrStoreAllowanceId = $usrStoreAllowance->id;
        // 空の場合はエラー
        if (blank($usrStoreAllowanceId)) {
            throw new WpBillingException("usr_store_allowance_id is empty", ErrorCode::INVALID_ALLOWANCE);
        }
        $storeProductId = $usrStoreAllowance->product_id;
        $productSubId = $usrStoreAllowance->product_sub_id;
        $mstStoreProductId = $usrStoreAllowance->mst_store_product_id;

        // レシートからreceiptUniqueIdとbundleIdを取得
        $receiptUniqueId = $receipt->getUnitqueId();
        $bundleId = $receipt->getBundleId();
        $purchaseToken = $receipt->getPurchaseToken();

        // サンドボックスレシートかどうかは、レシートオブジェクトから取得する
        //   サンドボックスレシートの場合は売上から除外するなどの対応が必要となるため
        //   また本番環境でも調査などでサンドボックスレシートが使用される可能性があり、
        //   本番/開発環境とは別に判定する必要がある
        $isSandbox = $receipt->isSandboxReceipt();

        // レシート情報を文字列で取得
        $receiptStr = $receipt->getReceipt();

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

        // 対応する購入許可情報の削除
        $this->usrStoreAllowanceRepository->deleteStoreAllowance($userId, $usrStoreAllowance->id);

        // allowance削除ログの追加
        $this->logAllowanceRepository->insertAllowanceLog(
            $usrStoreAllowance->usr_user_id,
            $usrStoreAllowance->os_platform,
            $usrStoreAllowance->billing_platform,
            $usrStoreAllowance->product_id,
            $usrStoreAllowance->mst_store_product_id,
            $usrStoreAllowance->product_sub_id,
            $usrStoreAllowance->device_id,
            new Trigger(Trigger::TRIGGER_TYPE_ALLOWANCE_DELETE, $usrStoreAllowance->id, '', '')
        );

        // 購入の承認(GooglePlay向けの処理)
        $this->billingStoreService->purchaseAcknowledge($receipt);

        return true;
    }

    /**
     * ユーザーのショップ購入履歴があればtrueを返す
     *
     * @param string $userId
     * @return boolean
     */
    public function hasStoreProductHistory(string $userId): bool
    {
        return $this->usrStoreProductHistoryRepository->hasStoreProductHistory($userId);
    }

    /**
     * 購入トランザクションを終了させて購入処理を終了させる
     * ref: https://wonderplanet.atlassian.net/wiki/spaces/SEED/pages/560595317#%E8%AA%B2%E9%87%91%E5%9F%BA%E7%9B%A4
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param string $deviceId
     * @param UsrStoreAllowanceEntity $usrStoreAllowance
     * @param string $purchasePrice
     * @param string $rawPriceString
     * @param string $currencyCode
     * @param StoreReceipt $receipt
     * @param Trigger $trigger
     * @param string $loggingProductSubName
     * @return void
     * @throws \WonderPlanet\Domain\Billing\Exceptions\WpBillingEndTransactionException
     */
    public function forceClosePurchase(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        string $deviceId,
        UsrStoreAllowanceEntity $usrStoreAllowance,
        string $purchasePrice,
        string $rawPriceString,
        string $currencyCode,
        StoreReceipt $receipt,
        Trigger $trigger,
        string $loggingProductSubName,
    ) {
        // ストア購入情報確認
        $usrStoreAllowanceId = $usrStoreAllowance->id;
        if (blank($usrStoreAllowanceId)) {
            throw new WpBillingException("usr_store_allowance_id is empty", ErrorCode::INVALID_ALLOWANCE);
        }
        // 購入済情報を記録してそのログを発行する
        // 有償通貨は付与しないため0として記録する
        [$usrStoreProductHistoryId, $logStoreId] = $this->savePurchaseAndInsertLog(
            $userId,
            $osPlatform,
            $billingPlatform,
            0,
            $deviceId,
            $usrStoreAllowance->product_id,
            $usrStoreAllowance->mst_store_product_id,
            $usrStoreAllowance->product_sub_id,
            $purchasePrice,
            $rawPriceString,
            0,
            $currencyCode,
            $receipt->getUnitqueId(),
            $receipt->getBundleId(),
            $receipt->getPurchaseToken(),
            $receipt->getReceipt(),
            $trigger,
            $loggingProductSubName,
            function () {
            },
            $receipt->isSandboxReceipt(),
        );

        // allowanceが残っていたら削除してそのログを発行する
        $this->usrStoreAllowanceRepository->deleteStoreAllowance(
            $userId,
            $usrStoreAllowance->id
        );
        $this->logAllowanceRepository->insertAllowanceLog(
            $usrStoreAllowance->usr_user_id,
            $usrStoreAllowance->os_platform,
            $usrStoreAllowance->billing_platform,
            $usrStoreAllowance->product_id,
            $usrStoreAllowance->mst_store_product_id,
            $usrStoreAllowance->product_sub_id,
            $usrStoreAllowance->device_id,
            new Trigger(Trigger::TRIGGER_TYPE_ALLOWANCE_DELETE, $usrStoreAllowanceId, '', '')
        );

        // ストア側のステータスを変更する(GooglePlayのみ)
        $this->billingStoreService->purchaseAcknowledge($receipt);

        // 購入トランザクションを終了させる
        // ログ発行
        $usrStoreProductHistory = $this->usrStoreProductHistoryRepository->findById($usrStoreProductHistoryId);
        $this->logCloseStoreTransactionRepository->insertCloseStoreTransactionLog(
            $userId,
            $osPlatform,
            $billingPlatform,
            $usrStoreAllowance->product_id,
            $usrStoreAllowance->mst_store_product_id,
            $usrStoreAllowance->product_sub_id,
            $loggingProductSubName,
            $rawPriceString,
            $rawPriceString,
            $currencyCode,
            $usrStoreProductHistory->receipt_unique_id,
            $usrStoreProductHistory->receipt_bundle_id,
            $deviceId,
            $usrStoreProductHistoryId,
            $logStoreId,
            $purchasePrice,
            $receipt->isSandboxReceipt(),
            new Trigger(Trigger::TRIGGER_TYPE_ALLOWANCE_INSERT, $usrStoreAllowanceId, '', '')
        );
        // エラーコード発行
        throw new WpBillingEndTransactionException();
    }

    /**
     * WebStore購入処理を実行する
     *
     * WebStore（Xsolla）からの購入通知を処理し、有償通貨を付与します。
     * レシート検証は不要（Xsolla側で実施済み）のため、直接通貨を付与します。
     *
     * @param string $userId ユーザーID
     * @param string $oprProductId OPR商品ID
     * @param int $paidAmount 有償通貨付与数
     * @param string $currencyCode 通貨コード（例: "JPY", "USD"）
     * @param int $orderId Xsollaの注文ID（べき等性キー）
     * @param string|null $invoiceId Xsollaの請求書ID（無料アイテムの場合はNULL）
     * @param string $transactionId W2で発行したトランザクションID
     * @param string $mstStoreProductId マスター商品ID
     * @param string $platformProductId プラットフォーム商品ID（WebStore SKU）
     * @param string $purchasePrice 購入価格
     * @param int $vipPoint VIPポイント
     * @param bool $isSandbox サンドボックスフラグ
     * @param string $loggingProductSubName ログに記録する商品名
     * @param Trigger $trigger トリガー情報
     * @return void
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
     */
    public function purchasedForWebStore(
        string $userId,
        string $oprProductId,
        int $paidAmount,
        string $currencyCode,
        string $receiptUniqueId,
        int $orderId,
        ?string $invoiceId,
        string $transactionId,
        string $mstStoreProductId,
        string $platformProductId,
        string $purchasePrice,
        int $vipPoint,
        bool $isSandbox,
        string $loggingProductSubName,
        Trigger $trigger
    ): void {
        // WebStoreではデバイスが特定できないため空文字列を使用
        $deviceId = '';

        // WebStore共通の定数を変数化
        $osPlatform = CurrencyConstants::OS_PLATFORM_WEBSTORE;
        $billingPlatform = CurrencyConstants::PLATFORM_WEBSTORE;

        // OPR商品情報を取得
        $oprProduct = $this->oprProductRepository->findById($oprProductId);
        if (is_null($oprProduct)) {
            throw new WpBillingException(
                "OPR product not found: {$oprProductId}",
                ErrorCode::OPR_PRODUCT_NOT_FOUND
            );
        }

        // 有償通貨を付与（billing_platform='WebStore'）
        $purchasePriceStr = (string) $purchasePrice;
        $usrCurrencyPaid = $this->currencyInternalDelegator->addPaidCurrencyForWebStore(
            $userId,
            $osPlatform,
            $billingPlatform,
            $paidAmount,
            $currencyCode,
            $purchasePriceStr,
            $receiptUniqueId,
            $isSandbox,
            $trigger
        );

        // paidPricePerAmountは登録時に計算されているので、登録後のオブジェクトから取得
        $paidPricePerAmount = $usrCurrencyPaid->price_per_amount;

        // seqNoは付与後の値を取得する
        $seqNo = $usrCurrencyPaid->seq_no;

        // store_infoの更新（累計課金額の加算）
        $this->addStoreInfoPaidPrice($userId, $currencyCode, $purchasePriceStr);

        // 登録時の年齢設定を取得
        $usrStoreInfo = $this->usrStoreInfoRepository->findByUserId($userId);
        $age = $usrStoreInfo?->age ?? 0;

        // free_amountは課金基盤側で無償一次通貨の配布機能はないため、常に0
        $freeAmount = 0;

        // store_product_historyの登録
        $this->usrStoreProductHistoryRepository->insertStoreProductHistoryForWebStore(
            $userId,
            $receiptUniqueId,
            $orderId,
            $invoiceId,
            $transactionId,
            $osPlatform,
            $deviceId,
            $age,
            $oprProductId,
            $platformProductId,
            $mstStoreProductId,
            $currencyCode,
            '',
            '',
            $paidAmount,
            $freeAmount,
            $purchasePrice,
            $paidPricePerAmount,
            $vipPoint,
            $isSandbox,
            $billingPlatform
        );

        // WebStore用のrawReceipt情報をJSON形式で作成
        $rawReceipt = json_encode([
            'order_id' => $orderId,
            'invoice_id' => $invoiceId,
            'transaction_id' => $transactionId,
        ]);

        // ショップログの追加
        $this->logStoreRepository->insertStoreLog(
            $userId,
            $deviceId,
            $osPlatform,
            $billingPlatform,
            $age,
            $seqNo,
            $platformProductId,
            $mstStoreProductId,
            $oprProductId,
            $loggingProductSubName,
            $rawReceipt,
            $purchasePriceStr,
            $currencyCode,
            (string)$orderId,
            '',
            $paidAmount,
            $freeAmount,
            $purchasePrice,
            $paidPricePerAmount,
            $vipPoint,
            $isSandbox,
            $trigger
        );

        // VIPポイントの合計を更新
        $this->refreshTotalVipPoint($userId);
    }

    /**
     * order_idで購入履歴が存在するかチェック（べき等性確認用）
     *
     * @param int $orderId Xsollaの注文ID
     * @return bool true: 存在する（既に処理済み）, false: 存在しない（新規リクエスト）
     */
    public function existsByOrderId(int $orderId): bool
    {
        return $this->usrStoreProductHistoryRepository->existsByOrderId($orderId);
    }
}
