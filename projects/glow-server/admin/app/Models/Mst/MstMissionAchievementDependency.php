<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionAchievementDependency as BaseMstMissionAchievementDependency;

class MstMissionAchievementDependency extends BaseMstMissionAchievementDependency
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
