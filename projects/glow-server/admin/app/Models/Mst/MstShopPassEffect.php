<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstShopPassEffect as BaseMstShopPassEffect;

class MstShopPassEffect extends BaseMstShopPassEffect
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
