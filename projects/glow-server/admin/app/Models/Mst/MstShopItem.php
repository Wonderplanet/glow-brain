<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Constants\ShopItemCostType;
use App\Constants\ShopItemResourceType;
use App\Constants\ShopType;
use App\Domain\Resource\Mst\Models\MstShopItem as BaseMstShopItem;
use App\Dtos\RewardDto;
use App\Models\Usr\UsrShopItem;

class MstShopItem extends BaseMstShopItem
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function usr_shop_item()
    {
        return $this->hasOne(UsrShopItem::class, 'mst_shop_item_id', 'id');
    }

    public function getShopType(): string
    {
        return $this->shop_type;
    }

    /**
     * $this->rewardにアクセスした際に呼ばれる
     * @return RewardDto
     */
    public function getRewardAttribute()
    {
        return new RewardDto(
            $this->id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
        );
    }

    public function getCostAttribute(): RewardDto
    {
        return new RewardDto(
            $this->id,
            $this->cost_type,
            null,
            $this->cost_amount ?? 0,
        );
    }

    public function getShopTypeLabelAttribute(): string
    {
        $shopTypeEnum = ShopType::tryFrom($this->shop_type);
        if ($shopTypeEnum === null) {
            return '';
        }
        return $shopTypeEnum->label();
    }

    public function getCostTypeLabelAttribute(): string
    {
        $costTypeEnum = ShopItemCostType::tryFrom($this->cost_type);
        if ($costTypeEnum === null) {
            return '';
        }
        return $costTypeEnum->label();
    }

    public function getResourceTypeLabelAttribute(): string
    {
        $resourceTypeEnum = ShopItemResourceType::tryFrom($this->resource_type);
        if ($resourceTypeEnum === null) {
            return '';
        }
        return $resourceTypeEnum->label();
    }
}
