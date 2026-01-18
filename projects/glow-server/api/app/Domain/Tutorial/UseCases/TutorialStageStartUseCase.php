<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Mst\Entities\MstTutorialEntity;
use App\Domain\Resource\Mst\Repositories\MstQuestRepository;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Resource\Mst\Repositories\MstTutorialRepository;
use App\Domain\Stage\Delegators\StageTutorialDelegator;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Tutorial\Services\TutorialStatusService;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\TutorialStageStartResultData;

class TutorialStageStartUseCase
{
    use UseCaseTrait;

    public function __construct(
        // MstRepository
        private MstTutorialRepository $mstTutorialRepository,
        private MstStageRepository $mstStageRepository,
        private MstQuestRepository $mstQuestRepository,
        // Service
        private TutorialStatusService $tutorialStatusService,
        // Common
        private Clock $clock,
        // Delegator
        private StageTutorialDelegator $stageTutorialDelegator,
        private UserDelegator $userDelegator,
    ) {
    }

    public function exec(
        CurrentUser $user,
        string $mstTutorialFunctionName,
        int $partyNo,
        int $platform
    ): TutorialStageStartResultData {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        /** @var MstTutorialEntity $mstTutorial */
        $mstTutorial = $this->mstTutorialRepository->getActiveByFunctionName(
            $mstTutorialFunctionName,
            $now,
            isThrowError: true,
        );
        $mstStageId = $mstTutorial->getConditionValue();

        $mstStage = $this->mstStageRepository->getByIdWithError($mstStageId);
        $mstQuest = $this->mstQuestRepository->getQuestPeriod($mstStage->getMstQuestId(), $now);
        if ($mstQuest->getQuestType() !== QuestType::TUTORIAL->value) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('QuestType is not tutorial. QuestType: %s', $mstQuest->getQuestType()),
            );
        }

        // チュートリアルステージを開始する
        $usrStageStatusData = $this->stageTutorialDelegator->startTutorialStageAndGetUsrStageStatusData(
            $usrUserId,
            $partyNo,
            $mstStage,
            $mstQuest,
            $now,
        );

        // ステージセッションは上書き可能で、tutorial/stage_startを何度も実行されることを想定する
        $currentTutorialStatus = $this->userDelegator->getTutorialStatus($usrUserId);
        if ($currentTutorialStatus !== $mstTutorialFunctionName) {
            $this->tutorialStatusService->updateTutorialStatus($usrUserId, $now, $mstTutorialFunctionName, $platform);
        }

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new TutorialStageStartResultData(
            $mstTutorialFunctionName,
            $usrStageStatusData,
        );
    }
}
