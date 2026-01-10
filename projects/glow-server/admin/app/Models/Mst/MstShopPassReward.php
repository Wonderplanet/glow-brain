<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstShopPassReward as BaseMstShopPassReward;
use App\Domain\Shop\Enums\PassRewardType;
use App\Dtos\RewardDto;

class MstShopPassReward extends BaseMstShopPassReward
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

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

    public function getPassRewardTypeLabelAttribute(): string
    {
        return PassRewardType::tryFrom($this->pass_reward_type)?->label() ?? '';
    }
}
