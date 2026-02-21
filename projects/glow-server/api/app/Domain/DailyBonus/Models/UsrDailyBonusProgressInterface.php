<?php

declare(strict_types=1);

namespace App\Domain\DailyBonus\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrDailyBonusProgressInterface extends UsrModelInterface
{
    public function getMstScheduleId(): string;

    public function getProgress(): int;

    public function getLatestUpdateAt(): ?string;

    public function incrementProgress(CarbonImmutable $now): void;

    public function resetProgress(CarbonImmutable $now): void;
}
