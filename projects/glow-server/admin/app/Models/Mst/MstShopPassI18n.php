<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstShopPassI18n as BaseMstShopPassI18n;

class MstShopPassI18n extends BaseMstShopPassI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
