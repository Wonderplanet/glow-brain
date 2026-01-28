<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Encyclopedia\Models\UsrReceivedUnitEncyclopediaReward as BaseUsrReceivedUnitEncyclopediaReward;
use App\Models\Mst\MstUnitEncyclopediaReward;

class UsrReceivedUnitEncyclopediaReward extends BaseUsrReceivedUnitEncyclopediaReward
{
    protected $connection = Database::TIDB_CONNECTION;

    public function mst_unit_encyclopedia_rewards()
    {
        return $this->hasOne(MstUnitEncyclopediaReward::class, 'id', 'mst_unit_encyclopedia_reward_id');
    }
}
