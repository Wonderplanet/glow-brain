<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstMissionRewardEntity
{
    public function __construct(
        private string $id,
        private int $releaseKey,
        private string $groupId,
        private string $resourceType,
        private ?string $resourceId,
        private int $resourceAmount,
        private int $sortOrder,
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

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }
}
