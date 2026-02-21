<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionEventDailyBonusSchedule as BaseMstMissionEventDailyBonusSchedule;

class MstMissionEventDailyBonusSchedule extends BaseMstMissionEventDailyBonusSchedule
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_mission_event_daily_bonus()
    {
        return $this->hasOne(MstMissionEventDailyBonus::class, 'mst_mission_event_daily_bonus_schedule_id', 'id');
    }

    public function mst_event()
    {
        return $this->hasOne(MstEvent::class, 'id', 'mst_event_id');
    }
}
