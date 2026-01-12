<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstMissionEventI18nEntity
{
    public function __construct(
        private string $id,
        private string $mstMissionAchievementId,
        private string $language,
        private string $description,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstMissionAchievementId(): string
    {
        return $this->mstMissionAchievementId;
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
