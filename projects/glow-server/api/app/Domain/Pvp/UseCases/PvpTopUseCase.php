<?php

declare(strict_types=1);

namespace App\Domain\Pvp\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Message\Delegator\MessageDelegator;
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
use App\Http\Responses\ResultData\PvpTopResultData;

class PvpTopUseCase
{
    use UseCaseTrait;

    public function __construct(
        // repositories
        private readonly SysPvpSeasonRepository $sysPvpSeasonRepository,
        private readonly MstPvpRepository $mstPvpRepository,
        private readonly UsrPvpRepository $usrPvpRepository,
        // services
        private readonly PvpTopService $pvpTopService,
        private readonly PvpMatchingService $pvpMatchingService,
        private readonly PvpRewardService $pvpRewardService,
        private readonly PvpService $pvpService,
        private readonly PvpCacheService $pvpCacheService,
        private readonly Clock $clock,
        // Delegators
        private MessageDelegator $messageDelegator,
    ) {
    }

    public function exec(CurrentUser $user): PvpTopResultData
    {
        return $this->applyUserTransactionChanges(function () use ($user) {
            $usrUserId = $user->id;
            $now = $this->clock->now();

            $sysPvpSeasonId = $this->pvpService->getCurrentSeasonId($now);
            $mstPvp = $this->mstPvpRepository->getDefaultOrTargetById($sysPvpSeasonId, true);
            $sysPvpSeason = $this->sysPvpSeasonRepository->getCurrentOrCreate(
                $sysPvpSeasonId,
                $now
            );

            $pvpPreviousSeasonResultData = null;
            $usrPvp = $this->usrPvpRepository->getBySysPvpSeasonId(
                $usrUserId,
                $sysPvpSeason->getId(),
            );

            // 存在しない場合は新シーズンの為専用処理
            if (is_null($usrPvp)) {
                $lastPlayedUsrPvp = $this->pvpService->getLatestPlayedUsrPvp($usrUserId, $sysPvpSeason->getId());
                $pvpPreviousSeasonResultData = $this->pvpRewardService->getSeasonResult(
                    $usrUserId,
                    $sysPvpSeason->getId(),
                    $lastPlayedUsrPvp,
                );
                // 報酬未受取ならメッセージに追加して受取済みにする
                if (! $lastPlayedUsrPvp?->isSeasonRewardReceived()) {
                    $sendRewards = $pvpPreviousSeasonResultData?->rewards ?? collect();
                    foreach ($sendRewards as $reward) {
                        /** @var \App\Domain\Resource\Entities\Rewards\PvpReward $reward */
                        $this->messageDelegator->addNewSystemMessage(
                            $usrUserId,
                            $reward->getRewardGroupId(),
                            $now->addDays($reward->getExpirationDays()),
                            $reward,
                            $reward->getTitle(),
                            $reward->getBody(),
                        );
                    }
                    $this->pvpRewardService->markSeasonRewardAsReceived(collect([$lastPlayedUsrPvp]));
                }
                $usrPvp = $this->pvpTopService->generateUsrPvpForNewSeason(
                    $usrUserId,
                    $sysPvpSeason->getId(),
                    $mstPvp->getMaxDailyChallengeCount(),
                    $mstPvp->getMaxDailyItemChallengeCount(),
                    $lastPlayedUsrPvp,
                    $now
                );
            }

            // 今シーズンの情報を取得
            $pvpHeldStatusData = $this->pvpTopService->getPvpHeldStatus($sysPvpSeason);
            $usrPvpStatusData = $this->pvpService->makeUsrPvpStatusData($usrPvp, $mstPvp, $now, true);

            // 対戦相手を選出
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

            $usrPvp->setSelectedOpponentCandidates($opponentPvpStatuses);
            $this->usrPvpRepository->syncModel($usrPvp);

            $isViewableRanking = $this->pvpCacheService->isViewableRanking($sysPvpSeason->getId());

            return new PvpTopResultData(
                $pvpHeldStatusData,
                $usrPvpStatusData,
                $opponentSelectStatusResponses,
                $pvpPreviousSeasonResultData,
                $isViewableRanking,
            );
        });
    }
}
