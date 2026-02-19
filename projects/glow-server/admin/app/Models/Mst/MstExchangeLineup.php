<?php

declare(strict_types=1);

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstExchangeLineup as BaseMstExchangeLineup;

class MstExchangeLineup extends BaseMstExchangeLineup
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function rewards()
    {
        return $this->hasMany(MstExchangeReward::class, 'mst_exchange_lineup_id', 'id');
    }

    public function costs()
    {
        return $this->hasMany(MstExchangeCost::class, 'mst_exchange_lineup_id', 'id');
    }
}
