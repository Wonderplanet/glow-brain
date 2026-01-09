<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\Unit\Models\LogUnitRankUp as BaseLogUnitRankUp;
use App\Traits\AthenaModelTrait;

class LogUnitRankUp extends BaseLogUnitRankUp implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;
}
