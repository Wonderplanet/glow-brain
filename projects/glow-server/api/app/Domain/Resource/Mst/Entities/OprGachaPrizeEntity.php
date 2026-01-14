<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Resource\Enums\RewardType;

class OprGachaPrizeEntity
{
    public function __construct(
        protected string $id,
        protected string $groupId,
        protected RewardType $resourceType,
        protected ?string $resourceId,
        protected int $resourceAmount,
        protected int $weight,
        protected bool $pickup,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function getResourceType(): RewardType
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

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getPickup(): bool
    {
        return $this->pickup;
    }

    public function isUnit(): bool
    {
        return $this->resourceType === RewardType::UNIT;
    }

    public function isItem(): bool
    {
        return $this->resourceType === RewardType::ITEM;
    }
}
