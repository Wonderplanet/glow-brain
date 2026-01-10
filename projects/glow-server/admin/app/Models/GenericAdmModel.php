<?php

namespace App\Models;

use App\Constants\Database;

class GenericAdmModel extends GenericModel
{
    protected $connection = Database::ADMIN_CONNECTION;
    protected string $tablePrefix = 'adm_';
}
