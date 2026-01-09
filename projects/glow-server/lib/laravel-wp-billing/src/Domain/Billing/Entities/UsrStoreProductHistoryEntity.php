<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Entities;

use WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory;
use WonderPlanet\Domain\Currency\Entities\BaseModelEntity;

/**
 * usr_store_product_historyのデータを所持するエンティティ
 *
 * 読み取り専用でデータのみ使用するため作成
 */
class UsrStoreProductHistoryEntity extends BaseModelEntity
{
    // DBテーブルカラムのプロパティ
    public string $id;
    public string $receipt_unique_id;
    public string $os_platform;
    public string $usr_user_id;
    public int $seq_no;
    public string $device_id;
    public int $age;
    public string $product_sub_id;
    public string $platform_product_id;
    public string $mst_store_product_id;
    public string $currency_code;
    public string $receipt_bundle_id;
    public int $paid_amount;
    public int $free_amount;
    public string $purchase_price;
    public string $price_per_amount;
    public int $vip_point;
    public bool $is_sandbox;
    public string $billing_platform;

    /**
     * @param UsrStoreProductHistory $model
     */
    public function __construct(UsrStoreProductHistory $model)
    {
        parent::__construct($model);

        $this->id = $model->id;
        $this->receipt_unique_id = $model->receipt_unique_id;
        $this->os_platform = $model->os_platform;
        $this->usr_user_id = $model->usr_user_id;
        $this->device_id = $model->device_id;
        $this->age = $model->age;
        $this->product_sub_id = $model->product_sub_id;
        $this->platform_product_id = $model->platform_product_id;
        $this->mst_store_product_id = $model->mst_store_product_id;
        $this->receipt_bundle_id = $model->receipt_bundle_id;
        $this->paid_amount = $model->paid_amount;
        $this->free_amount = $model->free_amount;
        $this->purchase_price = $model->purchase_price;
        $this->price_per_amount = $model->price_per_amount;
        $this->vip_point = $model->vip_point;
        // スキーマではtinyintになっているのでboolにキャストする
        $this->is_sandbox = (bool)$model->is_sandbox;
        $this->billing_platform = $model->billing_platform;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'receipt_unique_id' => $this->getReceiptUniqueId(),
            'os_platform' => $this->getOsPlatform(),
            'usr_user_id' => $this->getUsrUserId(),
            'device_id' => $this->getDeviceId(),
            'age' => $this->getAge(),
            'product_sub_id' => $this->getProductSubId(),
            'platform_product_id' => $this->getPlatformProductId(),
            'mst_store_product_id' => $this->getMstStoreProductId(),
            'receipt_bundle_id' => $this->getReceiptBundleId(),
            'paid_amount' => $this->getPaidAmount(),
            'free_amount' => $this->getFreeAmount(),
            'purchase_price' => $this->getPurchasePrice(),
            'price_per_amount' => $this->getPricePerAmount(),
            'vip_point' => $this->getVipPoint(),
            'is_sandbox' => $this->getIsSandbox(),
            'billing_platform' => $this->getBillingPlatform(),
        ];
    }

    // getter
    public function getId(): string
    {
        return $this->id;
    }

    public function getReceiptUniqueId(): string
    {
        return $this->receipt_unique_id;
    }

    public function getOsPlatform(): string
    {
        return $this->os_platform;
    }

    public function getUsrUserId(): string
    {
        return $this->usr_user_id;
    }

    public function getDeviceId(): string
    {
        return $this->device_id;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getProductSubId(): string
    {
        return $this->product_sub_id;
    }

    public function getPlatformProductId(): string
    {
        return $this->platform_product_id;
    }

    public function getMstStoreProductId(): string
    {
        return $this->mst_store_product_id;
    }

    public function getCurrencyCode(): string
    {
        return $this->currency_code;
    }

    public function getReceiptBundleId(): string
    {
        return $this->receipt_bundle_id;
    }

    public function getPaidAmount(): int
    {
        return $this->paid_amount;
    }

    public function getFreeAmount(): int
    {
        return $this->free_amount;
    }

    public function getPurchasePrice(): string
    {
        return $this->purchase_price;
    }

    public function getPricePerAmount(): string
    {
        return $this->price_per_amount;
    }

    public function getVipPoint(): int
    {
        return $this->vip_point;
    }

    public function getIsSandbox(): bool
    {
        return $this->is_sandbox;
    }

    public function getBillingPlatform(): string
    {
        return $this->billing_platform;
    }
}
