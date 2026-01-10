<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaEffect as BaseMstUnitEncyclopediaEffect;

class MstUnitEncyclopediaEffect extends BaseMstUnitEncyclopediaEffect
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
