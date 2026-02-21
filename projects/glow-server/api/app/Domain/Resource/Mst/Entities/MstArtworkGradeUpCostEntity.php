<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstArtworkGradeUpCostEntity
{
    public function __construct(
        private string $id,
        private string $mstArtworkGradeUpId,
        private string $resourceType,
        private ?string $resourceId,
        private int $resourceAmount,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstArtworkGradeUpId(): string
    {
        return $this->mstArtworkGradeUpId;
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
