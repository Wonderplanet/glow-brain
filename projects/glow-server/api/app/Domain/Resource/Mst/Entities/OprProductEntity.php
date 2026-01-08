<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class OprProductEntity
{
    public function __construct(
        private string $id,
        private string $mst_store_product_id,
        private string $productType,
        private ?int $purchasableCount,
        private int $paidAmount,
        private int $displayPriority,
        private string $start_date,
        private string $end_date,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstStoreProductId(): string
    {
        return $this->mst_store_product_id;
    }

    public function getProductType(): string
    {
        return $this->productType;
    }

    public function getPurchasableCount(): ?int
    {
        return $this->purchasableCount;
    }

    public function getPaidAmount(): int
    {
        return $this->paidAmount;
    }

    public function getDisplayPriority(): int
    {
        return $this->displayPriority;
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
