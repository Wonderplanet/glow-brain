<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\BoxGacha\Models\UsrBoxGacha as BaseUsrBoxGacha;
use App\Models\Mst\MstBoxGacha;
use App\Models\Usr\UsrUser;

class UsrBoxGacha extends BaseUsrBoxGacha
{
    protected $connection = Database::TIDB_CONNECTION;

    public function mst_box_gacha()
    {
        return $this->hasOne(MstBoxGacha::class, 'id', 'mst_box_gacha_id');
    }

    public function usr_user()
    {
        return $this->hasOne(UsrUser::class, 'id', 'usr_user_id');
    }
}
