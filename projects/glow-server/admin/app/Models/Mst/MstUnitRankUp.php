<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstUnitRankUp as BaseMstUnitRankUp;

class MstUnitRankUp extends BaseMstUnitRankUp
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
