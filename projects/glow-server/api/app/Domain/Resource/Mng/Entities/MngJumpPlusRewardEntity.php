<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Entities;

class MngJumpPlusRewardEntity
{
    public function __construct(
        private string $id,
        private string $groupId,
        private string $resourceType,
        private ?string $resourceId,
        private int $resourceAmount,
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
