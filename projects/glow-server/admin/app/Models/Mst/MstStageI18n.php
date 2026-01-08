<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstStageI18n as BaseMstStageI18n;

class MstStageI18n extends BaseMstStageI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
