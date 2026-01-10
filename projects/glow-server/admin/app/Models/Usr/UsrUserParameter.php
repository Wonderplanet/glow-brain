<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\User\Models\UsrUserParameter as BaseUsrUserParameter;

class UsrUserParameter extends BaseUsrUserParameter
{
    protected $connection = Database::TIDB_CONNECTION;
}

