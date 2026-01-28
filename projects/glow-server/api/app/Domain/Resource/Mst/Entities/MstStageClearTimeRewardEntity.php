<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstStageClearTimeRewardEntity
{
    public function __construct(
        private string $id,
        private string $mstStageId,
        private int $upperClearTimeMs,
        private string $resourceType,
        private ?string $resourceId,
        private int $resourceAmount,
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

    public function getUpperClearTimeMs(): int
    {
        return $this->upperClearTimeMs;
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

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }
}
