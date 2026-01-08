<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Entities;

use Carbon\CarbonImmutable;
use Google\Service\AndroidPublisher\ProductPurchase;

/**
 * GooglePlayのストアレシートクラス
 */
class StoreReceiptGooglePlay extends StoreReceipt
{
    private ProductPurchase $verifiedProductPurchase;
    /** @var array<mixed> プラットフォームレシートJSONをデコードした配列データ*/
    private array $platformStoreReceipt;

    protected string $receiptType = self::TYPE_GOOGLEPLAY;

    /**
     *
     * @param string $productId
     * @param string $receipt
     * @param array<mixed> $verifiedResponse
     */
    public function __construct(
        protected string $productId,
        protected string $receipt,
        protected array $verifiedResponse,
    ) {
        // GooglePlayの検証結果はProductPurchaseオブジェクトとして取得される。
        // それが$verifyResponseの配列0に入ってくるので、取り出しておく
        $this->verifiedProductPurchase = $verifiedResponse[0] ?? null;

        // プラットフォームのレシートを取得する
        $this->platformStoreReceipt = json_decode(
            $this->getPlatformReceiptString(),
            true
        );
    }

    public function getUnitqueId(): string
    {
        return $this->verifiedProductPurchase->getOrderId();
    }

    public function getBundleId(): string
    {
        // レシートから取得する
        return $this->platformStoreReceipt['packageName'] ?? '';
    }

    public function getPurchaseToken(): string
    {
        // レシートから取得する
        return $this->platformStoreReceipt['purchaseToken'] ?? '';
    }

    /**
     * GooglePlayStoreのレシートがサンドボックス購入かどうか
     *
     * Playストアのテストでは、アカウントがテストアカウントの場合に課金されないようになっている。
     * purchaseType=0の場合はそのテストアカウントになる。
     *
     * キーが存在しなければ通常アカウントで決済されている。
     *
     * またキーの値が0ではない場合、次のように他の購入パターンの可能性がある。
     * そのため間違いなく0となっているか確認すること
     *
     *   0. テスト（ライセンス テスト アカウントから購入）
     *   1.プロモーション（プロモーション コードを使用して購入）
     *   2.リワード（例: 有料ではなく動画広告を視聴）
     *
     * @see https://developers.google.com/android-publisher/api-ref/rest/v3/purchases.products?hl=ja
     *
     * @return boolean
     */
    public function isSandboxReceipt(): bool
    {
        return $this->verifiedProductPurchase->getPurchaseType() === 0;
    }

    /**
     * ストアレシートを返す
     *
     * Payload内のjsonカテゴリに格納されている
     *
     * @return string
     */
    public function getPlatformReceiptString(): string
    {
        $payload = $this->getPayloadString();
        $json = json_decode($payload, true);

        return $json['json'] ?? '';
    }

    /**
     * Product Idを取得する
     *
     * @return array<mixed>
     */
    public function getProductIds(): array
    {
        $receipt = $this->getPlatformReceiptString();
        $receiptJson = json_decode($receipt, true);
        return [$receiptJson['productId']];
    }

    /**
     * 購入日時を取得する
     *
     * @return CarbonImmutable|null
     */
    public function getPurchaseDate(): ?CarbonImmutable
    {
        return CarbonImmutable::createFromTimestamp(
            substr($this->verifiedProductPurchase->purchaseTimeMillis, 0, -3)
        );
    }
}
