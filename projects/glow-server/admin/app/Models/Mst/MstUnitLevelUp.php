<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstUnitLevelUp as BaseMstUnitLevelUp;

class MstUnitLevelUp extends BaseMstUnitLevelUp
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
