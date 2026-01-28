<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstPvpBonusPoint as BaseMstPvpBonusPoint;

class MstPvpBonusPoint extends BaseMstPvpBonusPoint
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
