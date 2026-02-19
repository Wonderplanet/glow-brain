<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Constants\GachaUpperType;
use App\Domain\Gacha\Models\UsrGachaUpper as BaseUsrGachaUpper;
use App\Models\Mst\OprGachaUpper;

class UsrGachaUpper extends BaseUsrGachaUpper
{
    protected $connection = Database::TIDB_CONNECTION;

    public function opr_gacha_upper()
    {
        return $this->hasOne(OprGachaUpper::class, 'upper_group', 'upper_group')
            ->whereColumn('upper_type', 'upper_type');
    }

    public function getUpperTypeLabelAttribute(): string
    {
        $enum = GachaUpperType::tryFrom($this->upper_type);
        return $enum?->label() ?? '';
    }
}
