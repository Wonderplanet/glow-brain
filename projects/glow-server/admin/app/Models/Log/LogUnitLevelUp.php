<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\Unit\Models\LogUnitLevelUp as BaseLogUnitLevelUp;
use App\Traits\AthenaModelTrait;

class LogUnitLevelUp extends BaseLogUnitLevelUp implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;

}
