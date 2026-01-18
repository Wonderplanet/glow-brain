<?php

declare(strict_types=1);

namespace App\Domain\Mission\Services;

use App\Domain\Mission\Constants\MissionConstant;
use App\Domain\Mission\Entities\MissionReceiveRewardStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\UsrMissionDailyBonusInterface;
use App\Domain\Mission\Repositories\UsrMissionDailyBonusRepository;
use App\Domain\Resource\Entities\UserLoginCount;
use App\Domain\Resource\Mst\Entities\MstMissionDailyBonusEntity;
use App\Domain\Resource\Mst\Repositories\MstMissionDailyBonusRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MissionDailyBonusUpdateService
{
    public function __construct(
        // Repository
        private MstMissionDailyBonusRepository $mstMissionDailyBonusRepository,
        private UsrMissionDailyBonusRepository $usrMissionDailyBonusRepository,
        // Service
        private MissionRewardService $missionRewardService,
        // Delegator
        private RewardDelegator $rewardDelegator,
    ) {
    }

    /**
     * 進捗更新メソッド
     *
     * @return Collection<MissionReceiveRewardStatus>
     */
    public function updateStatuses(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
        UserLoginCount $userLoginCount,
    ): Collection {
        if ($userLoginCount->getIsFirstLoginToday() === false) {
            return collect();
        }

        $totalProgress = $userLoginCount->getLoginDayCount();

        $mstMissions = $this->mstMissionDailyBonusRepository->getMapForUpdateStatus($totalProgress);
        $mstMissionIds = $mstMissions->keys();

        /** @var Collection<string, UsrMissionDailyBonusInterface> */
        $usrMissions = $this->usrMissionDailyBonusRepository->getByMstMissionIds($usrUserId, $mstMissionIds)
            ->keyBy(function (UsrMissionDailyBonusInterface $usrMission) {
                return $usrMission->getMstMissionId();
            });

        // 進捗更新
        foreach ($mstMissions as $mstMission) {
            /** @var MstMissionDailyBonusEntity $mstMission */
            $mstMissionId = $mstMission->getId();
            /** @var UsrMissionDailyBonusInterface $usrMission */
            $usrMission = $usrMissions->get($mstMissionId);

            $usrMission = $this->updateStatus(
                $usrUserId,
                $now,
                $userLoginCount,
                $mstMission,
                $usrMission,
            );

            if ($usrMission === null) {
                continue;
            }

            $usrMissions->put($mstMissionId, $usrMission);
        }

        // 報酬自動受け取り
        $rewards = $this->missionRewardService->calcRewards(
            MissionType::DAILY_BONUS,
            $mstMissions,
            $usrMissions,
        );
        $this->rewardDelegator->addRewards($rewards);
        $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

        // 受取済ステータスへ更新
        $receiveStatuses = collect();
        foreach ($usrMissions as $usrMission) {
            /** @var UsrMissionDailyBonusInterface $usrMission */
            if ($usrMission->canReceiveReward() === false) {
                continue;
            }

            $usrMission->receiveReward($now);
            $usrMissions->put($usrMission->getMstMissionId(), $usrMission);

            $receiveStatuses->push(
                new MissionReceiveRewardStatus(
                    MissionType::DAILY_BONUS,
                    $usrMission->getMstMissionId(),
                    null,
                )
            );
        }
        $this->usrMissionDailyBonusRepository->syncModels($usrMissions);

        return $receiveStatuses;
    }

    /**
     * デイリーボーナスタイプのミッションの進捗を全日リセットするか判定
     *
     * 連続ログインが途切れた時 または 全日達成して2周目以降に突入した時に、全日リセットする
     */
    private function isResetDailyBonusTerm(
        UserLoginCount $userLoginCount,
    ): bool {
        if ($userLoginCount->isContinuousLogin() === false) {
            return true;
        }

        $loginContinueDayCount = $userLoginCount->getLoginContinueDayCount();
        $maxCount = MissionConstant::MAX_DAILY_BONUS_LOGIN_CONTINUE_DAY_COUNT;

        $quotient = floor($loginContinueDayCount / $maxCount);
        $remainder = $loginContinueDayCount % $maxCount;

        return $quotient >= 1 && $remainder === 1;
    }

    /**
     * ユーザーデータ1つ当たりの進捗更新処理
     */
    private function updateStatus(
        string $usrUserId,
        CarbonImmutable $now,
        UserLoginCount $userLoginCount,
        MstMissionDailyBonusEntity $mstMission,
        ?UsrMissionDailyBonusInterface $usrMission,
    ): ?UsrMissionDailyBonusInterface {
        if ($mstMission->isDailyBonusType()) {
            $usrMission = $this->updateDailyBonusTypeStatus(
                $usrUserId,
                $now,
                $userLoginCount,
                $mstMission,
                $usrMission,
            );
        }

        return $usrMission;
    }


    /**
     * DailyBonusタイプのユーザーデータ1つ当たりの進捗更新処理
     *
     * @return UsrMissionDailyBonusInterface
     */
    private function updateDailyBonusTypeStatus(
        string $usrUserId,
        CarbonImmutable $now,
        UserLoginCount $userLoginCount,
        MstMissionDailyBonusEntity $mstMission,
        ?UsrMissionDailyBonusInterface $usrMission,
    ): ?UsrMissionDailyBonusInterface {
        if ($mstMission->isDailyBonusType() === false) {
            return $usrMission;
        }

        $mstMissionId = $mstMission->getId();
        $criterionCount = $mstMission->getLoginDayCount();

        // 想定しないデータを持つマスタの場合は何もせずに終了
        if ($criterionCount > MissionConstant::MAX_DAILY_BONUS_LOGIN_CONTINUE_DAY_COUNT) {
            return $usrMission;
        }

        $progress = $this->calcDailyBonusProgress($userLoginCount);

        $isResetTerm = $this->isResetDailyBonusTerm($userLoginCount);

        if ($isResetTerm) {
            $usrMission?->resetStatus($now);
        }

        if ($criterionCount > $progress) {
            // 進捗的にまだ未クリアの場合は何もせずに終了
            return $usrMission;
        }

        if ($usrMission === null) {
            $usrMission = $this->usrMissionDailyBonusRepository->create(
                $usrUserId,
                $mstMissionId,
            );
        }

        if ($usrMission->isClear()) {
            return $usrMission;
        }

        $usrMission->clear($now);

        return $usrMission;
    }

    /**
     * DailyBonusタイプのミッションの進捗値を計算する
     *
     * ログイン連続日数が{MissionConstant::MAX_DAILY_BONUS_LOGIN_CONTINUE_DAY_COUNT}の倍数日の場合は、
     * {MissionConstant::MAX_DAILY_BONUS_LOGIN_CONTINUE_DAY_COUNT}日目として扱う
     *
     * 例：MissionConstant::MAX_DAILY_BONUS_LOGIN_CONTINUE_DAY_COUNT = 7 の場合
     * 7日目 → 7日目
     * 8日目 → 1日目
     * 14日目 → 7日目
     * 15日目 → 1日目
     * 21日目 → 7日目
     * 22日目 → 1日目
     */
    private function calcDailyBonusProgress(
        UserLoginCount $userLoginCount,
    ): int {
        $progress = $userLoginCount->getLoginContinueDayCount()
            % MissionConstant::MAX_DAILY_BONUS_LOGIN_CONTINUE_DAY_COUNT;
        if ($progress === 0) {
            $progress = MissionConstant::MAX_DAILY_BONUS_LOGIN_CONTINUE_DAY_COUNT;
        }

        return $progress;
    }
}
