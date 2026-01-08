<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionWeekly as BaseMstMissionWeekly;
use App\Models\Usr\UsrMissionWeekly;
use App\Models\Usr\UsrMissionWeeklyProgress;

class MstMissionWeekly extends BaseMstMissionWeekly
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_mission_i18n()
    {
        return $this->hasOne(MstMissionWeeklyI18n::class, 'mst_mission_weekly_id', 'id');
    }

    public function usr_mission()
    {
        return $this->hasOne(UsrMissionWeekly::class, 'mst_mission_weekly_id', 'id');
    }

    public function usr_mission_progress()
    {
        return $this->hasOne(UsrMissionWeeklyProgress::class, 'criterion_key', 'criterion_key');
    }

    public function getCriterionKeyAttribute()
    {
        return $this->toEntity()->getCriterionKey();
    }
}
