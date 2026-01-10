<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstPvpRank as BaseMstPvpRank;

class MstPvpRank extends BaseMstPvpRank
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
