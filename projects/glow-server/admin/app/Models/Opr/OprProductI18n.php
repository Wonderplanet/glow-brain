<?php

namespace App\Models\Opr;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\OprProductI18n as BaseOprProductI18n;

class OprProductI18n extends BaseOprProductI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
