<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Constants\RewardType;
use App\Domain\Resource\Mst\Models\MstFragmentBoxGroup as BaseMstFragmentBoxGroup;
use App\Dtos\RewardDto;

class MstFragmentBoxGroup extends BaseMstFragmentBoxGroup
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function getRewardAttribute(): RewardDto
    {
        return new RewardDto(
            $this->id,
            RewardType::ITEM->value,
            $this->mst_item_id,
            1,
        );
    }
}
