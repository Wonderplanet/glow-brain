<?php

declare(strict_types=1);

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstExchangeCost as BaseMstExchangeCost;
use App\Dtos\RewardDto;

class MstExchangeCost extends BaseMstExchangeCost
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    /**
     * @return RewardDto
     */
    public function getCostAttribute()
    {
        return new RewardDto(
            $this->id,
            $this->cost_type,
            $this->cost_id,
            $this->cost_amount,
        );
    }
}
