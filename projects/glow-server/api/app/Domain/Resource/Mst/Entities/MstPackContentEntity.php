<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstPackContentEntity
{
    public function __construct(
        private string $id,
        private string $mst_pack_id,
        private string $resource_type,
        private ?string $resource_id,
        private int $resource_amount,
        private int $is_bonus,
        private int $display_order,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstPackId(): string
    {
        return $this->mst_pack_id;
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

    public function getIsBonus(): int
    {
        return $this->is_bonus;
    }

    public function getDisplayOrder(): int
    {
        return $this->display_order;
    }
}
