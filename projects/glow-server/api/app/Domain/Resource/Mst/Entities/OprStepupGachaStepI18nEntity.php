<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class OprStepupGachaStepI18nEntity
{
    public function __construct(
        private string $id,
        private int $releaseKey,
        private string $oprStepupGachaStepId,
        private string $language,
        private string $fixedPrizeDescription,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }

    public function getOprStepupGachaStepId(): string
    {
        return $this->oprStepupGachaStepId;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getFixedPrizeDescription(): string
    {
        return $this->fixedPrizeDescription;
    }
}
