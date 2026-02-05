<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Entities;

use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;
use WonderPlanet\Domain\Currency\Entities\BaseModelEntity;

/**
 * usr_store_infoのデータを所持するエンティティ
 *
 * 読み取り専用でデータのみ使用するため作成
 */
class UsrStoreInfoEntity extends BaseModelEntity
{
    // DBテーブルカラムのプロパティ
    public string $id;
    public string $usr_user_id;
    public int $age;
    public int $paid_price;
    public ?string $renotify_at;
    public int $total_vip_point;

    public function __construct(UsrStoreInfo $model)
    {
        parent::__construct($model);

        $this->id = $model->id;
        $this->usr_user_id = $model->usr_user_id;
        $this->age = $model->age;
        $this->paid_price = (int)$model->paid_price;
        $this->renotify_at = $model->renotify_at;
        $this->total_vip_point = $model->total_vip_point;
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

    public function getAge(): int
    {
        return $this->age;
    }

    public function getPaidPrice(): int
    {
        return $this->paid_price;
    }

    public function getRenotifyAt(): ?string
    {
        return $this->renotify_at;
    }

    public function getTotalVipPoint(): int
    {
        return $this->total_vip_point;
    }
}
