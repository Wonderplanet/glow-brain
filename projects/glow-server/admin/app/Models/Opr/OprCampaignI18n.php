<?php

namespace App\Models\Opr;

use App\Constants\Database;
use App\Constants\Language;
use App\Domain\Resource\Mst\Models\OprCampaignI18n as BaseOprCampaignI18n;

class OprCampaignI18n extends BaseOprCampaignI18n
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function getLanguageLabelAttribute(): string
    {
        $enum = Language::from($this->language);
        if ($enum === null) {
            return '';
        }

        return $enum->label();
    }
}
