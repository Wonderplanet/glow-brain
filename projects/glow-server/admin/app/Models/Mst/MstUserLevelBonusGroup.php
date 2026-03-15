<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstUserLevelBonusGroup as BaseMstUserLevelBonusGroup;

class MstUserLevelBonusGroup extends BaseMstUserLevelBonusGroup
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
