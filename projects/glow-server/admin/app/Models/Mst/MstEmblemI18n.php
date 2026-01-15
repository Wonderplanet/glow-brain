<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstEmblemI18n as BaseMstEmblemI18n;

class MstEmblemI18n extends BaseMstEmblemI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
