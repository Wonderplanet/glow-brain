<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstEnemyCharacterI18n as BaseMstEnemyCharacterI18n;

class MstEnemyCharacterI18n extends BaseMstEnemyCharacterI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
