<?php

declare(strict_types=1);

namespace App\Domain\Mission\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrMissionEventDailyBonusProgressInterface extends UsrModelInterface
{
    public function getMstMissionEventDailyBonusScheduleId(): string;

    public function getProgress(): int;

    public function getLatestUpdateAt(): ?string;

    public function incrementProgress(CarbonImmutable $now): void;
}
