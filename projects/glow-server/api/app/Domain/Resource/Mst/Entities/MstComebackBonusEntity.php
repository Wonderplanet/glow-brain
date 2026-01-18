<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\DailyBonus\Enums\DailyBonusType;
use App\Domain\Resource\Mst\Entities\Contracts\MstDailyBonusEntityInterface;

class MstComebackBonusEntity implements MstDailyBonusEntityInterface
{
    public function __construct(
        private string $id,
        private string $mstComebackBonusScheduleId,
        private int $loginDayCount,
        private string $mstDailyBonusRewardGroupId,
        private int $sortOrder,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstScheduleId(): string
    {
        return $this->mstComebackBonusScheduleId;
    }

    public function getDailyBonusType(): DailyBonusType
    {
        return DailyBonusType::COMEBACK_BONUS;
    }

    public function getLoginDayCount(): int
    {
        return $this->loginDayCount;
    }

    public function getMstDailyBonusRewardGroupId(): string
    {
        return $this->mstDailyBonusRewardGroupId;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }
}
