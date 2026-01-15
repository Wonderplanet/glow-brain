<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Delegators;

use App\Domain\AdventBattle\Services\AdventBattleRankingService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

readonly class AdventBattleDelegator
{
    public function __construct(
        private AdventBattleRankingService $adventBattleRankingService,
    ) {
    }

    public function getReceivableRewards(string $usrUserId, CarbonImmutable $now): Collection
    {
        return $this->adventBattleRankingService->getReceivableRewards($usrUserId, $now);
    }
}
