<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Gacha\Models\UsrGacha as BaseUsrGacha;
use App\Models\Mst\OprGacha;

class UsrGacha extends BaseUsrGacha
{
    protected $connection = Database::TIDB_CONNECTION;

    public function opr_gacha()
    {
        return $this->hasOne(OprGacha::class, 'id', 'opr_gacha_id');
    }
}
