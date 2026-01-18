<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstMissionBeginnerI18nEntity
{
    public function __construct(
        private string $id,
        private string $mstMissionBeginnerId,
        private string $language,
        private string $title,
        private string $description,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstMissionBeginnerId(): string
    {
        return $this->mstMissionBeginnerId;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
