<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstIdleIncentive as BaseMstIdleIncentive;

class MstIdleIncentive extends BaseMstIdleIncentive
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
