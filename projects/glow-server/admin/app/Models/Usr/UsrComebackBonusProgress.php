<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\DailyBonus\Models\UsrComebackBonusProgress as BaseUsrComebackBonusProgress;
use App\Domain\Resource\Mst\Models\MstComebackBonus;
use App\Domain\Resource\Mst\Models\MstComebackBonusSchedule;

class UsrComebackBonusProgress extends BaseUsrComebackBonusProgress
{
    protected $connection = Database::TIDB_CONNECTION;

    public function mst_comeback_bonus_schedule()
    {
        return $this->belongsTo(MstComebackBonusSchedule::class, 'mst_comeback_bonus_schedule_id', 'id');
    }
}
