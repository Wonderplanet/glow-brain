<?php

declare(strict_types=1);

namespace App\Domain\Stage\Delegators;

use App\Domain\Stage\Services\StageService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class StageDelegator
{
    public function __construct(
        private StageService $stageService,
    ) {
    }

    public function getClearedMstStageIds(string $usrUserId): Collection
    {
        return $this->stageService->getClearedMstStageIds($usrUserId);
    }

    public function resetStageEvent(string $usrUserId, CarbonImmutable $now): void
    {
        $this->stageService->resetStageEvent($usrUserId, $now);
    }
}
