<?php

declare(strict_types=1);

namespace App\Domain\DailyBonus\Delegators;

use App\Domain\DailyBonus\Services\ComebackBonusFetchService;
use App\Domain\DailyBonus\Services\ComebackBonusUpdateService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DailyBonusDelegator
{
    public function __construct(
        private ComebackBonusFetchService $comebackBonusFetchService,
        private ComebackBonusUpdateService $comebackBonusUpdateService,
    ) {
    }

    /**
     * @param string $usrUserId
     * @param int $platform
     * @param CarbonImmutable $now
     * @return void
     */
    public function updateComebackBonusStatuses(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
        int $comebackDayCount,
    ): void {
        $this->comebackBonusUpdateService->updateStatuses(
            $usrUserId,
            $platform,
            $now,
            $comebackDayCount,
        );
    }

    /**
     * @param string $usrUserId
     * @param CarbonImmutable $now
     */
    public function fetchComebackBonusProgresses(
        string $usrUserId,
        CarbonImmutable $now,
    ): Collection {
        return $this->comebackBonusFetchService->fetchProgresses(
            $usrUserId,
            $now,
        );
    }
}
