<?php

namespace App\Models;

use App\Constants\Database;

class GenericOprModel extends GenericModel
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
    protected string $tablePrefix = 'opr_';
}
