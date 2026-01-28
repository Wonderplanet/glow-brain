<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstDummyOutpost as BaseMstDummyOutpost;

class MstDummyOutpost extends BaseMstDummyOutpost
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_outpost_enhancement()
    {
        return $this->hasOne(MstOutpostEnhancement::class, 'id', 'mst_outpost_enhancement_id');
    }
}
