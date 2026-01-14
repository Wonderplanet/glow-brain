<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

class ShopPassActiveEffect
{
    public function __construct(
        private readonly int $idleIncentiveRewardMultiplier,
        private readonly int $idleIncentiveAddQuickReceiveByDiamond,
        private readonly int $idleIncentiveAddQuickReceiveByAd,
        private readonly int $staminaAddRecoveryLimit
    ) {
    }

    public function getIdleIncentiveRewardMultiplier(): int
    {
        return $this->idleIncentiveRewardMultiplier;
    }

    public function getIdleIncentiveAddQuickReceiveByDiamond(): int
    {
        return $this->idleIncentiveAddQuickReceiveByDiamond;
    }

    public function getIdleIncentiveAddQuickReceiveByAd(): int
    {
        return $this->idleIncentiveAddQuickReceiveByAd;
    }

    public function getStaminaAddRecoveryLimit(): int
    {
        return $this->staminaAddRecoveryLimit;
    }
}
