<?php

declare(strict_types=1);

namespace App\Domain\Mission\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Repositories\LogMissionRewardRepository;
use App\Domain\Mission\Services\MissionFetchService;
use App\Domain\Mission\Services\MissionReceiveRewardService;
use App\Domain\Mission\Services\MissionStatusService;
use App\Domain\Mission\Services\MissionUpdateHandleService;
use App\Domain\Resource\Entities\Rewards\MissionReward;
use App\Domain\Resource\Entities\Rewards\UserLevelUpReward;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Shop\Delegators\ShopDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\ResultData\MissionBulkReceiveRewardResultData;

class MissionBulkReceiveRewardUseCase
{
    use UseCaseTrait;

    public function __construct(
        private MissionUpdateHandleService $missionUpdateHandleService,
        private MissionStatusService $missionStatusService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        private MissionReceiveRewardService $missionReceiveRewardService,
        private MissionFetchService $missionFetchService,
        private LogMissionRewardRepository $logMissionRewardRepository,
        // Delegator
        private RewardDelegator $rewardDelegator,
        private UserDelegator $userDelegator,
        private ShopDelegator $shopDelegator,
        // Other
        private Clock $clock,
    ) {
    }

    /**
     * @param array<string> $mstMissionIds
     */
    public function exec(
        CurrentUser $user,
        int $platform,
        string $missionType,
        array $mstMissionIds,
    ): MissionBulkReceiveRewardResultData {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        $missionType = MissionType::getFromString($missionType);

        $beforeUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($user->id);
        $beforeExp = $beforeUsrUserParameter->getExp();
        $beforeLevel = $beforeUsrUserParameter->getLevel();

        $receiveRewardStatuses = $this->missionReceiveRewardService->bulkReceiveReward(
            $usrUserId,
            $now,
            $platform,
            $missionType,
            collect($mstMissionIds),
        );

        // トランザクション処理
        list(
            $usrConditionPacks,
            $afterUsrUserParameter,
        ) = $this->applyUserTransactionChanges(function () use (
            $usrUserId,
            $platform,
            $now,
            $missionType,
            $beforeLevel,
        ) {
            // 報酬配布実行
            $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

            // 報酬配布されたことで
            // レスポンスデータを作る前にトリガーされたミッションの進捗判定を実行する
            /**
             * 報酬配布がされたので、他のミッションがトリガーされ、進捗が変動する。
             * この報酬受取APIでは、他のミッションの進捗変動もレスポンスする必要があるため、
             * 報酬配布直後に、ミッション進捗判定を行い、レスポンス用のデータを作成する必要がある
             */
            $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $now);

            // 初心者ミッションの完了ステータス更新
            if ($missionType->isBeginner()) {
                $this->missionStatusService->completeBeginnerMission($usrUserId);
            }

            // レベルアップパックの開放
            $usrConditionPacks = collect();
            // 報酬受け取りでレベルが上っている可能性があるので再取得
            $afterUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
            if ($beforeLevel < $afterUsrUserParameter->getLevel()) {
                $usrConditionPacks = $this->shopDelegator->releaseUserLevelPack(
                    $usrUserId,
                    $afterUsrUserParameter->getLevel(),
                    $now
                );
            }

            // ミッション報酬ログ
            $missionRewards = $this->rewardDelegator->getSentRewards(MissionReward::class);
            $this->logMissionRewardRepository->create(
                $usrUserId,
                $missionType,
                $missionRewards,
            );

            return [
                $usrConditionPacks,
                $afterUsrUserParameter,
            ];
        });

        // ミッション進捗データ取得
        // normal
        $normalFetchStatusData = $this->missionFetchService
            ->getMissionNormalFetchStatusWhenReceiveRewards($usrUserId, $now);
        $achievementFetchStatusData = $normalFetchStatusData->getAchievementMissionFetchStatusData();
        $dailyFetchStatusData = $normalFetchStatusData->getDailyMissionFetchStatusData();
        $weeklyFetchStatusData = $normalFetchStatusData->getWeeklyMissionFetchStatusData();
        $beginnerFetchStatusData = $normalFetchStatusData->getBeginnerMissionFetchStatusData();
        // event
        $eventCategoryFetchStatusData = $this->missionFetchService
            ->getMissionEventFetchStatusWhenReceiveRewards($usrUserId, $now);
        $eventFetchStatusData = $eventCategoryFetchStatusData->getEventMissionFetchStatusData();
        $eventDailyFetchStatusData = $eventCategoryFetchStatusData->getEventDailyMissionFetchStatusData();
        // limited term
        $limitedTermFetchStatus = $this->missionFetchService->getMissionLimitedTermFetchStatusWhenReceiveRewards(
            $usrUserId,
            $now,
        );

        // ボーナスポイントの獲得状況を取得
        $usrMissionBonusPoints = collect();
        $usrMissionBonusPoints->push(
            $dailyFetchStatusData->getUsrMissionBonusPointData(),
        );
        $usrMissionBonusPoints->push(
            $weeklyFetchStatusData->getUsrMissionBonusPointData(),
        );
        $usrMissionBonusPoints->push(
            $beginnerFetchStatusData->getUsrMissionBonusPointData(),
        );

        // レスポンスデータを作成
        $userLevelUpData = new UserLevelUpData(
            $beforeExp,
            $afterUsrUserParameter->getExp(),
            $this->rewardDelegator->getSentRewards(UserLevelUpReward::class),
        );

        return new MissionBulkReceiveRewardResultData(
            $receiveRewardStatuses,
            $this->rewardDelegator->getSentRewards(MissionReward::class),
            $achievementFetchStatusData->getUsrMissionStatusDataList(),
            $dailyFetchStatusData->getUsrMissionStatusDataList(),
            $weeklyFetchStatusData->getUsrMissionStatusDataList(),
            $beginnerFetchStatusData->getUsrMissionStatusDataList(),
            $eventFetchStatusData->getUsrMissionStatusDataList(),
            $eventDailyFetchStatusData->getUsrMissionStatusDataList(),
            $limitedTermFetchStatus->getUsrMissionStatusDataList(),
            $usrMissionBonusPoints,
            $this->makeUsrParameterData($afterUsrUserParameter),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $this->usrModelDiffGetService->getChangedUsrUnits(),
            $userLevelUpData,
            $usrConditionPacks,
            $this->usrModelDiffGetService->getChangedUsrArtworks(),
            $this->usrModelDiffGetService->getChangedUsrArtworkFragments(),
        );
    }
}
