<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstConfig as BaseMstConfig;

class MstConfig extends BaseMstConfig
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
