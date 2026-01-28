<?php

declare(strict_types=1);

namespace App\Domain\DailyBonus\Models;

use Carbon\CarbonImmutable;

interface UsrComebackBonusProgressInterface extends UsrDailyBonusProgressInterface
{
    public function getStartCount(): int;

    public function getMstComebackBonusScheduleId(): string;

    public function getStartAt(): string;

    public function getEndAt(): string;

    public function resetTerm(CarbonImmutable $startAt, CarbonImmutable $endAt): void;
}
