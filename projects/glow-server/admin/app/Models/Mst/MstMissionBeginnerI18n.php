<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionBeginnerI18n as BaseMstMissionBeginnerI18n;

class MstMissionBeginnerI18n extends BaseMstMissionBeginnerI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    protected $table = 'mst_mission_beginners_i18n';
}
