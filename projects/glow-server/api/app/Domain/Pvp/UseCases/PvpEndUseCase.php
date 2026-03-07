<?php

declare(strict_types=1);

namespace App\Domain\Pvp\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Pvp\Repositories\UsrPvpRepository;
use App\Domain\Pvp\Repositories\UsrPvpSessionRepository;
use App\Domain\Pvp\Services\PvpEndCheatService;
use App\Domain\Pvp\Services\PvpEndService;
use App\Domain\Pvp\Services\PvpLogService;
use App\Domain\Pvp\Services\PvpService;
use App\Domain\Resource\Entities\Rewards\PvpTotalScoreReward;
use App\Domain\Resource\Mst\Repositories\MstPvpRepository;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\PvpEndResultData;

class PvpEndUseCase
{
    use UseCaseTrait;

    public function __construct(
        // repositories
        private readonly UsrPvpSessionRepository $usrPvpSessionRepository,
        private readonly UsrPvpRepository $usrPvpRepository,
        private readonly MstPvpRepository $mstPvpRepository,
        // services
        private readonly PvpService $pvpService,
        private readonly PvpEndService $pvpEndService,
        private readonly PvpEndCheatService $pvpEndCheatService,
        private readonly PvpLogService $pvpLogService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        // delegators
        private readonly UserDelegator $userDelegator,
        private readonly RewardDelegator $rewardDelegator,
        private Clock $clock,
    ) {
    }

    /**
     * PVP終了時の処理を実行
     * @param CurrentUser $user
     * @param int $platform
     * @param string $sysPvpSeasonId
     * @param array<mixed> $inGameBattleLogArray
     * @param bool $isWin
     * @return PvpEndResultData
     * @throws GameException
     */
    public function exec(
        CurrentUser $user,
        int $platform,
        string $sysPvpSeasonId,
        array $inGameBattleLogArray = [],
        bool $isWin = false,
    ): PvpEndResultData {
        $now = $this->clock->now();
        $usrUserId = $user->id;
        $sysPvpSeason = $this->pvpService->getCurrentSysPvpSeason($now);
        $currentSeasonId = $sysPvpSeason->getId();

        if ($sysPvpSeasonId !== $currentSeasonId) {
            throw new GameException(
                ErrorCode::PVP_SEASON_PERIOD_OUTSIDE,
                'sys_pvp_season is mismatch with current season. Expected: '
                    . $currentSeasonId . ', Received: ' . $sysPvpSeasonId,
            );
        }

        $usrPvpSession = $this->usrPvpSessionRepository->findValidOneOrFail($usrUserId);
        if ($usrPvpSession->getSysPvpSeasonId() !== $currentSeasonId) {
            throw new GameException(
                ErrorCode::PVP_SESSION_NOT_FOUND,
                'PVP session not found or does not match the current season.',
            );
        }

        $inGameBattleLog = $this->pvpLogService->makeInGameBattleLogData($inGameBattleLogArray);
        $mstPvp = $this->mstPvpRepository->getDefaultOrTargetById($sysPvpSeason->getId(), true);
        $usrPvp = $this->usrPvpRepository->getBySysPvpSeasonId($usrUserId, $sysPvpSeason->getId(), true);
        $opponentPvpStatus = $this->pvpService->convertJsonToOpponentPvpStatus(
            $usrPvpSession->getOpponentPvpStatusToArray()
        );

        $this->pvpEndService->validateCanEnd(
            $usrPvpSession,
            $sysPvpSeason,
            $now,
        );

        $this->pvpEndCheatService->checkCheat(
            $inGameBattleLog,
            $usrPvp,
            $sysPvpSeason,
            $now,
            $usrPvpSession->getPartyNo(),
            $usrPvpSession->calcBattleTime($now),
            $opponentPvpStatus->getPvpUnits(),
            $opponentPvpStatus->getUsrEncyclopediaEffects()->map(fn($effect) => $effect->getMstEncyclopediaEffectId()),
        );

        $pvpResultPoints = $this->pvpEndService->end(
            $mstPvp,
            $usrPvp,
            $usrPvpSession,
            $inGameBattleLog,
            $isWin,
            $opponentPvpStatus->getPvpUserProfile()->getMatchingType(),
            $now
        );

        $usrPvpStatus = $this->pvpService->makeUsrPvpStatusData($usrPvp, $mstPvp, $now, true);
        $this->applyUserTransactionChanges(function () use ($usrUserId, $platform, $now) {
            // 報酬配布実行
            $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);
        });
        return new PvpEndResultData(
            usrPvpStatus: $usrPvpStatus,
            usrParameterData: $this->makeUsrParameterData(
                $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId)
            ),
            usrItems: $this->usrModelDiffGetService->getChangedUsrItems(),
            usrEmblems: $this->usrModelDiffGetService->getChangedUsrEmblems(),
            pvpResultPoints: $pvpResultPoints,
            pvpTotalScoreRewards: $this->rewardDelegator->getSentRewards(PvpTotalScoreReward::class),
        );
    }
}
