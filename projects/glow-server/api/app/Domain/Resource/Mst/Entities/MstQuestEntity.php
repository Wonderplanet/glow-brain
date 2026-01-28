<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Stage\Enums\QuestType;

class MstQuestEntity
{
    public function __construct(
        private string $id,
        private string $questType,
        private ?string $mstEventId,
        private string $mstSeriesId,
        private int $sortOrder,
        private string $assetKey,
        private string $startDate,
        private string $endDate,
        private int $releaseKey,
        private ?string $questGroup,
        private string $difficulty,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getQuestType(): string
    {
        return $this->questType;
    }

    public function getQuestTypeEnum(): QuestType
    {
        return QuestType::from($this->questType);
    }

    public function getMstEventId(): ?string
    {
        return $this->mstEventId;
    }

    public function getMstSeriesId(): string
    {
        return $this->mstSeriesId;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getAssetKey(): string
    {
        return $this->assetKey;
    }

    public function getStartDate(): string
    {
        return $this->startDate;
    }

    public function getEndDate(): string
    {
        return $this->endDate;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }

    public function getQuestGroup(): string
    {
        return $this->questGroup;
    }

    public function getDifficulty(): string
    {
        return $this->difficulty;
    }
}
