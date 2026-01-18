<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Resource\Mst\Entities\OprGachaEntity;
use Carbon\CarbonImmutable;

readonly class GachaDrawRequest
{
    public function __construct(
        private CurrentUser $usr,
        private OprGachaEntity $oprGacha,
        private int $drewCount,
        private int $playNum,
        private CostType $costType,
        private ?string $costId,
        private int $costNum,
        private int $platform,
        private string $billingPlatform,
        private CarbonImmutable $now,
        private ?int $currentStepNumber = null,
    ) {
    }

    public function getUsr(): CurrentUser
    {
        return $this->usr;
    }

    public function getOprGacha(): OprGachaEntity
    {
        return $this->oprGacha;
    }

    public function getDrewCount(): int
    {
        return $this->drewCount;
    }

    public function getPlayNum(): int
    {
        return $this->playNum;
    }

    public function getCostType(): CostType
    {
        return $this->costType;
    }

    public function getCostId(): ?string
    {
        return $this->costId;
    }

    public function getCostNum(): int
    {
        return $this->costNum;
    }

    public function getPlatform(): int
    {
        return $this->platform;
    }

    public function getBillingPlatform(): string
    {
        return $this->billingPlatform;
    }

    public function getNow(): CarbonImmutable
    {
        return $this->now;
    }

    public function getCurrentStepNumber(): ?int
    {
        return $this->currentStepNumber;
    }
}
