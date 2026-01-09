<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Unit\Models\Eloquent\UsrUnit as BaseUsrUnit;
use App\Models\Mst\MstUnit;

class UsrUnit extends BaseUsrUnit
{
    protected $connection = Database::TIDB_CONNECTION;

    public function mst_unit()
    {
        return $this->belongsTo(MstUnit::class, 'mst_unit_id', 'id');
    }
}
