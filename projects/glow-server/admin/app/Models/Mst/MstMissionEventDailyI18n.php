<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionEventDailyI18n as BaseMstMissionEventDailyI18n;

class MstMissionEventDailyI18n extends BaseMstMissionEventDailyI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
