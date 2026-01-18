<?php

namespace App\Models\Mst;

use App\Constants\AdventBattleRewardCategory;
use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstAdventBattleRewardGroup as BaseMstAdventBattleRewardGroup;

class MstAdventBattleRewardGroup extends BaseMstAdventBattleRewardGroup
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_advent_battle_rewards()
    {
        return $this->hasMany(MstAdventBattleReward::class, 'mst_advent_battle_reward_group_id', 'id');
    }

    public function isMaxScoreReward(): bool
    {
        return $this->reward_category === AdventBattleRewardCategory::MAX_SCORE->value;
    }

    public function isRankingReward(): bool
    {
        return $this->reward_category === AdventBattleRewardCategory::RANKING->value;
    }

    public function isRankReward(): bool
    {
        return $this->reward_category === AdventBattleRewardCategory::RANK->value;
    }

    public function isRaidTotalScoreReward(): bool
    {
        return $this->reward_category === AdventBattleRewardCategory::RAID_TOTAL_SCORE->value;
    }
}
