<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstArtworkI18n as BaseMstArtworkI18n;

class MstArtworkI18n extends BaseMstArtworkI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
