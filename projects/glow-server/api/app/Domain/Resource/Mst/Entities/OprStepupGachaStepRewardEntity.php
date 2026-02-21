<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class OprStepupGachaStepRewardEntity
{
    public function __construct(
        private string $id,
        private int $releaseKey,
        private string $oprGachaId,
        private int $stepNumber,
        private ?int $loopCountTarget,
        private string $resourceType,
        private ?string $resourceId,
        private int $resourceAmount,
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

    public function getOprGachaId(): string
    {
        return $this->oprGachaId;
    }

    public function getStepNumber(): int
    {
        return $this->stepNumber;
    }

    public function getLoopCountTarget(): ?int
    {
        return $this->loopCountTarget;
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
}
