<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Constants\PvpRewardCategory;
use App\Domain\Resource\Mst\Models\MstPvpRewardGroup as BaseMstPvpRewardGroup;

class MstPvpRewardGroup extends BaseMstPvpRewardGroup
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_pvp_rewards()
    {
        return $this->hasMany(MstPvpReward::class, 'mst_pvp_reward_group_id', 'id');
    }

    public function isRankClassReward(): bool
    {
        return $this->reward_category === PvpRewardCategory::RANK_CLASS->value;
    }
}
