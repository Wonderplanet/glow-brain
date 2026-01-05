<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancementLevel as BaseMstOutpostEnhancementLevel;

class MstOutpostEnhancementLevel extends BaseMstOutpostEnhancementLevel
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_outpost_enhancement_level_i18n()
    {
        return $this->hasOne(MstOutpostEnhancementLevelI18n::class, 'mst_outpost_enhancement_level_id', 'id');

    }
}
