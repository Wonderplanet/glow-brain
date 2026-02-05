<?php

namespace App\Models;

use App\Constants\Database;

class GenericSysModel extends GenericModel
{
    protected $connection = Database::TIDB_CONNECTION;
    protected string $tablePrefix = 'sys_';
}
