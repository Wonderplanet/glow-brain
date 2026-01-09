<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\IdleIncentive\Models\UsrIdleIncentive as BaseUsrIdleIncentive;

class UsrIdleIncentive extends BaseUsrIdleIncentive
{
    protected $connection = Database::TIDB_CONNECTION;
}
