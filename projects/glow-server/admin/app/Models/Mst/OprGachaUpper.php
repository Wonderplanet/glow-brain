<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Constants\GachaUpperType;
use App\Domain\Resource\Mst\Models\OprGachaUpper as BaseOprGachaUpper;

class OprGachaUpper extends BaseOprGachaUpper
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function getUpperType(): GachaUpperType
    {
        return GachaUpperType::from($this->upper_type->value);
    }

    public function getUpperTypeLabelAttribute(): string
    {
        return $this->getUpperType()->label();
    }
}
