<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\User\Models\UsrUserLogin as BaseUsrUserLogin;

class UsrUserLogin extends BaseUsrUserLogin
{
    protected $connection = Database::TIDB_CONNECTION;
}
