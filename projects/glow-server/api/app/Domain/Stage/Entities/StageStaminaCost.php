<?php

declare(strict_types=1);

namespace App\Domain\Stage\Entities;

class StageStaminaCost
{
    /**
     * @param int $baseCost
     * @param int $staminaCost
     * @param int $lapStaminaCost
     * @param float $staminaCostCampaignMultiplier
     * @param int $lapCount
     */
    public function __construct(
        private int $baseCost,
        private int $staminaCost,
        private int $lapStaminaCost,
        private float $staminaCostCampaignMultiplier,
        private int $lapCount,
    ) {
    }

    public function getBaseCost(): int
    {
        return $this->baseCost;
    }

    public function getStaminaCost(): int
    {
        return $this->staminaCost;
    }

    public function getLapStaminaCost(): int
    {
        return $this->lapStaminaCost;
    }

    public function getStaminaCostCampaignMultiplier(): float
    {
        return $this->staminaCostCampaignMultiplier;
    }

    public function getLapCount(): int
    {
        return $this->lapCount;
    }
}
