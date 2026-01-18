<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Common\Enums\Language;

class OprGachaI18nEntity
{
    public function __construct(
        private string $id,
        private string $oprGachaId,
        private Language $language,
        private ?string $name,
        private ?string $description,
        private ?string $max_rarity_upper_description,
        private ?string $pickup_upper_description,
        private ?string $bannerUrl,
        private ?string $logoBannerUrl,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOprGachaId(): string
    {
        return $this->oprGachaId;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getMaxRarityUpperDescription(): ?string
    {
        return $this->max_rarity_upper_description;
    }

    public function getPickupUpperDescription(): ?string
    {
        return $this->pickup_upper_description;
    }

    public function getBannerUrl(): string
    {
        return $this->bannerUrl;
    }

    public function getLogoBannerUrl(): string
    {
        return $this->logoBannerUrl;
    }
}
