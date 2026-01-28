<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstPvpReward as BaseMstPvpReward;
use App\Dtos\RewardDto;

class MstPvpReward extends BaseMstPvpReward
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
}
