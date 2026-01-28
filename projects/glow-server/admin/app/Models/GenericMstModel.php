<?php

namespace App\Models;

use App\Constants\Database;

class GenericMstModel extends GenericModel
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
    protected string $tablePrefix = 'mst_';
}
