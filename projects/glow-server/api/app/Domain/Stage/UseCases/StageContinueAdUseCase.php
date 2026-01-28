<?php

declare(strict_types=1);

namespace App\Domain\Stage\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Mst\Repositories\MstQuestRepository;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Stage\Services\StageContinueService;
use App\Domain\Stage\Services\StageSessionService;
use App\Http\Responses\Data\UsrStageStatusData;
use App\Http\Responses\ResultData\StageContinueAdResultData;

class StageContinueAdUseCase
{
    use UseCaseTrait;

    public function __construct(
        // MstRepository
        private MstStageRepository $mstStageRepository,
        private MstQuestRepository $mstQuestRepository,
        // Service
        private StageSessionService $stageSessionService,
        private StageContinueService $stageContinueService,
        // Common
        private Clock $clock,
    ) {
    }

    /**
     * 広告視聴でコンティニューする
     *
     * @param CurrentUser $user
     * @param string      $mstStageId
     * @return StageContinueAdResultData
     * @throws \Throwable
     */
    public function exec(
        CurrentUser $user,
        string $mstStageId,
    ): StageContinueAdResultData {
        $now = $this->clock->now();

        $mstStage = $this->mstStageRepository->getByIdWithError($mstStageId);
        // 期間が終了しているならコンティニューさせない
        $mstQuest = $this->mstQuestRepository->getQuestPeriod($mstStage->getMstQuestId(), $now);

        // スピードアタックの場合コンティニューさせない
        $this->stageContinueService->checkStageEventContinue(
            $mstQuest->getQuestTypeEnum(),
            $mstStageId,
            $now,
        );

        // ステージチェック
        $usrStageSession = $this->stageSessionService->getUsrStageSessionWithResetDaily($user->id, $now);
        $this->stageSessionService->validateStageStarted($usrStageSession, $mstStageId);
        $this->stageContinueService->checkStageContinueLimit($usrStageSession);
        $this->stageContinueService->checkDailyStageContinueAdLimit($usrStageSession);

        // コンティニュー処理
        $this->stageContinueService->continueAd($usrStageSession);

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new StageContinueAdResultData(
            new UsrStageStatusData($usrStageSession),
        );
    }
}
