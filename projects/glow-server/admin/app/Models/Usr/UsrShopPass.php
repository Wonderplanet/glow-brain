<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Shop\Models\UsrShopPass as BaseUsrShopPass;

class UsrShopPass extends BaseUsrShopPass
{
    protected $connection = Database::TIDB_CONNECTION;
}

