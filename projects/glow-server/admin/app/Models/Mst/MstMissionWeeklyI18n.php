<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionWeeklyI18n as BaseMstMissionWeeklyI18n;

class MstMissionWeeklyI18n extends BaseMstMissionWeeklyI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    protected $table = 'mst_mission_weeklies_i18n';
}
