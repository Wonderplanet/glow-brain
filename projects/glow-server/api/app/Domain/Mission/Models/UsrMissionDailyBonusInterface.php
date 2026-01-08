<?php

declare(strict_types=1);

namespace App\Domain\Mission\Models;

interface UsrMissionDailyBonusInterface extends UsrMissionInterface
{
    public function getMstMissionDailyBonusId(): string;
}
