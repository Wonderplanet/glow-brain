<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\OprStepupGacha as BaseOprStepupGacha;

class OprStepupGacha extends BaseOprStepupGacha
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function opr_gacha()
    {
        return $this->belongsTo(OprGacha::class, 'opr_gacha_id', 'id');
    }
}
