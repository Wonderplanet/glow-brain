<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

readonly class EncyclopediaEffect
{
    public function __construct(
        private float $hpEffectPercentage = 0,
        private float $attackPowerEffectPercentage = 0,
        private float $healEffectPercentage = 0,
    ) {
    }

    public function getHpEffectPercentage(): float
    {
        return $this->hpEffectPercentage;
    }

    public function getAttackPowerEffectPercentage(): float
    {
        return $this->attackPowerEffectPercentage;
    }

    public function getHealEffectPercentage(): float
    {
        return $this->healEffectPercentage;
    }
}
