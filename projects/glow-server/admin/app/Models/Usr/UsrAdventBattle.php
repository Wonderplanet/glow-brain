<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\AdventBattle\Models\UsrAdventBattle as BaseUsrAdventBattle;

class UsrAdventBattle extends BaseUsrAdventBattle
{
    protected $connection = Database::TIDB_CONNECTION;

    public function usr_advent_battle_session()
    {
        return $this->hasOne(UsrAdventBattleSession::class, 'mst_advent_battle_id', 'mst_advent_battle_id');
    }
}
