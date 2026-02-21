<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\BoxGacha\Models\LogBoxGachaAction as BaseLogBoxGachaAction;
use App\Models\Mst\MstBoxGacha;
use App\Traits\AthenaModelTrait;

class LogBoxGachaAction extends BaseLogBoxGachaAction implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;

    public function mst_box_gacha()
    {
        return $this->hasOne(MstBoxGacha::class, 'id', 'mst_box_gacha_id');
    }
}
