<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Resource\Mst\Entities\Contracts\IMstStageRewardEntity;
use App\Domain\Stage\Enums\StageRewardCategory;

class MstStageRewardEntity implements IMstStageRewardEntity
{
    public function __construct(
        private string $id,
        private string $mstStageId,
        private string $rewardCategory,
        private string $resourceType,
        private ?string $resourceId,
        private int $resourceAmount,
        private int $percentage,
        private int $releaseKey,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstStageId(): string
    {
        return $this->mstStageId;
    }

    public function getRewardCategory(): string
    {
        return $this->rewardCategory;
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    public function getResourceAmount(): int
    {
        return $this->resourceAmount;
    }

    public function getPercentage(): int
    {
        return $this->percentage;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }

    public function isFirstClear(): bool
    {
        return $this->rewardCategory === StageRewardCategory::FIRST_CLEAR->value;
    }

    public function isAlways(): bool
    {
        return $this->rewardCategory === StageRewardCategory::ALWAYS->value;
    }

    public function isRandom(): bool
    {
        return $this->rewardCategory === StageRewardCategory::RANDOM->value;
    }
}
