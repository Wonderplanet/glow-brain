<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstMissionDailyI18nEntity
{
    public function __construct(
        private string $id,
        private string $mstMissionDailyId,
        private string $language,
        private string $description,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstMissionDailyId(): string
    {
        return $this->mstMissionDailyId;
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
