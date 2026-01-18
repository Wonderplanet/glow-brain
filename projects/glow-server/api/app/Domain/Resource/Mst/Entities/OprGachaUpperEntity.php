<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Gacha\Enums\UpperType;

class OprGachaUpperEntity
{
    public function __construct(
        private string $id,
        private string $upperGroup,
        private UpperType $upperType,
        private int $count,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUpperGroup(): string
    {
        return $this->upperGroup;
    }

    public function getUpperType(): UpperType
    {
        return $this->upperType;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function isMaxRarity(): bool
    {
        return $this->upperType === UpperType::MAX_RARITY;
    }

    public function isPickup(): bool
    {
        return $this->upperType === UpperType::PICKUP;
    }
}
