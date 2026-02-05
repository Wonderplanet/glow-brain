<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class OprStepUpGachaStepRewardEntity
{
    public function __construct(
        private int $id,
        private int $releaseKey,
        private string $oprGachaId,
        private int $stepNumber,
        private ?int $loopCountTarget,
        private string $resourceType,
        private ?string $resourceId,
        private int $resourceAmount,
    ) {
    }

    public function getId(): int
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
