<?php

declare(strict_types=1);

namespace App\Domain\Unit\Delegators;

use App\Domain\Resource\Usr\Entities\UsrUnitEntity;
use App\Domain\Unit\Services\UnitLevelUpService;
use Carbon\CarbonImmutable;

class UnitTutorialDelegator
{
    public function __construct(
        private UnitLevelUpService $unitLevelUpService,
    ) {
    }

    public function levelUp(string $usrUserId, string $usrUnitId, int $level, CarbonImmutable $now): ?UsrUnitEntity
    {
        return $this->unitLevelUpService->levelUp($usrUserId, $usrUnitId, $level, $now)?->toEntity();
    }
}
