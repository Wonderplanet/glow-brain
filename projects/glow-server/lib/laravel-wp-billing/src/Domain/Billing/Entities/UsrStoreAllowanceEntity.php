<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Entities;

use WonderPlanet\Domain\Billing\Models\UsrStoreAllowance;
use WonderPlanet\Domain\Currency\Entities\BaseModelEntity;

/**
 * usr_store_allowanceのデータを所持するエンティティ
 *
 * 読み取り専用でデータのみ使用するため作成
 */
class UsrStoreAllowanceEntity extends BaseModelEntity
{
    // DBテーブルカラムのプロパティ
    public string $id;
    public string $usr_user_id;
    public string $product_id;
    public string $mst_store_product_id;
    public string $product_sub_id;
    public string $os_platform;
    public string $billing_platform;
    public string $device_id;

    public function __construct(UsrStoreAllowance $model)
    {
        parent::__construct($model);

        $this->id = $model->id;
        $this->usr_user_id = $model->usr_user_id;
        $this->product_id = $model->product_id;
        $this->mst_store_product_id = $model->mst_store_product_id;
        $this->product_sub_id = $model->product_sub_id;
        $this->os_platform = $model->os_platform;
        $this->billing_platform = $model->billing_platform;
        $this->device_id = $model->device_id;
    }

    // getter
    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->usr_user_id;
    }

    public function getProductId(): string
    {
        return $this->product_id;
    }

    public function getMstStoreProductId(): string
    {
        return $this->mst_store_product_id;
    }

    public function getProductSubId(): string
    {
        return $this->product_sub_id;
    }

    public function getOsPlatform(): string
    {
        return $this->os_platform;
    }

    public function getBillingPlatform(): string
    {
        return $this->billing_platform;
    }

    public function getDeviceId(): string
    {
        return $this->device_id;
    }
}
