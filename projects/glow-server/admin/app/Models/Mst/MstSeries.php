<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstSeries as BaseMstSeries;

class MstSeries extends BaseMstSeries
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_series_i18n()
    {
        return $this->hasOne(MstSeriesI18n::class, 'mst_series_id', 'id');
    }
}
