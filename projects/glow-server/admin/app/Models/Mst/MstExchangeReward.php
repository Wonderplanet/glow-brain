<?php

declare(strict_types=1);

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstExchangeReward as BaseMstExchangeReward;
use App\Dtos\RewardDto;

class MstExchangeReward extends BaseMstExchangeReward
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
