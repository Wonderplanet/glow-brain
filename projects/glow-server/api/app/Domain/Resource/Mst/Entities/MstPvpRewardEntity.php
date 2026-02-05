<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstPvpRewardEntity
{
    public function __construct(
        private string $id,
        private string $mstPvpRewardGroupId,
        private string $resourceType,
        private ?string $resourceId,
        private int $amount,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstPvpRewardGroupId(): string
    {
        return $this->mstPvpRewardGroupId;
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
        return $this->amount;
    }
}
