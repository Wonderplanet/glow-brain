<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstComebackBonusSchedule as BaseMstComebackBonusSchedule;

class MstComebackBonusSchedule extends BaseMstComebackBonusSchedule
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
