<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\Outpost\Models\LogOutpostEnhancement as BaseLogOutpostEnhancement;
use App\Models\Mst\MstOutpostEnhancement;
use App\Traits\AthenaModelTrait;

class LogOutpostEnhancement extends BaseLogOutpostEnhancement implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;

    public function mst_outpost_enhancement()
    {
        return $this->hasOne(MstOutpostEnhancement::class, 'id', 'mst_outpost_enhancement_id');
    }
}
