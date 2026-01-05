<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstStageEventReward as BaseMstStageEventReward;
use App\Domain\Stage\Enums\StageRewardCategory;
use App\Dtos\RewardDto;

class MstStageEventReward extends BaseMstStageEventReward
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    /**
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

    public function isFirstClearReward(): bool
    {
        return $this->reward_category === StageRewardCategory::FIRST_CLEAR->value;
    }

    public function isAlwaysReward(): bool
    {
        return $this->reward_category === StageRewardCategory::ALWAYS->value;
    }
}
