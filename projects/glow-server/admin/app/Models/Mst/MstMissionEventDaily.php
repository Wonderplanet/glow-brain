<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionEventDaily as BaseMstMissionEventDaily;

class MstMissionEventDaily extends BaseMstMissionEventDaily
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_mission_i18n()
    {
        return $this->hasOne(MstMissionEventDailyI18n::class, 'mst_mission_event_daily_id', 'id');
    }

    public function getCriterionKeyAttribute()
    {
        return $this->toEntity()->getCriterionKey();
    }
}
