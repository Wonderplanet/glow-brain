<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class OprCampaignI18nEntity
{
    public function __construct(
        private string $id,
        private string $opr_campaign_id,
        private string $language,
        private string $description,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOprCampaignId(): string
    {
        return $this->opr_campaign_id;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
