<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\Common\Entities\Clock;
use App\Domain\DailyBonus\Delegators\DailyBonusDelegator;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Resource\Entities\UserLoginCount;
use App\Domain\User\Models\UsrUserLoginInterface;
use App\Domain\User\Repositories\LogLoginRepository;
use App\Domain\User\Repositories\UsrUserLoginRepository;
use App\Domain\User\Repositories\UsrUserRepository;
use Carbon\CarbonImmutable;

class UserLoginCountService
{
    public function __construct(
        // Services
        private UserMissionTriggerService $userMissionTriggerService,
        // Delegators
        private MissionDelegator $missionDelegator,
        private DailyBonusDelegator $dailyBonusDelegator,
        // Repositories
        private UsrUserLoginRepository $usrUserLoginRepository,
        private UsrUserRepository $usrUserRepository,
        private LogLoginRepository $logLoginRepository,
        // Common
        private Clock $clock,
    ) {
    }

    /**
     * ログインカウントを更新する
     *
     * @return UserLoginCount|null null=未ログインとみなす
     */
    public function updateLoginCount(string $usrUserId, CarbonImmutable $now): ?UserLoginCount
    {
        $usrUser = $this->usrUserRepository->findById($usrUserId);
        if (!$usrUser->isMainPartTutorialCompleted()) {
            // メインパートのチュートリアルが未完了の場合は、未ログインとみなす
            return null;
        }

        $usrUserLogin = $this->usrUserLoginRepository->getOrCreate($usrUserId, $now);

        $beforeLoginAt = $usrUserLogin->getLastLoginAt();
        $beforeLoginContinueDayCount = $usrUserLogin->getLoginContinueDayCount();

        $isFirstLoginToday = $this->isFirstLoginToday($usrUserLogin, $now);

        $usrUserLogin->login($now);

        if ($isFirstLoginToday === false) {
            $this->usrUserLoginRepository->syncModel($usrUserLogin);

            // ログインログを記録
            $this->logLoginRepository->create(
                $usrUserId,
                $usrUserLogin->getLoginCount(),
                $isFirstLoginToday,
                $usrUserLogin->getLoginDayCount(),
                $usrUserLogin->getLoginContinueDayCount(),
                $usrUserLogin->getComebackDayCount(),
            );

            return new UserLoginCount(
                $beforeLoginAt,
                $now->toDateTimeString(),
                $isFirstLoginToday,
                $usrUserLogin->getLoginDayCount(),
                $beforeLoginContinueDayCount,
                $usrUserLogin->getLoginContinueDayCount(),
                $usrUserLogin->getComebackDayCount(),
            );
        }

        // 本日初ログインなので、各カウントを更新

        $usrUserLogin->incrementLoginDayCount();

        if ($beforeLoginAt === null || $this->clock->isContinuousLogin($beforeLoginAt)) {
            // 連続日ログイン時
            $usrUserLogin->incrementLoginContinueDayCount();
        } else {
            // 連続日ログインでない場合、復帰日数を更新
            $diffDayCount = $this->clock->diffDays($beforeLoginAt);
            $usrUserLogin->comebackLogin($diffDayCount);
        }

        $this->usrUserLoginRepository->syncModel($usrUserLogin);

        // ログインログを記録
        $this->logLoginRepository->create(
            $usrUserId,
            $usrUserLogin->getLoginCount(),
            $isFirstLoginToday,
            $usrUserLogin->getLoginDayCount(),
            $usrUserLogin->getLoginContinueDayCount(),
            $usrUserLogin->getComebackDayCount(),
        );

        $userLoginCount =  new UserLoginCount(
            $beforeLoginAt,
            $now->toDateTimeString(),
            $isFirstLoginToday,
            $usrUserLogin->getLoginDayCount(),
            $beforeLoginContinueDayCount,
            $usrUserLogin->getLoginContinueDayCount(),
            $usrUserLogin->getComebackDayCount(),
        );

        // ミッショントリガー送信
        $this->userMissionTriggerService->sendLoginCountTrigger($usrUserId, $userLoginCount);

        return $userLoginCount;
    }

    /**
     * 本日初ログインかどうかを判定する
     * true: 本日初ログイン, false: 本日2回目以降のログイン
     */
    private function isFirstLoginToday(UsrUserLoginInterface $usrUserLogin, CarbonImmutable $now): bool
    {
        $beforeLoginAt = $usrUserLogin->getLastLoginAt();
        if ($beforeLoginAt === null) {
            // 生涯で初ログインとみなす
            return true;
        }

        $currentLoginAt = $now->toDateTimeString();

        return $this->clock->isAfterDay($beforeLoginAt, $currentLoginAt);
    }

    /**
     * ログインカウント増加に応じて必要となる処理をまとめたメソッド
     */
    public function incrementLoginCountAndProcessActions(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
    ): void {
        // ログインカウントが進んでいないうちは、ログインボーナスの配布はしない
        $userLoginCount = $this->updateLoginCount($usrUserId, $now);
        if (is_null($userLoginCount)) {
            return;
        }

        // デイリーボーナス進捗更新と報酬自動受け取り
        $this->missionDelegator->updateDailyBonusStatuses(
            $usrUserId,
            $platform,
            $now,
            $userLoginCount,
        );

        // イベントデイリーボーナス進捗更新と報酬自動受け取り
        $this->missionDelegator->updateEventDailyBonusStatuses(
            $usrUserId,
            $platform,
            $now,
        );

        // 本日初ログイン時のみ実行する処理
        if ($userLoginCount->getIsFirstLoginToday()) {
            // カムバックボーナス進捗更新と報酬自動受け取り
            $this->dailyBonusDelegator->updateComebackBonusStatuses(
                $usrUserId,
                $platform,
                $now,
                $userLoginCount->getComebackDayCount(),
            );

            // 初心者ミッションを開放する
            $this->missionDelegator->unlockTodayBeginnerMissions($usrUserId);
        }
    }
}
