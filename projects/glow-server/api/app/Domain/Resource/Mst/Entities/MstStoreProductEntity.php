<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use WonderPlanet\Domain\Currency\Traits\Mst\MstStoreProductTrait as WpCurrencyMstStoreProductTrait;

readonly class MstStoreProductEntity
{
    use WpCurrencyMstStoreProductTrait;

    public function __construct(
        private readonly string $id,
        private readonly int $releaseKey,
        private readonly string $productIdIos,
        private readonly string $productIdAndroid,
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

    public function getProductIdIos(): string
    {
        return $this->productIdIos;
    }

    public function getProductIdAndroid(): string
    {
        return $this->productIdAndroid;
    }
}
