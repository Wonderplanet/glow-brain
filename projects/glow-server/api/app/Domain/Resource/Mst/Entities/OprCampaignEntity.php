<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Campaign\Enums\CampaignTargetIdType;
use App\Domain\Campaign\Enums\CampaignType;
use App\Domain\Common\Utils\StringUtil;

class OprCampaignEntity
{
    public function __construct(
        private string $id,
        private string $campaign_type,
        private string $target_type,
        private string $difficulty,
        private string $target_id_type,
        private ?string $target_id,
        private int $effect_value,
        private string $start_at,
        private string $end_at,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCampaignType(): string
    {
        return $this->campaign_type;
    }

    public function getTargetType(): string
    {
        return $this->target_type;
    }

    public function getDifficulty(): string
    {
        return $this->difficulty;
    }

    public function getTargetIdType(): string
    {
        return $this->target_id_type;
    }

    public function getTargetId(): ?string
    {
        return $this->target_id;
    }

    public function getEffectValue(): int
    {
        return $this->effect_value;
    }

    public function getStaminaEffectValue(): float
    {
        return $this->effect_value / 100;
    }

    public function getExpEffectValue(): float
    {
        // collectionからgetしてnull safe演算子で呼び出しているのでIDE上では未使用扱いになっているが使用している
        return $this->effect_value / 100;
    }

    public function getArtworkFragmentEffectValue(): float
    {
        // collectionからgetしてnull safe演算子で呼び出しているのでIDE上では未使用扱いになっているが使用している
        return $this->effect_value / 100;
    }

    public function getItemDropEffectValue(): float
    {
        // collectionからgetしてnull safe演算子で呼び出しているのでIDE上では未使用扱いになっているが使用している
        return $this->effect_value / 100;
    }

    public function getCoinDropEffectValue(): float
    {
        // collectionからgetしてnull safe演算子で呼び出しているのでIDE上では未使用扱いになっているが使用している
        return $this->effect_value / 100;
    }

    public function getChallengeCountEffectValue(): int
    {
        // collectionからgetしてnull safe演算子で呼び出しているのでIDE上では未使用扱いになっているが使用している
        return $this->effect_value;
    }

    public function getStartAt(): string
    {
        return $this->start_at;
    }

    public function getEndAt(): string
    {
        return $this->end_at;
    }

    public function isTargetIdTypeQuest(): bool
    {
        return $this->target_id_type === CampaignTargetIdType::QUEST->value
            && StringUtil::isSpecified($this->target_id);
    }

    public function isTargetIdTypeSeries(): bool
    {
        return $this->target_id_type === CampaignTargetIdType::SERIES->value
            && StringUtil::isSpecified($this->target_id);
    }

    public function isStaminaCampaign(): bool
    {
        return $this->campaign_type === CampaignType::STAMINA->value;
    }

    public function isChallengeCountCampaign(): bool
    {
        return $this->campaign_type === CampaignType::CHALLENGE_COUNT->value;
    }
}
