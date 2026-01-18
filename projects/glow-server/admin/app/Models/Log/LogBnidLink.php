<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\User\Models\LogBnidLink as BaseLogBnidLink;
use App\Traits\AthenaModelTrait;

class LogBnidLink extends BaseLogBnidLink implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;
}
