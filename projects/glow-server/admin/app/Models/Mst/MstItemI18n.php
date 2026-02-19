<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstItemI18n as BaseMstItemI18n;

class MstItemI18n extends BaseMstItemI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
