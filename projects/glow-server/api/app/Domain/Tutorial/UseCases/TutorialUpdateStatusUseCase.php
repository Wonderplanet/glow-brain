<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Resource\Entities\Rewards\MissionDailyBonusReward;
use App\Domain\Resource\Entities\Rewards\MissionEventDailyBonusReward;
use App\Domain\Resource\Entities\Rewards\UserLevelUpReward;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Shop\Delegators\ShopDelegator;
use App\Domain\Tutorial\Services\TutorialStatusService;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\ResultData\TutorialUpdateStatusResultData;

class TutorialUpdateStatusUseCase
{
    use UseCaseTrait;

    public function __construct(
        // Service
        private TutorialStatusService $tutorialStatusService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        // Delegator
        private MissionDelegator $missionDelegator,
        private RewardDelegator $rewardDelegator,
        private UserDelegator $userDelegator,
        private ShopDelegator $shopDelegator,
        // Common
        private Clock $clock,
    ) {
    }

    /**
     * チュートリアル状態更新処理
     *
     * @param CurrentUser $user
     * @param string $mstTutorialFunctionName
     * @param int $platform
     * @return TutorialUpdateStatusResultData
     */
    public function exec(
        CurrentUser $user,
        string $mstTutorialFunctionName,
        int $platform
    ): TutorialUpdateStatusResultData {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        $beforeUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
        $beforeLevel = $beforeUsrUserParameter->getLevel();
        $beforeExp = $beforeUsrUserParameter->getExp();

        // トランザクション処理
        list(
            $usrConditionPacks,
            $afterUsrUserParameter,
            $usrMissionEventDailyBonusProgresses,
        ) = $this->applyUserTransactionChanges(function () use (
            $usrUserId,
            $now,
            $mstTutorialFunctionName,
            $platform,
            $beforeLevel,
        ) {
            $this->tutorialStatusService->updateTutorialStatus($usrUserId, $now, $mstTutorialFunctionName, $platform);

            // 報酬受け取りでレベルが上っている可能性があるので再取得
            $afterUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);

            $usrConditionPacks = collect();
            if ($beforeLevel < $afterUsrUserParameter->getLevel()) {
                // レベルアップパックの開放
                $usrConditionPacks = $this->shopDelegator->releaseUserLevelPack(
                    $usrUserId,
                    $afterUsrUserParameter->getLevel(),
                    $now
                );
            }

            // ミッションイベントデイリーボーナス進捗を取得
            $usrMissionEventDailyBonusProgresses = $this->missionDelegator->fetchEventDailyBonusProgresses(
                $usrUserId,
                $now,
            );

            return [
                $usrConditionPacks,
                $afterUsrUserParameter,
                $usrMissionEventDailyBonusProgresses,
            ];
        });

        $userLevelUpData = new UserLevelUpData(
            $beforeExp,
            $afterUsrUserParameter->getExp(),
            $this->rewardDelegator->getSentRewards(UserLevelUpReward::class),
        );

        return new TutorialUpdateStatusResultData(
            $this->usrModelDiffGetService->getChangedUsrGachas(),
            $this->usrModelDiffGetService->getChangedUsrIdleIncentive(),
            $this->rewardDelegator->getSentRewards(MissionDailyBonusReward::class),
            $this->rewardDelegator->getSentRewards(MissionEventDailyBonusReward::class),
            $usrMissionEventDailyBonusProgresses,
            $this->makeUsrParameterData(
                $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId)
            ),
            $userLevelUpData,
            $this->usrModelDiffGetService->getChangedUsrUnits(),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $this->usrModelDiffGetService->getChangedUsrEmblems(),
            $usrConditionPacks,
        );
    }
}
