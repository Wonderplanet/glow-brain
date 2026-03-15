<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstMissionEventI18n as BaseMstMissionEventI18n;

class MstMissionEventI18n extends BaseMstMissionEventI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
