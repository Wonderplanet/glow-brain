<?php

declare(strict_types=1);

namespace App\Domain\Stage\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\Resource\Entities\CurrencyTriggers\StageContinueTrigger;
use App\Domain\Resource\Mst\Repositories\MstQuestRepository;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Stage\Services\StageContinueService;
use App\Domain\Stage\Services\StageSessionService;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\UsrStageStatusData;
use App\Http\Responses\ResultData\StageContinueResultData;

class StageContinueDiamondUseCase
{
    use UseCaseTrait;

    public function __construct(
        // MstRepository
        private MstStageRepository $mstStageRepository,
        private MstQuestRepository $mstQuestRepository,
        // Service
        private StageSessionService $stageSessionService,
        private StageContinueService $stageContinueService,
        // Resource
        private MstConfigService $mstConfigService,
        // Common
        private Clock $clock,
        // Delegator
        private UserDelegator $userDelegator,
        private AppCurrencyDelegator $appCurrencyDelegator,
    ) {
    }

    /**
     * 一次通貨でコンティニューする
     *
     * @param CurrentUser $user
     * @param int         $platform
     * @param string      $mstStageId
     * @param string      $billingPlatform
     * @return StageContinueResultData
     * @throws \Throwable
     */
    public function exec(
        CurrentUser $user,
        int $platform,
        string $mstStageId,
        string $billingPlatform
    ): StageContinueResultData {
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

        // コンティニュー処理
        $this->stageContinueService->continue($usrStageSession);

        // トランザクション処理
        $this->applyUserTransactionChanges(function () use ($user, $platform, $mstStageId, $billingPlatform) {
            $diamond = $this->mstConfigService->getStageContinueDiamondAmount();

            // ステージをコンテニューするトリガー
            $trigger = new StageContinueTrigger($mstStageId, $diamond);
            $this->appCurrencyDelegator->consumeDiamond($user->id, $diamond, $platform, $billingPlatform, $trigger);
        });

        return new StageContinueResultData(
            $this->makeUsrParameterData($this->userDelegator->getUsrUserParameterByUsrUserId($user->id)),
            new UsrStageStatusData($usrStageSession),
        );
    }
}
