<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionAchievementI18n as BaseMstMissionAchievementI18n;

class MstMissionAchievementI18n extends BaseMstMissionAchievementI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    protected $table = 'mst_mission_achievements_i18n';
}
