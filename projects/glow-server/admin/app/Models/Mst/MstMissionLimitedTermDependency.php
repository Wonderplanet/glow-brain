<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTermDependency as BaseMstMissionLimitedTermDependency;

class MstMissionLimitedTermDependency extends BaseMstMissionLimitedTermDependency
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
