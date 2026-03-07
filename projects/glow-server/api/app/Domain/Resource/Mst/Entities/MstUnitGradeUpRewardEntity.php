<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstUnitGradeUpRewardEntity
{
    public function __construct(
        private string $id,
        private string $mstUnitId,
        private int $gradeLevel,
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

    public function getMstUnitId(): string
    {
        return $this->mstUnitId;
    }

    public function getGradeLevel(): int
    {
        return $this->gradeLevel;
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
