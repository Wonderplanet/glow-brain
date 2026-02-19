<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstUserLevelBonus as BaseMstUserLevelBonus;

class MstUserLevelBonus extends BaseMstUserLevelBonus
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_user_level_bonus_groups()
    {
        return $this->hasMany(
            MstUserLevelBonusGroup::class,
            'mst_user_level_bonus_group_id',
            'mst_user_level_bonus_group_id',
        );
    }
}
