<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstEnemyStageParameter as BaseMstEnemyStageParameter;
use App\Domain\Resource\Mst\Models\MstUnitAbility;

class MstEnemyStageParameter extends BaseMstEnemyStageParameter
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_unit_abilities()
    {
        return $this->hasOne(MstUnitAbility::class, 'id', 'mst_unit_ability_id1');
    }
}
