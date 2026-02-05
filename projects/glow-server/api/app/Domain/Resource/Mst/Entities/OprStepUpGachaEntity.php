<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class OprStepUpGachaEntity
{
    public function __construct(
        private string $id,
        private int $releaseKey,
        private string $oprGachaId,
        private int $maxStepNumber,
        private ?int $maxLoopCount,
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

    public function getMaxStepNumber(): int
    {
        return $this->maxStepNumber;
    }

    public function getMaxLoopCount(): ?int
    {
        return $this->maxLoopCount;
    }
}
