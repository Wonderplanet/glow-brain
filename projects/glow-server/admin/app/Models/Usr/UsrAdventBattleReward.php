<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\AdventBattle\Models\UsrAdventBattleReward as BaseUsrAdventBattleReward;

class UsrAdventBattleReward extends BaseUsrAdventBattleReward
{
    protected $connection = Database::TIDB_CONNECTION;
}
