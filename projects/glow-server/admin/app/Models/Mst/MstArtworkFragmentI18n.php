<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstArtworkFragmentI18n as BaseMstArtworkFragmentI18n;

class MstArtworkFragmentI18n extends BaseMstArtworkFragmentI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
