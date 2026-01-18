<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Constants\MissionDailyBonusType;
use App\Domain\Resource\Mst\Models\MstMissionDailyBonus as BaseMstMissionDailyBonus;
use App\Models\Usr\UsrMissionDailyBonus;

class MstMissionDailyBonus extends BaseMstMissionDailyBonus
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function usr_mission()
    {
        return $this->hasOne(UsrMissionDailyBonus::class, 'mst_mission_daily_bonus_id', 'id');
    }

    public function getTypeLabelAttribute(): string
    {
        $enum = MissionDailyBonusType::from($this->mission_daily_bonus_type);

        return $enum?->label() ?? '';
    }
}
