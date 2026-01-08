<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Entities;

class MngMessageRewardEntity
{
    public function __construct(
        private string $id,
        private string $mngMessageId,
        private string $resourceType,
        private ?string $resourceId,
        private ?int $resourceAmount,
        private int $displayOrder,
    ) {
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    public function getResourceAmount(): ?int
    {
        return $this->resourceAmount;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMngMessageId(): string
    {
        return $this->mngMessageId;
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }
}
