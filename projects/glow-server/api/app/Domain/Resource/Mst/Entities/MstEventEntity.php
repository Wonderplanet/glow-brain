<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstEventEntity
{
    public function __construct(
        private string $id,
        private string $mstSeriesId,
        private int $isDisplayedSeriesLogo,
        private int $isDisplayedJumpPlus,
        private string $startAt,
        private string $endAt,
        private string $assetKey,
        private int $releaseKey
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstSeriesId(): string
    {
        return $this->mstSeriesId;
    }

    public function getIsDisplayedSeriesLogo(): int
    {
        return $this->isDisplayedSeriesLogo;
    }

    public function getIsDisplayedJumpPlus(): int
    {
        return $this->isDisplayedJumpPlus;
    }

    public function getStartAt(): string
    {
        return $this->startAt;
    }

    public function getEndAt(): string
    {
        return $this->endAt;
    }

    public function getAssetKey(): string
    {
        return $this->assetKey;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }
}
