<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionEventDailyBonus as BaseMstMissionEventDailyBonus;

class MstMissionEventDailyBonus extends BaseMstMissionEventDailyBonus
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_mission_event_daily_bonus_schedule()
    {
        return $this->hasMany(MstMissionEventDailyBonusSchedule::class, 'id', 'mst_mission_event_daily_bonus_schedule_id');
    }

    public function mst_mission_rewards()
    {
        return $this->hasMany(MstMissionReward::class, 'group_id', 'mst_mission_reward_group_id');
    }
}
