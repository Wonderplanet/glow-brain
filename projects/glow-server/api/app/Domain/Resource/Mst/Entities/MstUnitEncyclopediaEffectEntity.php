<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstUnitEncyclopediaEffectEntity
{
    public function __construct(
        private string $id,
        private string $mstUnitEncyclopediaRewardId,
        private string $effectType,
        private float $value,
        private int $releaseKey,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstUnitEncyclopediaRewardId(): string
    {
        return $this->mstUnitEncyclopediaRewardId;
    }

    public function getEffectType(): string
    {
        return $this->effectType;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }
}
