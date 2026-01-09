<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstShopPassRewardEntity
{
    public function __construct(
        private string $id,
        private string $mst_shop_pass_id,
        private string $pass_reward_type,
        private string $resource_type,
        private ?string $resource_id,
        private int $resource_amount,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstShopPassId(): string
    {
        return $this->mst_shop_pass_id;
    }

    public function getPassRewardType(): string
    {
        return $this->pass_reward_type;
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
