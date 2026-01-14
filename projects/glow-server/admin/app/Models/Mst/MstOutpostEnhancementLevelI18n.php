<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancementLevelI18n as BaseMstOutpostEnhancementLevelI18n;

class MstOutpostEnhancementLevelI18n extends BaseMstOutpostEnhancementLevelI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
