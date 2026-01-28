<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstPackContent as BaseMstPackContent;
use App\Dtos\RewardDto;

class MstPackContent extends BaseMstPackContent
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
}
