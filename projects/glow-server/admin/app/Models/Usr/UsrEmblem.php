<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Emblem\Models\UsrEmblem as BaseUsrEmblem;
use App\Models\Mst\MstEmblem;

class UsrEmblem extends BaseUsrEmblem
{
    protected $connection = Database::TIDB_CONNECTION;

    public function mst_emblem()
    {
        return $this->belongsTo(MstEmblem::class, 'mst_emblem_id', 'id');
    }
}
