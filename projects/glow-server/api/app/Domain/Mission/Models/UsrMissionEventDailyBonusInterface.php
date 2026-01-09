<?php

declare(strict_types=1);

namespace App\Domain\Mission\Models;

interface UsrMissionEventDailyBonusInterface extends UsrMissionInterface
{
    public function getMstMissionEventDailyBonusId(): string;
}
