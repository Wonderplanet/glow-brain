<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\AdventBattle\Models\UsrAdventBattleSession as BaseUsrAdventBattleSession;

class UsrAdventBattleSession extends BaseUsrAdventBattleSession
{
    protected $connection = Database::TIDB_CONNECTION;
}
