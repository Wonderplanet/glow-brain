<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancement as BaseMstOutpostEnhancement;

class MstOutpostEnhancement extends BaseMstOutpostEnhancement
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_outpost_enhancement_i18n()
    {
        return $this->hasOne(MstOutpostEnhancementI18n::class, 'mst_outpost_enhancement_id', 'id');
    }

    public function mst_outpost_enhancement_level()
    {
        return $this->hasMany(MstOutpostEnhancementLevel::class, 'mst_outpost_enhancement_id', 'id');
    }
}
