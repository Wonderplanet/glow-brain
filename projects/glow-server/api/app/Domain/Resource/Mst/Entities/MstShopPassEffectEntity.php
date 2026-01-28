<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstShopPassEffectEntity
{
    public function __construct(
        private string $id,
        private string $mst_shop_pass_id,
        private string $effect_type,
        private int $effect_value,
        private int $release_key
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

    public function getEffectType(): string
    {
        return $this->effect_type;
    }

    public function getEffectValue(): int
    {
        return $this->effect_value;
    }

    public function getReleaseKey(): int
    {
        return $this->release_key;
    }
}
