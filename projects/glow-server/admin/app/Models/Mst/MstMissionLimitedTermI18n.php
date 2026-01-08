<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTermI18n as BaseMstMissionLimitedTermI18n;

class MstMissionLimitedTermI18n extends BaseMstMissionLimitedTermI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
