<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Constants\GachaUpperType;
use App\Domain\Resource\Mst\Models\OprGachaI18n as BaseOprGachaI18n;

class OprGachaI18n extends BaseOprGachaI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function getUpperDescription(GachaUpperType $upperType): string
    {
        switch ($upperType) {
            case GachaUpperType::MAX_RARITY:
                return $this->max_rarity_upper_description;
            case GachaUpperType::PICKUP:
                return $this->pickup_upper_description;
            default:
                return '';
        }
    }
}
