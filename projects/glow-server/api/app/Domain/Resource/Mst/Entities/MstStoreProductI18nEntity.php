<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

readonly class MstStoreProductI18nEntity
{
    public function __construct(
        private string $id,
        private string $mstStoreProductId,
        private string $language,
        private float $priceIos,
        private float $priceAndroid,
        private ?float $priceWebstore,
        private float $paidDiamondPriceIos,
        private float $paidDiamondPriceAndroid,
        private float $paidDiamondPriceWebstore,
        private int $releaseKey,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstStoreProductId(): string
    {
        return $this->mstStoreProductId;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getPriceIos(): float
    {
        return $this->priceIos;
    }

    public function getPriceAndroid(): float
    {
        return $this->priceAndroid;
    }

    public function getPriceWebstore(): ?float
    {
        return $this->priceWebstore;
    }

    public function getPaidDiamondPriceIos(): float
    {
        return $this->paidDiamondPriceIos;
    }

    public function getPaidDiamondPriceAndroid(): float
    {
        return $this->paidDiamondPriceAndroid;
    }

    public function getPaidDiamondPriceWebstore(): float
    {
        return $this->paidDiamondPriceWebstore;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }
}
