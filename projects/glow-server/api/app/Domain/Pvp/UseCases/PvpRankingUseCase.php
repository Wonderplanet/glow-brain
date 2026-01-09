<?php

declare(strict_types=1);

namespace App\Domain\Pvp\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Pvp\Services\PvpRankingService;
use App\Domain\Pvp\Services\PvpService;
use App\Http\Responses\ResultData\PvpRankingResultData;

readonly class PvpRankingUseCase
{
    use UseCaseTrait;

    public function __construct(
        private PvpService $pvpService,
        private PvpRankingService $pvpRankingService,
        private Clock $clock,
    ) {
    }

    public function exec(CurrentUser $user, bool $isPreviousSeason): PvpRankingResultData
    {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        if ($isPreviousSeason) {
            $sysPvpSeason = $this->pvpService->getPreviousSysPvpSeason($now, false);
        } else {
            $sysPvpSeason = $this->pvpService->getCurrentSysPvpSeason($now, false);
        }

        $result = $this->pvpRankingService->getRanking(
            $usrUserId,
            $sysPvpSeason,
            $now,
        );
        $this->processWithoutUserTransactionChanges();

        return new PvpRankingResultData($result);
    }
}
