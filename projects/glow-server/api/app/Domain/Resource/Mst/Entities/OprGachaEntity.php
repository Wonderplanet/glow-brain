<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Gacha\Enums\AppearanceCondition;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Enums\GachaUpper;
use Illuminate\Support\Carbon;

class OprGachaEntity
{
    public function __construct(
        private string $id,
        private GachaType $gachaType,
        private string $upperGroup,
        private bool $enableAdPlay,
        private bool $enableAddAdPlayUpper,
        private ?int $adPlayIntervalTime,
        private int $multiDrawCount,
        private ?int $multiFixedPrizeCount,
        private ?int $dailyPlayLimitCount,
        private ?int $totalPlayLimitCount,
        private ?int $dailyAdLimitCount,
        private ?int $totalAdLimitCount,
        private string $prizeGroupId,
        private ?string $fixedPrizeGroupId,
        private AppearanceCondition $appearanceCondition,
        private ?string $unlockConditionType,
        private ?int $unlockDurationHours,
        private ?Carbon $startAt,
        private ?Carbon $endAt,
        private ?string $displayMstUnitId
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getGachaType(): GachaType
    {
        return $this->gachaType;
    }

    public function getUpperGroup(): string
    {
        return $this->upperGroup;
    }

    public function getEnableAdPlay(): bool
    {
        return $this->enableAdPlay;
    }

    public function getEnableAddAdPlayUpper(): bool
    {
        return $this->enableAddAdPlayUpper;
    }

    public function getAdPlayIntervalTime(): ?int
    {
        return $this->adPlayIntervalTime;
    }

    public function getMultiDrawCount(): int
    {
        return $this->multiDrawCount;
    }

    public function getMultiFixedPrizeCount(): ?int
    {
        return $this->multiFixedPrizeCount;
    }

    public function getDailyPlayLimitCount(): ?int
    {
        return $this->dailyPlayLimitCount;
    }

    public function getTotalPlayLimitCount(): ?int
    {
        return $this->totalPlayLimitCount;
    }

    public function getDailyAdLimitCount(): ?int
    {
        return $this->dailyAdLimitCount;
    }

    public function getTotalAdLimitCount(): ?int
    {
        return $this->totalAdLimitCount;
    }

    public function getPrizeGroupId(): string
    {
        return $this->prizeGroupId;
    }

    public function getFixedPrizeGroupId(): ?string
    {
        return $this->fixedPrizeGroupId;
    }

    public function hasFixedPrizeGroup(): bool
    {
        return StringUtil::isSpecified($this->fixedPrizeGroupId);
    }

    public function getAppearanceCondition(): AppearanceCondition
    {
        return $this->appearanceCondition;
    }

    public function getUnlockConditionType(): string
    {
        return $this->unlockConditionType;
    }

    public function getUnlockDurationHours(): ?int
    {
        return $this->unlockDurationHours;
    }

    public function getStartAt(): ?Carbon
    {
        return $this->startAt;
    }

    public function getEndAt(): ?Carbon
    {
        return $this->endAt;
    }

    public function getDisplayMstUnitId(): ?string
    {
        return $this->displayMstUnitId;
    }

    public function hasUpper(): bool
    {
        return $this->upperGroup !== GachaUpper::NONE->value;
    }

    public function isStepUp(): bool
    {
        return $this->gachaType === GachaType::STEPUP;
    }

    /**
     * 確定枠が存在する:true, 存在しない:false
     * @return bool
     */
    public function hasMultiFixedPrize(): bool
    {
        return $this->multiFixedPrizeCount !== null
            && $this->multiFixedPrizeCount >= 1
            && StringUtil::isSpecified($this->fixedPrizeGroupId);
    }
}
