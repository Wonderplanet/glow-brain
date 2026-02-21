<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Entities;

use Carbon\CarbonImmutable;

/**
 * App Store (Apple)のストアレシートクラス
 */
class StoreReceiptAppStore extends StoreReceipt
{
    protected string $receiptType = self::TYPE_APPSTORE;

    /**
     * このレシートのトランザクションID
     *
     * @var string
     */
    private $transactionId = '';

    /**
     * コンストラクタ
     *
     * 取得したverifiedReceiptDataより各種情報をとる
     *
     * @param string $productId
     * @param string $receipt
     * @param array<mixed> $verifiedResponse verify後に得られたレスポンス
     */
    public function __construct(
        string $productId,
        string $receipt,
        array $verifiedResponse,
    ) {
        parent::__construct($productId, $receipt, $verifiedResponse);

        // verifiedResponse の時点では、ストアAPIからのレスポンスをそのまま格納している
        // @see https://developer.apple.com/documentation/appstorereceipts/responsebody
        $this->transactionId = $this->getTransactionIdForApple($this->productId);
    }

    public function getUnitqueId(): string
    {
        return $this->transactionId;
    }

    public function getBundleId(): string
    {
        return $this->verifiedResponse['receipt']['bundle_id'] ?? '';
    }

    public function getPurchaseToken(): string
    {
        // AppStoreのレシートには存在しない項目なので空文字を返す
        return '';
    }

    /**
     * サンドボックスレシートの判定
     *
     * @return boolean
     */
    public function isSandboxReceipt(): bool
    {
        // verifiedResponseのenvironmentがSandboxであれば、サンドボックスレシート
        //  @see https://developer.apple.com/documentation/appstorereceipts/responsebody
        return ($this->verifiedResponse['environment'] === 'Sandbox');
    }

    /**
     * ストアレシートを返す
     *
     * Payload内にBase64エンコードされたレシートが格納されている
     *
     * @return string
     */
    public function getPlatformReceiptString(): string
    {
        return $this->getPayloadString();
    }

    /**
     * Product Idを取得する
     *
     * @return array<mixed>
     */
    public function getProductIds(): array
    {
        return array_map(fn($item) => $item['product_id'], $this->verifiedResponse['receipt']['in_app']);
    }

    /**
     * 購入日時を取得する
     *
     * @return CarbonImmutable|null
     */
    public function getPurchaseDate(): ?CarbonImmutable
    {
        // in_app要素からtransaction_idをもとに購入したレシート情報を取得する
        // ref: https://app.clickup.com/t/86ert7y64
        $purchase = $this->getInApp($this->productId);
        if (!isset($purchase['purchase_date'])) {
            return null;
        }

        return CarbonImmutable::parse($purchase['purchase_date']);
    }

    /**
     * レシートからtransaction_idを取得する
     *
     * 1つのレシートに複数のtransaction情報が存在している場合があるため、
     * その中から最新のtransaction_idを取得する
     *
     * $receiptDataの内容はストアからのレスポンスになるため、mixedとしている
     *
     * @param string $productId
     * @return string
     */
    private function getTransactionIdForApple(string $productId): string
    {
        // productIdをもとにin_app情報を取得する
        $newest = $this->getInApp($productId);
        return !isset($newest['transaction_id']) ? '' : $newest["transaction_id"];
    }

    /**
     * レシート情報内のin_appから購入情報を取得する
     *
     * @param string $productId
     * @return array<mixed> $inApp
     */
    private function getInApp(string $productId): array
    {
        // 1つのレシートに、transaction_idは複数個存在することがある
        $newest = [];
        foreach ($this->verifiedResponse['receipt']['in_app'] as $v) {
            // TODO: Transaction IDでの取得方法に変更する予定
            // ref: https://github.com/Wonderplanet/laravel-wp-framework/pull/1223#discussion_r2003169904

            // 別のプロダクトの購入履歴が入ってくる可能性があるため、それを避ける
            if ($v['product_id'] !== $productId) {
                continue;
            }

            // purchase_date は、ユーザが購入処理を行った日付（UTC）。
            // 以前のトランザクションを復元したトランザクションの場合は、復元が行われた日付になります。
            // purchase_date_ms は、purchase_dateのミリ秒変換。
            if (count($newest) === 0 || $newest['purchase_date_ms'] < $v['purchase_date_ms']) {
                $newest = $v;
            }
        }

        return $newest;
    }
}
