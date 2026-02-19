<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Delegators;

use WonderPlanet\Domain\Billing\Entities\StoreReceipt;
use WonderPlanet\Domain\Billing\Entities\UsrStoreAllowanceEntity;
use WonderPlanet\Domain\Billing\Entities\UsrStoreInfoEntity;
use WonderPlanet\Domain\Billing\Services\BillingService;
use WonderPlanet\Domain\Currency\Entities\Trigger;

/**
 * 課金・通貨基盤で課金関連の処理を行うDelegator
 *
 * 課金・通貨基盤ライブラリ外からはこのDelegetorを使用してください。
 */
class BillingDelegator
{
    public function __construct(
        private BillingService $billingService,
    ) {
    }

    /**
     * ユーザーのショップ登録情報を取得する
     *
     * 年齢、月の累計支払金額、次回年齢確認日など
     *
     * 存在しない場合はnullを返す
     *
     * @param string $userId
     * @return UsrStoreInfoEntity|null
     */
    public function getStoreInfo(string $userId): ?UsrStoreInfoEntity
    {
        return $this->billingService->getStoreInfo($userId);
    }

    /**
     * 年齢情報を設定する
     *
     * ※このメソッドを使用して$renotifyAtを更新するタイミングで、累積課金額も0にリセットされる。
     *  実質的に、このメソッドが実行されたタイミングで月の累積課金額(paid_price)は0に設定される。
     *   内部的に集計期間の判定を行なっていないため注意すること。
     *
     * $renotifyAtについて
     * nullに設定すると、次回年齢確認日時は行われず、累計化金額の制限もされないものとして扱われる
     *
     * @param string $userId
     * @param integer $age
     * @param string|null $renotifyAt
     * @return UsrStoreInfoEntity
     */
    public function setStoreInfo(string $userId, int $age, ?string $renotifyAt): UsrStoreInfoEntity
    {
        return $this->billingService->setStoreInfo($userId, $age, $renotifyAt);
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
        $this->billingService->upsertStoreInfoAge($userId, $age, $renotifyAt);
    }

    /**
     * 購入許可情報を登録する
     *
     * 登録できない場合、登録に失敗する場合はWpBillingExceptionをthrowする
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform 購入ストアプラットフォーム
     * @param string $productId 購入対象のストアプロダクトID
     * @param string $productSubId
     * @param string $deviceId ユーザーの使用しているデバイス識別ID
     * @param string $triggerDetail
     * @return UsrStoreAllowanceEntity
     * @throws \Wonderplanet\Domain\Billing\Exceptions\WpBillingException
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
        return $this->billingService->allowedToPurchase(
            $userId,
            $osPlatform,
            $billingPlatform,
            $productId,
            $productSubId,
            $deviceId,
            $triggerDetail
        );
    }

    /**
     * 購入許可情報を返す
     *
     * データが取得できた場合、購入許可情報が存在していることを意味する。
     * 後の処理でIDを必要とするため、existsではなくデータを取得して判定する。
     *
     * 購入許可(allowance)はストアのプロダクトID(storeProductId)ごとに存在する。
     *
     * @param string $userId
     * @param string $billingPlatform
     * @param string $storeProductId
     * @return UsrStoreAllowanceEntity|null
     */
    public function getStoreAllowance(
        string $userId,
        string $billingPlatform,
        string $storeProductId
    ): ?UsrStoreAllowanceEntity {
        return $this->billingService->getStoreAllowance($userId, $billingPlatform, $storeProductId);
    }

    /**
     * 購入許可情報を返す
     * データが存在している場合、購入許可情報が存在することを意味する
     * データがなければ自動生成した購入許可情報を返す
     *
     * @param string $userId
     * @param string $billingPlatform
     * @param string $storeProductId
     * @param callable|null $onNullAllowanceCallback
     * @return UsrStoreAllowanceEntity|null
     */
    public function getOrCreateStoreAllowance(
        string $userId,
        string $billingPlatform,
        string $storeProductId,
        ?callable $onNullAllowanceCallback = null
    ): ?UsrStoreAllowanceEntity {
        return $this->billingService->getOrCreateStoreAllowance(
            $userId,
            $billingPlatform,
            $storeProductId,
            $onNullAllowanceCallback
        );
    }

    /**
     * ストアレシートの検証を行う
     *
     * レシートの内容に問題がなく、レシートトランザクションIDが購入済みでなければStoreReceiptオブジェクトを返す。
     * (StoreReceiptは以降の処理で使用する)
     *
     * 問題がある場合はWpBillingExceptionをthrowする
     *
     * $productIdについて
     *   AppStoreレシートのTransaction IDの取得に必要となる
     *
     * @param string $billingPlatform
     * @param string $productId
     * @param string $receipt
     * @return StoreReceipt
     * @throws \Wonderplanet\Domain\Billing\Exceptions\WpBillingException
     */
    public function verifyReceipt(string $billingPlatform, string $productId, string $receipt): StoreReceipt
    {
        return $this->billingService->verifyReceipt($billingPlatform, $productId, $receipt);
    }

    /**
     * 購入処理を実行する
     *
     * 購入許可チェックやレシートの検証は事前に完了していることを前提とする
     * この時点までに購入許可レコードの存在が確認できているため、対象の購入許可レコードIDを渡す
     *
     * paidAmountについて
     *   有償一次通貨の付与数(paidAmount)はpurchaed内で、マスタデータから取得する
     *
     * StoreReceiptについて
     *   verifyReceiptで渡されたものをそのまま使用します
     *
     * purchasePriceについて
     *   アプリから受け取った購入価格をそのまま使用します (例: 0.01)
     *   rawPriceStringは通貨記号付きの文字列なので、数値のみの文字列を別途渡す必要がある
     *
     * rawPriceStringについて
     *   アプリで取得された購入価格をそのまま使用します (例: USDの場合「$0.01」のようになっている)
     *
     * loggingProductSubNameについて
     *   log_stroesに記録するための商品名
     *   product_sub_idはAllowanceに設定されているものを記録するが、
     *   当時の商品名を補助情報として記録しておくために使用する
     *
     *   各言語情報の商品名は内部的に扱っていないので、ここに渡す必要がある
     *
     * vipPointについて
     *   課金額の代わりとして集計に使うポイントとなる。
     *   フレームワークライブラリではポイントの記録のみ行う。
     *   具体的な値の指定や、ポイントによるセグメント分けなどはプロダクト側で判断する
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param string $deviceId ユーザーの使用しているデバイス識別ID
     * @param UsrStoreAllowanceEntity $usrStoreAllowance 対応する購入許可レコード
     * @param string $purchasePrice アプリから送信されてくる購入価格(数値のみ)
     * @param string $rawPriceString アプリから送信されてくる購入価格(通貨記号付き)
     * @param integer $vipPoint 付与するVIPポイント
     * @param string $currencyCode 通貨コード(JPY, USDなど)
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
        return $this->billingService->purchased(
            $userId,
            $osPlatform,
            $billingPlatform,
            $deviceId,
            $usrStoreAllowance,
            $purchasePrice,
            $rawPriceString,
            $vipPoint,
            $currencyCode,
            $receipt,
            $trigger,
            $loggingProductSubName,
            $callback,
        );
    }

    /**
     * ユーザーのショップ購入履歴があればtrueを返す
     *
     * @param string $userId
     * @return boolean
     */
    public function hasStoreProductHistory(string $userId): bool
    {
        return $this->billingService->hasStoreProductHistory($userId);
    }

    /**
     * 購入トランザクション終了例外処理
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
        string $loggingProductSubName
    ): void {
        $this->billingService->forceClosePurchase(
            $userId,
            $osPlatform,
            $billingPlatform,
            $deviceId,
            $usrStoreAllowance,
            $purchasePrice,
            $rawPriceString,
            $currencyCode,
            $receipt,
            $trigger,
            $loggingProductSubName
        );
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
        $this->billingService->purchasedForWebStore(
            $userId,
            $oprProductId,
            $paidAmount,
            $currencyCode,
            $orderId,
            $invoiceId,
            $transactionId,
            $mstStoreProductId,
            $platformProductId,
            $purchasePrice,
            $vipPoint,
            $isSandbox,
            $loggingProductSubName,
            $trigger
        );
    }

    /**
     * order_idで購入履歴が存在するかチェック（べき等性確認用）
     *
     * @param int $orderId Xsollaの注文ID
     * @return bool true: 存在する（既に処理済み）, false: 存在しない（新規リクエスト）
     */
    public function existsByOrderId(int $orderId): bool
    {
        return $this->billingService->existsByOrderId($orderId);
    }
}
