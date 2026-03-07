<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionEventDependency as BaseMstMissionEventDependency;

class MstMissionEventDependency extends BaseMstMissionEventDependency
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
