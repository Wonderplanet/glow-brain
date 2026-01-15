<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstUserLevelBonusGroupEntity
{
    public function __construct(
        private string $id,
        private string $mst_user_level_bonus_group_id,
        private string $resource_type,
        private ?string $resource_id,
        private int $resource_amount,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstUserLevelBonusGroupId(): string
    {
        return $this->mst_user_level_bonus_group_id;
    }

    public function getResourceType(): string
    {
        return $this->resource_type;
    }

    public function getResourceId(): ?string
    {
        return $this->resource_id;
    }

    public function getResourceAmount(): int
    {
        return $this->resource_amount;
    }
}
