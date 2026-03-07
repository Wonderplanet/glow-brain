<?php

declare(strict_types=1);

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstExchangeI18n as BaseMstExchangeI18n;

class MstExchangeI18n extends BaseMstExchangeI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;
}
