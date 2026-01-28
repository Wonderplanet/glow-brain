<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstOutpost as BaseMstOutpost;

class MstOutpost extends BaseMstOutpost
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_outpost_enhancement()
    {
        return $this->hasMany(MstOutpostEnhancement::class, 'mst_outpost_id', 'id');
    }
}
