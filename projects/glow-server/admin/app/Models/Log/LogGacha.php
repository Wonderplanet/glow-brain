<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Domain\Gacha\Models\LogGacha as BaseLogGacha;

class LogGacha extends BaseLogGacha
{
    protected $connection = Database::TIDB_CONNECTION;
}
