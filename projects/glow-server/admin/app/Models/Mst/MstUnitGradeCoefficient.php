<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstUnitGradeCoefficient as BaseMstUnitGradeCoefficient;

class MstUnitGradeCoefficient extends BaseMstUnitGradeCoefficient
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
