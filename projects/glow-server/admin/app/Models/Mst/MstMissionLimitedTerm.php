<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTerm as BaseMstMissionLimitedTerm;

class MstMissionLimitedTerm extends BaseMstMissionLimitedTerm
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_mission_i18n()
    {
        return $this->hasOne(MstMissionLimitedTermI18n::class, 'mst_mission_limited_term_id', 'id');
    }

    public function mst_mission_limited_term_dependency()
    {
        return $this->hasMany(MstMissionLimitedTermDependency::class, 'mst_mission_limited_term_id', 'id');
    }

    public function getCriterionKeyAttribute()
    {
        return $this->toEntity()->getCriterionKey();
    }
}
