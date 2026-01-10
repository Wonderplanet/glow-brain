<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstAttackI18n as BaseMstAttackI18n;

class MstAttackI18n extends BaseMstAttackI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
