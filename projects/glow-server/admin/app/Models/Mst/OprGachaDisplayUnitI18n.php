<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Constants\RewardType;
use App\Domain\Resource\Mst\Models\OprGachaDisplayUnitI18n as BaseOprGachaDisplayUnitI18n;
use App\Dtos\RewardDto;

class OprGachaDisplayUnitI18n extends BaseOprGachaDisplayUnitI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function getRewardAttribute(): RewardDto
    {
        return new RewardDto(
            $this->id,
            RewardType::UNIT->value,
            $this->mst_unit_id,
            1,
        );
    }
}
