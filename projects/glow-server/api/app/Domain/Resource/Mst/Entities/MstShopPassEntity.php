<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstShopPassEntity
{
    public function __construct(
        private string $id,
        private string $opr_product_id,
        private int $is_display_expiration,
        private int $pass_duration_days,
        private string $asset_key,
        private int $release_key
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOprProductId(): string
    {
        return $this->opr_product_id;
    }

    public function getIsDisplayExpiration(): int
    {
        return $this->is_display_expiration;
    }

    public function getPassDurationDays(): int
    {
        return $this->pass_duration_days;
    }

    public function getAssetKey(): string
    {
        return $this->asset_key;
    }

    public function getReleaseKey(): int
    {
        return $this->release_key;
    }
}
