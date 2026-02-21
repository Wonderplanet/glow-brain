<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstEventI18n as BaseMstEventI18n;

class MstEventI18n extends BaseMstEventI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
