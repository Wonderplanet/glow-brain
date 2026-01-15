<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstStageEventSetting as BaseMstStageEventSetting;

class MstStageEventSetting extends BaseMstStageEventSetting
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
