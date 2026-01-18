<?php

declare(strict_types=1);

namespace App\Domain\Stage\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Mst\Repositories\MstQuestRepository;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Stage\Factories\QuestServiceFactory;
use App\Domain\Stage\Services\StageLogService;
use App\Domain\Stage\Services\StageSessionService;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\UsrStageStatusData;
use App\Http\Responses\ResultData\StageStartResultData;

class StageStartUseCase
{
    use UseCaseTrait;

    public function __construct(
        // Factory
        private QuestServiceFactory $questServiceFactory,
        // Repository
        private MstStageRepository $mstStageRepository,
        private MstQuestRepository $mstQuestRepository,
        // Delegator
        private UserDelegator $userDelegator,
        // Service
        private StageLogService $stageLogService,
        private StageSessionService $stageSessionService,
        // Other
        private Clock $clock,
    ) {
    }

    /**
     * ステージ開始処理
     *
     * @param CurrentUser $user
     * @param string $mstStageId
     * @param int $partyNo
     * @param bool $isChallengeAd
     * @param int $lapCount
     * @return StageStartResultData
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function exec(
        CurrentUser $user,
        string $mstStageId,
        int $partyNo,
        bool $isChallengeAd,
        int $lapCount,
    ): StageStartResultData {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        $mstStage = $this->mstStageRepository->getStagePeriod($mstStageId, $now);

        // ステージ開始時は猶予時間を考慮せずに、開催中のクエストを取得する
        $mstQuest = $this->mstQuestRepository->getQuestPeriod($mstStage->getMstQuestId(), $now);
        $questType = $mstQuest->getQuestType();

        $stageStartQuestService = $this->questServiceFactory->getStageStartQuestService($questType);
        $stageStartQuestService->start(
            $usrUserId,
            $partyNo,
            $mstStage,
            $mstQuest,
            $isChallengeAd,
            $lapCount,
            $now,
        );

        // ログ送信
        $this->stageLogService->sendStartLog(
            $usrUserId,
            $mstStageId,
            $partyNo,
            $now,
            $isChallengeAd,
            $lapCount,
        );

        $usrParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
        $usrStageSession = $this->stageSessionService->getUsrStageSessionWithResetDaily($usrUserId, $now);
        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new StageStartResultData(
            $this->makeUsrParameterData($usrParameter),
            new UsrStageStatusData($usrStageSession),
        );
    }
}
