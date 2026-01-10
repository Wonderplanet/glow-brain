<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Shop\Enums\ShopItemCostType;

class MstShopItemEntity
{
    public function __construct(
        private string $id,
        private string $shop_type,
        private string $cost_type,
        private ?int $cost_amount,
        private int $isFirstTimeFree,
        private ?int $tradable_count,
        private string $resource_type,
        private ?string $resource_id,
        private int $resource_amount,
        private string $start_date,
        private string $end_date,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getShopType(): string
    {
        return $this->shop_type;
    }

    public function getCostType(): string
    {
        return $this->cost_type;
    }

    public function isCostAd(): bool
    {
        return $this->cost_type === ShopItemCostType::AD->value;
    }

    public function getCostAmount(): ?int
    {
        return $this->cost_amount;
    }

    public function getIsFirstTimeFree(): int
    {
        return $this->isFirstTimeFree;
    }

    public function isFirstTimeFree(): bool
    {
        return (bool) $this->isFirstTimeFree;
    }

    public function getTradableCount(): ?int
    {
        return $this->tradable_count;
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

    public function getStartDate(): string
    {
        return $this->start_date;
    }

    public function getEndDate(): string
    {
        return $this->end_date;
    }
}
