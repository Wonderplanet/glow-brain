<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstIdleIncentiveItemEntity
{
    /**
     * @param numeric-string $baseAmount
     */
    public function __construct(
        private string $id,
        private string $mstIdleIncentiveItemGroupId,
        private string $mstItemId,
        private string $baseAmount,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstIdleIncentiveItemGroupId(): string
    {
        return $this->mstIdleIncentiveItemGroupId;
    }

    public function getMstItemId(): string
    {
        return $this->mstItemId;
    }

    /**
     * @return numeric-string
     */
    public function getBaseAmount(): string
    {
        return $this->baseAmount;
    }
}
