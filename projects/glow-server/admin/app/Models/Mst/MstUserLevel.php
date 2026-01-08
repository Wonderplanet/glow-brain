<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstUserLevel as BaseMstUserLevel;
use App\Dtos\RewardDto;
use Illuminate\Support\Collection;

class MstUserLevel extends BaseMstUserLevel
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_user_level_bonus()
    {
        return $this->hasOne(MstUserLevelBonus::class, 'level', 'level');
    }

    public function getRewardDtos(): Collection
    {
        $result = collect();
        if (!isset($this->mst_user_level_bonus->mst_user_level_bonus_groups)) {
            return $result;
        }
        foreach ($this->mst_user_level_bonus->mst_user_level_bonus_groups as $reward) {
            if ($reward->isEmpty) {
                continue;
            }
            $result->push(
                new RewardDto(
                    $reward->id,
                    $reward->resource_type,
                    $reward->resource_id,
                    $reward->resource_amount,
                )
            );
        }
        return $result;
    }
}
