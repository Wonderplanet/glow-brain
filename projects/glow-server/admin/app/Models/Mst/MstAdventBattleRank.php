<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstAdventBattleRank as BaseMstAdventBattleRank;

class MstAdventBattleRank extends BaseMstAdventBattleRank
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
