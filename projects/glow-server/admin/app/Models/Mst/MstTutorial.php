<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstTutorial as BaseMstTutorial;

class MstTutorial extends BaseMstTutorial
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
