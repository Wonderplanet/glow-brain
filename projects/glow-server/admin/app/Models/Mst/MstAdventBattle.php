<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstAdventBattle as BaseMstAdventBattle;

class MstAdventBattle extends BaseMstAdventBattle
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_advent_battle_i18n(){
        return $this->hasOne(MstAdventBattleI18n::class, 'mst_advent_battle_id', 'id');
    }
}
