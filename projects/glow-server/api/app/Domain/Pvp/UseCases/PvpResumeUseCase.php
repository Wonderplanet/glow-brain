<?php

declare(strict_types=1);

namespace App\Domain\Pvp\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Pvp\Repositories\UsrPvpSessionRepository;
use App\Domain\Pvp\Services\PvpService;
use App\Domain\Pvp\Services\PvpStartService;
use App\Http\Responses\Data\OpponentSelectStatusResponseData;
use App\Http\Responses\ResultData\PvpResumeResultData;

class PvpResumeUseCase
{
    use UseCaseTrait;

    public function __construct(
        private readonly UsrPvpSessionRepository $usrPvpSessionRepository,
        private readonly PvpService $pvpService,
        private readonly PvpStartService $pvpStartService,
        private readonly Clock $clock,
    ) {
    }

    public function exec(CurrentUser $user): PvpResumeResultData
    {
        $now = $this->clock->now();
        $usrUserId = $user->getId();
        $usrPvpSession = $this->usrPvpSessionRepository->findValidOneOrFail($usrUserId);
        $currentSysPvpSeason = $this->pvpService->getCurrentSysPvpSeason($now);

        $this->pvpStartService->validateCanResume($usrPvpSession, $currentSysPvpSeason);
        $this->applyUserTransactionChanges();

        $opponentPvpStatusArr = $usrPvpSession->getOpponentPvpStatusToArray();
        $opponentPvpStatus = $this->pvpService->convertJsonToOpponentPvpStatus($opponentPvpStatusArr);

        $opponentSelectStatus = $opponentPvpStatus->getPvpUserProfile();
        $opponentSelectStatusResponse = new OpponentSelectStatusResponseData(
            $opponentSelectStatus->getMyId(),
            $opponentSelectStatus->getName(),
            $opponentSelectStatus->getMstUnitId(),
            $opponentSelectStatus->getMstEmblemId(),
            $opponentSelectStatus->getScore(),
            $opponentPvpStatus,
            $opponentSelectStatus->getWinAddPoint(),
        );

        return new PvpResumeResultData($opponentPvpStatus, $opponentSelectStatusResponse);
    }
}
