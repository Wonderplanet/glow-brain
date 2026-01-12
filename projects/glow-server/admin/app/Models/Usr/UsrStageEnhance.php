<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Stage\Models\UsrStageEnhance as BaseUsrStageEnhance;
use App\Models\Mst\MstStage;

class UsrStageEnhance extends BaseUsrStageEnhance
{
    protected $connection = Database::TIDB_CONNECTION;

    public $timestamps = true;

    public function mst_stage()
    {
        return $this->hasOne(MstStage::class, 'id', 'mst_stage_id');
    }
}
