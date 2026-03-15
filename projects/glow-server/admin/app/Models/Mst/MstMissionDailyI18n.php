<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionDailyI18n as BaseMstMissionDailyI18n;

class MstMissionDailyI18n extends BaseMstMissionDailyI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    protected $table = 'mst_mission_dailies_i18n';
}
