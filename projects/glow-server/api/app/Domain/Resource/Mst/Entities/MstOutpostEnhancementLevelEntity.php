<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstOutpostEnhancementLevelEntity
{
    public function __construct(
        private string $id,
        private string $mstOutpostEnhancementId,
        private int $level,
        private int $costCoin,
        private float $enhanceValue,
        private int $releaseKey,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstOutpostEnhancementId(): string
    {
        return $this->mstOutpostEnhancementId;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getCostCoin(): int
    {
        return $this->costCoin;
    }

    public function getEnhanceValue(): float
    {
        return $this->enhanceValue;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }
}
