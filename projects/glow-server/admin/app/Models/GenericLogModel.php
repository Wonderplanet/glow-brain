<?php

namespace App\Models;

use App\Constants\Database;

class GenericLogModel extends GenericModel
{
    protected $connection = Database::TIDB_CONNECTION;
    protected string $tablePrefix = 'log_';
}
