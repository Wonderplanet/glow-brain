<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Gacha\Models\UsrGacha as BaseUsrGacha;
use App\Models\Mst\OprGacha;
use App\Models\Mst\OprStepupGacha;

class UsrGacha extends BaseUsrGacha
{
    protected $connection = Database::TIDB_CONNECTION;

    public function opr_gacha()
    {
        return $this->hasOne(OprGacha::class, 'id', 'opr_gacha_id');
    }

    public function opr_stepup_gacha()
    {
        return $this->hasOneThrough(
            OprStepupGacha::class,
            OprGacha::class,
            'id', // opr_gachas.id
            'opr_gacha_id', // opr_stepup_gachas.opr_gacha_id
            'opr_gacha_id', // usr_gachas.opr_gacha_id
            'id' // opr_gachas.id
        );
    }
}
