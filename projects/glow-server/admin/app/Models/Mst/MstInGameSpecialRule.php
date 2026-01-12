<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstInGameSpecialRule as BaseMstInGameSpecialRule;

class MstInGameSpecialRule extends BaseMstInGameSpecialRule
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
