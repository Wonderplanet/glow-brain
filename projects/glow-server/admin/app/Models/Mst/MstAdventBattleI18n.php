<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstAdventBattleI18n as BaseMstAdventBattleI18n;

class MstAdventBattleI18n extends BaseMstAdventBattleI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

}
