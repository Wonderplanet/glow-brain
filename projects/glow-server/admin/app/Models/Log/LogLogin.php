<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\User\Models\LogLogin as BaseLogLogin;
use App\Traits\AthenaModelTrait;

class LogLogin extends BaseLogLogin implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;
}
