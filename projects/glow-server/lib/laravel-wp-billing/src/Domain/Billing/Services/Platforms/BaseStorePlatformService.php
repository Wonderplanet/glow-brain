<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services\Platforms;

use WonderPlanet\Domain\Billing\Entities\StoreReceipt;

/**
 * 課金のストア関連をとりまとめるService
 *
 * このクラスを継承して、各種ストアのサービスを作る
 */
abstract class BaseStorePlatformService
{
    /**
     * レシートの検証を行う。
     *
     * @param string $billingPlatform
     * @param string $productId
     * @param string $receipt
     * @return StoreReceipt 検証したレシート情報を含むStoreReceiptオブジェクト
     */
    abstract public function verifyReceipt(
        string $billingPlatform,
        string $productId,
        string $receipt,
    ): StoreReceipt;

    /**
     * 購入の承認を行う
     *
     * GooglePlay向けの処理になるため、他プラットフォームでは何もしない
     * GooglePlayPlatformServiceでオーバーライドされる
     *
     * $receiptJsonには、ストアレシートのJSONを渡すこと。
     * たとえばGooglePlayStoreで購入した場合にUnityから送信されてくるレシートでは、Payloadの中に入っている文字列になる。
     *
     * @param string $receiptJson
     * @return void
     */
    public function purchaseAcknowledge(string $receiptJson)
    {
    }
}
