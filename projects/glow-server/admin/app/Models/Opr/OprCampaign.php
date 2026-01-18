<?php

namespace App\Models\Opr;

use App\Constants\CampaignTargetIdType;
use App\Constants\CampaignTargetType;
use App\Constants\CampaignType;
use App\Constants\Database;
use App\Constants\QuestDifficulty;
use App\Domain\Resource\Mst\Models\OprCampaign as BaseOprCampaign;
use App\Domain\Resource\Mst\Models\OprCampaignI18n;

class OprCampaign extends BaseOprCampaign
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function opr_campaign_i18n()
    {
        // TODO: hasManyにして全言語データを取得する
        return $this->hasOne(OprCampaignI18n::class, 'opr_campaign_id', 'id');
    }

    public function getCampaignTypeLabelAttribute(): string
    {
        $enum = CampaignType::tryFrom($this->campaign_type);
        if ($enum === null) {
            return '';
        }

        return $enum->label();
    }

    public function getCampaignTargetTypeLabelAttribute(): string
    {
        $enum = CampaignTargetType::tryFrom($this->target_type);
        if ($enum === null) {
            return '';
        }

        return $enum->label();
    }

    public function getDifficultyLabelAttribute(): string
    {
        $enum = QuestDifficulty::tryFrom($this->difficulty);
        if ($enum === null) {
            return '';
        }

        return $enum->label();
    }

    public function getTargetIdTypeLabelAttribute(): string
    {
        $enum = CampaignTargetIdType::tryFrom($this->target_id_type);
        if ($enum === null) {
            return '';
        }

        return $enum->label();
    }

    public function isChallengeCountCampaign(): bool
    {
        return $this->campaign_type === CampaignType::CHALLENGE_COUNT->value;
    }
}
