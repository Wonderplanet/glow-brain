<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancementI18n as BaseMstOutpostEnhancementI18n;

class MstOutpostEnhancementI18n extends BaseMstOutpostEnhancementI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
