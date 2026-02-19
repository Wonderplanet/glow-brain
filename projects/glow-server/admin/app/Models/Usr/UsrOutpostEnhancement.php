<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Outpost\Models\UsrOutpostEnhancement as BaseOutpostEnhancement;
use App\Models\Mst\MstOutpostEnhancement;

class UsrOutpostEnhancement extends BaseOutpostEnhancement
{
    protected $connection = Database::TIDB_CONNECTION;

    public function mst_outpost_enhancement()
    {
        return $this->hasOne(MstOutpostEnhancement::class, 'id', 'mst_outpost_enhancement_id');
    }
}
