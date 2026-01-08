<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionEvent as BaseMstMissionEvent;

class MstMissionEvent extends BaseMstMissionEvent
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_mission_i18n()
    {
        return $this->hasOne(MstMissionEventI18n::class, 'mst_mission_event_id', 'id');
    }

    public function mst_mission_dependency()
    {
        return $this->hasMany(MstMissionEventDependency::class, 'mst_mission_event_id', 'id');
    }

    public function getCriterionKeyAttribute()
    {
        return $this->toEntity()->getCriterionKey();
    }
}
