<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstQuestI18n as BaseMstQuestI18n;

class MstQuestI18n extends BaseMstQuestI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
