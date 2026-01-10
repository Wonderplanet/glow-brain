<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstAttack as BaseMstAttack;
use App\Domain\Resource\Mst\Models\MstAttackElement;

class MstAttack extends BaseMstAttack
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_attack_elements()
    {
        return $this->hasMany(MstAttackElement::class, 'mst_attack_id', 'id');
    }
    public function mst_attack_i18n()
    {
        return $this->hasOne(MstAttackI18n::class, 'mst_attack_id', 'id');
    }
}
