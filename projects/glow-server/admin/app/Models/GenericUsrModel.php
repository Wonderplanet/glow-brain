<?php

namespace App\Models;

use App\Constants\Database;

class GenericUsrModel extends GenericModel
{
    protected $connection = Database::TIDB_CONNECTION;
    protected string $tablePrefix = 'usr_';
}
