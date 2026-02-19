<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\AdventBattle\Models\LogAdventBattleAction as BaseLogAdventBattleAction;
use App\Traits\AthenaModelTrait;

class LogAdventBattleAction extends BaseLogAdventBattleAction implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;
}
