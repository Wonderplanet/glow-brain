<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaReward as BaseMstUnitEncyclopediaReward;
use App\Dtos\RewardDto;

class MstUnitEncyclopediaReward extends BaseMstUnitEncyclopediaReward
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_item()
    {
        return $this->hasOne(MstItem::class, 'id', 'resource_id');
    }

    public function mst_emblem()
    {
        return $this->hasOne(MstEmblem::class, 'id', 'resource_id');
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
}
