<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstUnitGradeUp as BaseMstUnitGradeUp;

class MstUnitGradeUp extends BaseMstUnitGradeUp
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
