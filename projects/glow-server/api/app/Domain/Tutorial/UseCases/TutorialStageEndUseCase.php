<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\IdleIncentive\Delegators\IdleIncentiveDelegator;
use App\Domain\Resource\Entities\Rewards\StageFirstClearReward;
use App\Domain\Resource\Entities\Rewards\UserLevelUpReward;
use App\Domain\Resource\Mst\Entities\MstTutorialEntity;
use App\Domain\Resource\Mst\Repositories\MstQuestRepository;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Resource\Mst\Repositories\MstTutorialRepository;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Stage\Delegators\StageTutorialDelegator;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Tutorial\Services\TutorialStatusService;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\ResultData\TutorialStageEndResultData;

class TutorialStageEndUseCase
{
    use UseCaseTrait;

    public function __construct(
        // MstRepository
        private MstTutorialRepository $mstTutorialRepository,
        private MstStageRepository $mstStageRepository,
        private MstQuestRepository $mstQuestRepository,
        // Service
        private TutorialStatusService $tutorialStatusService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        // Common
        private Clock $clock,
        // Delegator
        private StageTutorialDelegator $stageTutorialDelegator,
        private UserDelegator $userDelegator,
        private RewardDelegator $rewardDelegator,
        private IdleIncentiveDelegator $idleIncentiveDelegator,
    ) {
    }

    public function exec(CurrentUser $user, string $mstTutorialFunctionName, int $platform): TutorialStageEndResultData
    {
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

        $beforeUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
        $beforeExp = $beforeUsrUserParameter->getExp();

        $this->stageTutorialDelegator->endTutorialStage($usrUserId, $mstStage);

        $this->tutorialStatusService->updateTutorialStatus($usrUserId, $now, $mstTutorialFunctionName, $platform);

        // 探索報酬が決まるステージIDを更新
        $this->idleIncentiveDelegator->updateRewardMstStageId($usrUserId, $mstStageId, $now);

        // トランザクション処理
        list(
            $afterUsrUserParameter,
        ) = $this->applyUserTransactionChanges(function () use ($usrUserId, $platform, $now) {
            // 報酬配布実行
            $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

            // 報酬受け取りでレベルが上っている可能性があるので再取得
            $afterUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);

            return [
                $afterUsrUserParameter,
            ];
        });

        $userLevelUpData = new UserLevelUpData(
            $beforeExp,
            $afterUsrUserParameter->getExp(),
            $this->rewardDelegator->getSentRewards(UserLevelUpReward::class),
        );

        // レスポンス用意
        return new TutorialStageEndResultData(
            $mstTutorialFunctionName,
            $this->makeUsrParameterData($this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId)),
            $this->rewardDelegator->getSentRewards(StageFirstClearReward::class),
            $userLevelUpData,
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $this->usrModelDiffGetService->getChangedUsrUnits(),
            $this->usrModelDiffGetService->getChangedUsrEmblems(),
        );
    }
}
