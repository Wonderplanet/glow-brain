<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\BoxGacha\Enums\BoxGachaRewardType;

class MstBoxGachaPrizeEntity
{
    public function __construct(
        private string $id,
        private string $mstBoxGachaGroupId,
        private bool $isPickup,
        private string $resourceType,
        private ?string $resourceId,
        private int $resourceAmount,
        private int $stock,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstBoxGachaGroupId(): string
    {
        return $this->mstBoxGachaGroupId;
    }

    public function isPickup(): bool
    {
        return $this->isPickup;
    }

    public function getResourceType(): BoxGachaRewardType
    {
        return BoxGachaRewardType::from($this->resourceType);
    }

    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    public function getResourceAmount(): int
    {
        return $this->resourceAmount;
    }

    public function getStock(): int
    {
        return $this->stock;
    }
}
