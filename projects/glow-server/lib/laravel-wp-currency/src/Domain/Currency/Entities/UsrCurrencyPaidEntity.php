<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

use WonderPlanet\Domain\Billing\Entities\UsrStoreProductHistoryEntity;
use WonderPlanet\Domain\Currency\Models\UsrCurrencyPaid;

/**
 * usr_currency_paidのデータを所持するエンティティ
 *
 * 読み取り専用でデータのみ使用するため作成
 */
class UsrCurrencyPaidEntity extends BaseModelEntity
{
    // DBテーブルカラムのプロパティ
    public string $id;
    public int $seq_no;
    public string $usr_user_id;
    public int $left_amount;
    public string $purchase_price;
    public int $purchase_amount;
    public string $price_per_amount;
    public int $vip_point;
    public string $currency_code;
    public string $receipt_unique_id;
    public bool $is_sandbox;
    public string $os_platform;
    public string $billing_platform;

    // user_id、billing_platform、receipt_unique_idと紐づいた購入履歴情報のEntity
    private ?UsrStoreProductHistoryEntity $usr_store_product_history_entity;

    public function __construct(UsrCurrencyPaid $model)
    {
        parent::__construct($model);

        $this->id = $model->id;
        $this->seq_no = $model->seq_no;
        $this->usr_user_id = $model->usr_user_id;
        $this->left_amount = $model->left_amount;
        $this->purchase_price = $model->purchase_price;
        $this->purchase_amount = $model->purchase_amount;
        $this->price_per_amount = $model->price_per_amount;
        $this->vip_point = $model->vip_point;
        $this->currency_code = $model->currency_code;
        $this->receipt_unique_id = $model->receipt_unique_id;
        // スキーマではtinyintになっているのでboolにキャストする
        $this->is_sandbox = (bool)$model->is_sandbox;
        $this->os_platform = $model->os_platform;
        $this->billing_platform = $model->billing_platform;
        $this->usr_store_product_history_entity = $model->getUsrStoreProductHistoryEntity();
    }

    // getter
    public function getId(): string
    {
        return $this->id;
    }

    public function getSeqNo(): int
    {
        return $this->seq_no;
    }

    public function getUserId(): string
    {
        return $this->usr_user_id;
    }

    public function getLeftAmount(): int
    {
        return $this->left_amount;
    }

    public function getPurchasePrice(): string
    {
        return $this->purchase_price;
    }

    public function getPurchaseAmount(): int
    {
        return $this->purchase_amount;
    }

    public function getPricePerAmount(): string
    {
        return $this->price_per_amount;
    }

    public function getVipPoint(): int
    {
        return $this->vip_point;
    }

    public function getCurrencyCode(): string
    {
        return $this->currency_code;
    }

    public function getReceiptUniqueId(): string
    {
        return $this->receipt_unique_id;
    }

    public function getIsSandbox(): bool
    {
        return $this->is_sandbox;
    }

    public function getOsPlatform(): string
    {
        return $this->os_platform;
    }

    public function getBillingPlatform(): string
    {
        return $this->billing_platform;
    }

    public function getUsrStoreProductHistoryEntity(): ?UsrStoreProductHistoryEntity
    {
        return $this->usr_store_product_history_entity;
    }
}
