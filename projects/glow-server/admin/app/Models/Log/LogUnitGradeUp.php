<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\Unit\Models\LogUnitGradeUp as BaseLogUnitGradeUp;
use App\Traits\AthenaModelTrait;

class LogUnitGradeUp extends BaseLogUnitGradeUp implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;
}
