<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionDaily as BaseMstMissionDaily;
use App\Models\Usr\UsrMissionDaily;
use App\Models\Usr\UsrMissionDailyProgress;

class MstMissionDaily extends BaseMstMissionDaily
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_mission_i18n()
    {
        return $this->hasOne(MstMissionDailyI18n::class, 'mst_mission_daily_id', 'id');
    }

    public function usr_mission()
    {
        return $this->hasOne(UsrMissionDaily::class, 'mst_mission_daily_id', 'id');
    }

    public function usr_mission_progress()
    {
        return $this->hasOne(UsrMissionDailyProgress::class, 'criterion_key', 'criterion_key');
    }

    public function getCriterionKeyAttribute()
    {
        return $this->toEntity()->getCriterionKey();
    }
}
