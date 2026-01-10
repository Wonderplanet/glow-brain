<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Exchange\Models\UsrExchangeLineup as BaseUsrExchangeLineup;
use App\Models\Mst\MstExchangeLineup;

class UsrExchangeLineup extends BaseUsrExchangeLineup
{
    protected $connection = Database::TIDB_CONNECTION;

    public function mst_exchange_lineup()
    {
        return $this->hasOne(MstExchangeLineup::class, 'id', 'mst_exchange_lineup_id');
    }
}
