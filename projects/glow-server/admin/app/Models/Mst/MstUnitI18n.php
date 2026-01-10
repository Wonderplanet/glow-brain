<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstUnitI18n as BaseMstUnitI18n;

class MstUnitI18n extends BaseMstUnitI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
