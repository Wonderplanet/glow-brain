<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstStageEnhanceRewardParamEntity
{
    public function __construct(
        private string $id,
        private int $minThresholdScore,
        private int $coinRewardAmount,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMinThresholdScore(): int
    {
        return $this->minThresholdScore;
    }

    public function getCoinRewardAmount(): int
    {
        return $this->coinRewardAmount;
    }
}
