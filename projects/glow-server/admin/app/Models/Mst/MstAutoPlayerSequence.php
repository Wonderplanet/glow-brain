<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstAutoPlayerSequence as BaseMstAutoPlayerSequence;

class MstAutoPlayerSequence extends BaseMstAutoPlayerSequence
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
    
    public function mst_enemy_character()
    {
        return $this->hasOne(MstEnemyCharacter::class, 'id', 'action_value');
    }

    public function mst_attack()
    {
        return $this->hasOne(MstAttack::class, 'mst_unit_id', 'action_value');
    }
}
