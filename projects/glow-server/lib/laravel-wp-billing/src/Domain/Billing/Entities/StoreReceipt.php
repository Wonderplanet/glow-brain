<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Entities;

use Carbon\CarbonImmutable;

/**
 * ストアレシートの規定クラス
 */
abstract class StoreReceipt
{
    /** レシートの種類 */
    public const TYPE_APPSTORE = 'appstore';
    public const TYPE_GOOGLEPLAY = 'googleplay';
    public const TYPE_FAKESTORE = 'fakestore';
    public const TYPE_NULL = 'null';

    /**
     * レシートのタイプ
     *
     * @var string
     */
    protected string $receiptType = self::TYPE_NULL;

    /**
     * コンストラクタ
     *
     * $verifiedResponseはストアによって中身が変わるためmixedにしている。
     * 詳細は各ストアの処理を確認すること
     *
     * @param string $productId
     * @param string $receipt
     * @param array<mixed> $verifiedResponse verify後に得られたレスポンス
     */
    public function __construct(
        protected string $productId,
        protected string $receipt,
        protected array $verifiedResponse,
    ) {
    }

    /**
     * レシートのユニークIDを取得する
     *
     * 主にトランザクションIDになる
     *
     * @return string
     */
    abstract public function getUnitqueId(): string;

    /**
     * このレシートオブジェクトがサンドボックスレシートか
     *
     * サンドボックスレシートとは、開発環境などで使用される、実際には課金されていないストアレシートを指す
     *
     * @return boolean
     */
    abstract public function isSandboxReceipt(): bool;

    /**
     * レシートの種類を取得する
     *
     * @return string
     */
    public function getReceiptType(): string
    {
        return $this->receiptType;
    }

    /**
     * レシートデータの文字列を返す
     *
     * @return string
     */
    public function getReceipt(): string
    {
        return $this->receipt;
    }

    /**
     * 各ストアプラットフォームのレシート文字列を返す
     *
     * AppStoreの場合はBase64エンコードされた文字列
     * GooglePlayの場合はJSON文字列
     *
     * Unity IAPのレシートでは、Payloadにストアレシートが格納されている
     *
     * @return string
     */
    abstract public function getPlatformReceiptString(): string;

    /**
     * Payloadに格納されている文字列を返す
     *
     * Unity IAPのレシートでは、Payloadにストアレシートが格納されているため、一括で取得項目を用意する
     *
     * @return string
     */
    public function getPayloadString(): string
    {
        $json = json_decode($this->receipt, true);
        return $json['Payload'] ?? '';
    }

    /**
     * Bundle IDを取得する
     *
     * 便宜上Bundle IDと読んでいるが、AppStore以外では存在しないので
     * その場合はBundle ID相当の文字列を返す。
     * たとえばGooglePlayStoreであれば、packageNameとなる。
     *
     * @return string
     */
    abstract public function getBundleId(): string;

    /**
     * Purchase Tokenを取得する
     *
     * GooglePlayのみなのでAppStoreは空文字となる
     * @return string
     */
    abstract public function getPurchaseToken(): string;

    /**
     * Product IDを取得する
     *
     * @return array<mixed>
     */
    abstract public function getProductIds(): array;

    /**
     * 購入日時を取得する
     *
     * @return CarbonImmutable|null
     */
    abstract public function getPurchaseDate(): ?CarbonImmutable;
}
