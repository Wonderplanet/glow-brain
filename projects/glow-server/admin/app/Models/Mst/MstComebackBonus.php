<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstComebackBonus as BaseMstComebackBonus;
use App\Domain\Resource\Mst\Models\MstComebackBonusSchedule;

class MstComebackBonus extends BaseMstComebackBonus
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function schedule()
    {
        return $this->belongsTo(MstComebackBonusSchedule::class, 'mst_comeback_bonus_schedule_id');
    }
}
