<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities\Contracts;

use App\Domain\DailyBonus\Enums\DailyBonusType;

interface MstDailyBonusEntityInterface
{
    public function getId(): string;

    public function getMstScheduleId(): string;

    public function getDailyBonusType(): DailyBonusType;

    public function getLoginDayCount(): int;

    public function getMstDailyBonusRewardGroupId(): string;

    public function getSortOrder(): int;
}
