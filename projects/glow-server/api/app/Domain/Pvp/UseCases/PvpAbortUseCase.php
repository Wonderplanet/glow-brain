<?php

declare(strict_types=1);

namespace App\Domain\Pvp\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Pvp\Enums\PvpMatchingType;
use App\Domain\Pvp\Repositories\SysPvpSeasonRepository;
use App\Domain\Pvp\Repositories\UsrPvpRepository;
use App\Domain\Pvp\Repositories\UsrPvpSessionRepository;
use App\Domain\Pvp\Services\PvpEndService;
use App\Domain\Pvp\Services\PvpLogService;
use App\Domain\Pvp\Services\PvpService;
use App\Domain\Resource\Mst\Repositories\MstPvpRepository;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Http\Responses\ResultData\PvpAbortResultData;

class PvpAbortUseCase
{
    use UseCaseTrait;

    public function __construct(
        private readonly UsrPvpSessionRepository $usrPvpSessionRepository,
        private readonly SysPvpSeasonRepository $sysPvpSeasonRepository,
        private readonly MstPvpRepository $mstPvpRepository,
        private readonly UsrPvpRepository $usrPvpRepository,
        private readonly PvpEndService $pvpEndService,
        private readonly PvpLogService $pvpLogService,
        private readonly PvpService $pvpService,
        private readonly Clock $clock,
        private UsrModelDiffGetService $usrModelDiffGetService,
    ) {
    }

    public function exec(CurrentUser $user): PvpAbortResultData
    {
        $usrUserId = $user->getId();
        $usrPvpSession = $this->usrPvpSessionRepository->findValidOneOrFail($usrUserId);
        $sysPvpSeason = $this->sysPvpSeasonRepository->findWithError($usrPvpSession->getSysPvpSeasonId(), true);
        $mstPvp = $this->mstPvpRepository->getDefaultOrTargetById($sysPvpSeason->getId(), true);
        $usrPvp = $this->usrPvpRepository->getBySysPvpSeasonId($usrUserId, $sysPvpSeason->getId(), true);
        $inGameBattleLog = $this->pvpLogService->makeInGameBattleLogData([]);
        $now = $this->clock->now();

        if ($sysPvpSeason->isInSeason($now)) {
            $this->pvpEndService->validateCanEnd(
                $usrPvpSession,
                $sysPvpSeason,
                $now,
            );

            $this->pvpEndService->end(
                $mstPvp,
                $usrPvp,
                $usrPvpSession,
                $inGameBattleLog,
                false, // 強制敗北
                PvpMatchingType::Unavailable,
                $now
            );
        } else {
            // 期間外の場合はセッションを終了し正常レスポンス
            $this->pvpEndService->abortSession($usrPvpSession);
        }

        $usrPvpStatus = $this->pvpService->makeUsrPvpStatusData($usrPvp, $mstPvp, $now, true);
        $this->applyUserTransactionChanges();
        return new PvpAbortResultData(
            usrPvpStatus: $usrPvpStatus,
            usrItems: $this->usrModelDiffGetService->getChangedUsrItems(),
        );
    }
}
