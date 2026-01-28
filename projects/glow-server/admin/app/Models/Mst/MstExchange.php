<?php

declare(strict_types=1);

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstExchange as BaseMstExchange;

class MstExchange extends BaseMstExchange
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_exchange_i18n()
    {
        return $this->hasOne(MstExchangeI18n::class, 'mst_exchange_id', 'id');
    }

    public function lineups()
    {
        return $this->hasMany(MstExchangeLineup::class, 'group_id', 'lineup_group_id');
    }

    public function getName(): string
    {
        return $this->mst_exchange_i18n?->name ?? '';
    }
}
