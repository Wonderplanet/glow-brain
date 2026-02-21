<?php

declare(strict_types=1);

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\OprStepupGachaStepReward as BaseOprStepupGachaStepReward;
use App\Dtos\RewardDto;

class OprStepupGachaStepReward extends BaseOprStepupGachaStepReward
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function getRewardAttribute(): RewardDto
    {
        return new RewardDto(
            (string) $this->id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
        );
    }
}
