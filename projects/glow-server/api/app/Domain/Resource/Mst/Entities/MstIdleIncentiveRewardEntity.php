<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstIdleIncentiveRewardEntity
{
    /**
     * @param numeric-string $baseCoinAmount
     * @param numeric-string $baseExpAmount
     */
    public function __construct(
        private string $id,
        private string $mstStageId,
        private string $baseCoinAmount,
        private string $baseExpAmount,
        private string $mstIdleIncentiveItemGroupId,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstStageId(): string
    {
        return $this->mstStageId;
    }

    /**
     * @return numeric-string
     */
    public function getBaseCoinAmount(): string
    {
        return $this->baseCoinAmount;
    }

    /**
     * @return numeric-string
     */
    public function getBaseExpAmount(): string
    {
        return $this->baseExpAmount;
    }

    public function getMstIdleIncentiveItemGroupId(): string
    {
        return $this->mstIdleIncentiveItemGroupId;
    }
}
