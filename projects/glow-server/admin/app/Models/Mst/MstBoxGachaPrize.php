<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstBoxGachaPrize as BaseMstBoxGachaPrize;
use App\Dtos\RewardDto;

class MstBoxGachaPrize extends BaseMstBoxGachaPrize
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_box_gacha_group()
    {
        return $this->belongsTo(MstBoxGachaGroup::class, 'mst_box_gacha_group_id', 'id');
    }

    public function getRewardDto(): RewardDto
    {
        return new RewardDto(
            $this->id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
        );
    }
}
