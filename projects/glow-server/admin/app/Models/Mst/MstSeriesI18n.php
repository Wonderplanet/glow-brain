<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstSeriesI18n as BaseMstSeriesI18n;

class MstSeriesI18n extends BaseMstSeriesI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
