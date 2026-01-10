<?php

declare(strict_types=1);

namespace App\Domain\Pvp\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Pvp\Repositories\SysPvpSeasonRepository;
use App\Domain\Pvp\Repositories\UsrPvpRepository;
use App\Domain\Pvp\Services\PvpCacheService;
use App\Domain\Pvp\Services\PvpMatchingService;
use App\Domain\Pvp\Services\PvpRewardService;
use App\Domain\Pvp\Services\PvpService;
use App\Domain\Pvp\Services\PvpTopService;
use App\Domain\Resource\Mst\Repositories\MstPvpRepository;
use App\Http\Responses\Data\OpponentPvpStatusData;
use App\Http\Responses\Data\OpponentSelectStatusResponseData;
use App\Http\Responses\ResultData\PvpChangeOpponentResultData;

class PvpChangeOpponentUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        // repositories
        public readonly SysPvpSeasonRepository $sysPvpSeasonRepository,
        public readonly MstPvpRepository $mstPvpRepository,
        public readonly UsrPvpRepository $usrPvpRepository,
        // services
        public readonly PvpTopService $pvpTopService,
        public readonly PvpMatchingService $pvpMatchingService,
        public readonly PvpRewardService $pvpRewardService,
        public readonly PvpService $pvpService,
        public readonly PvpCacheService $pvpCacheService,
    ) {
    }

    public function exec(CurrentUser $user): PvpChangeOpponentResultData
    {
        return $this->applyUserTransactionChanges(function () use ($user) {
            $usrUserId = $user->id;
            $now = $this->clock->now();

            $sysPvpSeasonId = $this->pvpService->getCurrentSeasonId($now);

            // シーズン期間のバリデーション
            $sysPvpSeason = $this->sysPvpSeasonRepository->getById($sysPvpSeasonId);
            if (is_null($sysPvpSeason) || !$sysPvpSeason->isInSeason($now)) {
                throw new GameException(
                    ErrorCode::PVP_SEASON_PERIOD_OUTSIDE,
                    "PVP season is not active. sys_pvp_season_id: {$sysPvpSeasonId}"
                );
            }

            // 事前にpvp/topを呼び出してusrPvpは生成されている前提なのでnullを許容しない
            $usrPvp = $this->usrPvpRepository->getBySysPvpSeasonId(
                $usrUserId,
                $sysPvpSeasonId,
                true
            );

            $opponentPvpStatuses = $this->pvpMatchingService->getMatchingOpponentSelectStatusDatas(
                $usrUserId,
                $sysPvpSeasonId,
                $now
            );
            $opponentSelectStatusResponses = $opponentPvpStatuses->map(
                function (OpponentPvpStatusData $opponentPvpStatus): OpponentSelectStatusResponseData {
                    $opponentSelectStatus = $opponentPvpStatus->getPvpUserProfile();
                    return new OpponentSelectStatusResponseData(
                        $opponentSelectStatus->getMyId(),
                        $opponentSelectStatus->getName(),
                        $opponentSelectStatus->getMstUnitId(),
                        $opponentSelectStatus->getMstEmblemId(),
                        $opponentSelectStatus->getScore(),
                        $opponentPvpStatus,
                        $opponentSelectStatus->getWinAddPoint(),
                    );
                }
            );

            // 選出した対戦相手をDBに保存
            $usrPvp->setSelectedOpponentCandidates($opponentPvpStatuses);
            $this->usrPvpRepository->syncModel($usrPvp);

            return new PvpChangeOpponentResultData(
                $opponentSelectStatusResponses
            );
        });
    }
}
