<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstPvpI18n as BaseMstPvpI18n;

class MstPvpI18n extends BaseMstPvpI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
