<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstBoxGachaGroup as BaseMstBoxGachaGroup;

class MstBoxGachaGroup extends BaseMstBoxGachaGroup
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_box_gacha()
    {
        return $this->belongsTo(MstBoxGacha::class, 'mst_box_gacha_id', 'id');
    }

    public function mst_box_gacha_prizes()
    {
        return $this->hasMany(MstBoxGachaPrize::class, 'mst_box_gacha_group_id', 'id');
    }
}
