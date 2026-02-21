<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionAchievement as BaseMstMissionAchievement;

class MstMissionAchievement extends BaseMstMissionAchievement
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_mission_i18n()
    {
        return $this->hasOne(MstMissionAchievementI18n::class, 'mst_mission_achievement_id', 'id');
    }

    public function mst_mission_dependency()
    {
        return $this->hasMany(MstMissionAchievementDependency::class, 'mst_mission_achievement_id', 'id');
    }

    public function getCriterionKeyAttribute()
    {
        return $this->toEntity()->getCriterionKey();
    }

    public function mst_mission_reward()
    {
        return $this->hasMany(MstMissionReward::class, 'group_id', 'mst_mission_reward_group_id');
    }
}
