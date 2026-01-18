<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstEvent as BaseMstEvent;

class MstEvent extends BaseMstEvent
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_event_i18n()
    {
        return $this->hasOne(MstEventI18n::class, 'mst_event_id', 'id');
    }

    public function mst_series()
    {
        return $this->hasOne(MstSeries::class, 'id', 'mst_series_id');
    }

    public function getName(): string
    {
        return $this->mst_event_i18n?->name ?? '';
    }
}
