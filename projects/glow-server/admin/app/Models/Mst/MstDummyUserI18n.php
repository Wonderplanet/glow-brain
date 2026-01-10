<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstDummyUserI18n as BaseMstDummyUserI18n;

class MstDummyUserI18n extends BaseMstDummyUserI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
