<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\Emblem\Models\LogEmblem as BaseLogEmblem;
use App\Models\Mst\MstEmblem;
use App\Traits\AthenaModelTrait;

class LogEmblem extends BaseLogEmblem implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;

    public function mst_emblem() {
        return $this->hasOne(MstEmblem::class, 'id', 'mst_emblem_id');
    }
}
