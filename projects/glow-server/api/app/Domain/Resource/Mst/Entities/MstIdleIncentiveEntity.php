<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstIdleIncentiveEntity
{
    public function __construct(
        private string $id,
        private string $assetKey,
        private int $initialRewardReceiveMinutes,
        private int $rewardIncreaseIntervalMinutes,
        private int $maxIdleHours,
        private int $maxDailyDiamondQuickReceiveAmount,
        private int $requiredQuickReceiveDiamondAmount,
        private int $maxDailyAdQuickReceiveAmount,
        private int $adIntervalSeconds,
        private int $quickIdleMinutes,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAssetKey(): string
    {
        return $this->assetKey;
    }

    public function getInitialRewardReceiveMinutes(): int
    {
        return $this->initialRewardReceiveMinutes;
    }

    public function getRewardIncreaseIntervalMinutes(): int
    {
        return $this->rewardIncreaseIntervalMinutes;
    }

    public function getMaxIdleHours(): int
    {
        return $this->maxIdleHours;
    }

    public function getMaxDailyDiamondQuickReceiveAmount(): int
    {
        return $this->maxDailyDiamondQuickReceiveAmount;
    }

    public function getRequiredQuickReceiveDiamondAmount(): int
    {
        return $this->requiredQuickReceiveDiamondAmount;
    }

    public function getMaxDailyAdQuickReceiveAmount(): int
    {
        return $this->maxDailyAdQuickReceiveAmount;
    }

    public function getAdIntervalSeconds(): int
    {
        return $this->adIntervalSeconds;
    }

    public function getQuickIdleMinutes(): int
    {
        return $this->quickIdleMinutes;
    }
}
