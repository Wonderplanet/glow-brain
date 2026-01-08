<?php

declare(strict_types=1);

namespace App\Domain\Mission\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Mission\Repositories\UsrMissionStatusRepository;
use App\Domain\Mission\Services\MissionDailyBonusFetchService;
use App\Domain\Mission\Services\MissionFetchService;
use App\Domain\Mission\Services\MissionInstantClearService;
use App\Domain\Mission\Services\MissionStatusService;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\MissionUpdateAndFetchResultData;

class MissionUpdateAndFetchUseCase
{
    use UseCaseTrait;

    public function __construct(
        private MissionStatusService $missionStatusService,
        private UsrMissionStatusRepository $usrMissionStatusRepository,
        private MissionInstantClearService $MissionInstantClearService,
        // MissionFetchService
        private MissionFetchService $missionFetchService,
        private MissionDailyBonusFetchService $missionDailyBonusFetchService,
        // Delegator
        private UserDelegator $userDelegator,
        // Other
        private Clock $clock,
    ) {
    }

    public function exec(CurrentUser $user): MissionUpdateAndFetchResultData
    {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        // 即時達成判定の実行
        $this->MissionInstantClearService->execInstantClear(
            $usrUserId,
            $now,
        );

        // トランザクション処理
        $this->applyUserTransactionChanges();

        // レスポンス用意

        // ミッション進捗データ取得
        // ここでは、リセットなどのモデル値のDB更新は行わない
        $normalFetchStatusData = $this->missionFetchService->getMissionNormalFetchStatusWhenFetchAll(
            $usrUserId,
            $now,
        );
        $achievementFetchStatusData = $normalFetchStatusData->getAchievementMissionFetchStatusData();
        $dailyFetchStatusData = $normalFetchStatusData->getDailyMissionFetchStatusData();
        $weeklyFetchStatusData = $normalFetchStatusData->getWeeklyMissionFetchStatusData();
        $beginnerFetchStatusData = $normalFetchStatusData->getBeginnerMissionFetchStatusData();

        // ミッション機能解放からの経過日数を取得
        $usrMissionStatus = $this->usrMissionStatusRepository->get($usrUserId);
        $beginnerDaysFromStart = $this->missionStatusService->calcDaysFromMissionUnlockedAt($usrMissionStatus);

        $usrUserLogin = $this->userDelegator->getUsrUserLogin($usrUserId);
        $dailyBonusStatusDataList = $this->missionDailyBonusFetchService->fetchAllStatuses(
            $usrUserId,
            $now,
            $usrUserLogin,
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

        return new MissionUpdateAndFetchResultData(
            $achievementFetchStatusData->getUsrMissionStatusDataList(),
            $dailyFetchStatusData->getUsrMissionStatusDataList(),
            $weeklyFetchStatusData->getUsrMissionStatusDataList(),
            $beginnerFetchStatusData->getUsrMissionStatusDataList(),
            $beginnerDaysFromStart,
            $dailyBonusStatusDataList,
            $usrMissionBonusPoints,
        );
    }
}
