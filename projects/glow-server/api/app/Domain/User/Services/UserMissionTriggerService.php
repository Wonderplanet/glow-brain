<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Resource\Entities\UserLoginCount;

class UserMissionTriggerService
{
    public function __construct(
        private MissionDelegator $missionDelegator,
        private Clock $clock,
    ) {
    }

    public function sendCoinCollectTrigger(
        int $collectedCoin,
    ): void {
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::COIN_COLLECT->value,
                null,
                $collectedCoin,
            )
        );
    }

    public function sendCoinUsedTrigger(
        int $consumedCoin,
    ): void {
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::COIN_USED_COUNT->value,
                null,
                $consumedCoin,
            )
        );
    }

    public function sendUserLevelTrigger(
        int $currentUserLevel,
        int $afterUserLevel,
    ): void {
        if ($currentUserLevel >= $afterUserLevel) {
            return;
        }

        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::USER_LEVEL->value,
                null,
                $afterUserLevel,
            )
        );
    }

    public function sendLoginCountTrigger(string $usrUserId, UserLoginCount $userLoginCount): void
    {
        if ($userLoginCount->getIsFirstLoginToday() === false) {
            return;
        }

        /**
         * login_count
         */

        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::LOGIN_COUNT->value,
                null,
                1,
            )
        );

        /**
         * login_continue_count
         */

        // 連続ログイン日数は、ミッションタイプごとに進捗値を変えて送信する
        // achievement
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::LOGIN_CONTINUE_COUNT->value,
                null,
                $userLoginCount->getLoginContinueDayCount(),
            ),
            MissionType::ACHIEVEMENT,
        );

        // weekly
        $weeklyProgress = $this->calcWeeklyLoginContinueCount(
            $userLoginCount->getLoginContinueDayCount(),
        );
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::LOGIN_CONTINUE_COUNT->value,
                null,
                $weeklyProgress,
            ),
            MissionType::WEEKLY,
        );

        //　beginner
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::LOGIN_CONTINUE_COUNT->value,
                null,
                $userLoginCount->getLoginContinueDayCount(),
            ),
            MissionType::BEGINNER,
        );
    }

    /**
     * 週の連続ログイン日数を計算する
     *
     * 連続ログイン日数は、ログイン1日目に1となるように算出する。
     */
    public function calcWeeklyLoginContinueCount(int $loginContinueDayCount): int
    {
        $loginContinueStartAt = $this->clock->calcDayStartAtFromElapsedDays(
            // 連続ログイン1日目は$loginContinueDayCount=1となるが、日付的には差分は0なので、-1する
            max($loginContinueDayCount - 1, 0),
        );
        $weekStartAt = $this->clock->getWeekStartDatetime();
        if ($weekStartAt->gte($loginContinueStartAt)) {
            /**
             * $weekStartAt >= $loginContinueStartAt
             * 連続ログイン継続中に週初日が含まれる場合、週初日からの進捗を算出する
             *
             * ログイン日が週初日なら1。
             * 週1,2日目まで連続ログインしている場合は2。
             */
            $weeklyProgress = $this->clock->diffDays($weekStartAt->toDateTimeString()) + 1;
        } else {
            // $weekStartAt < $loginContinueStartAt
            /**
             * 週初日が連続ログイン開始より前の場合、UsrUserLoginのlogin_continue_day_countをそのまま進捗として返す
             */
            $weeklyProgress = $loginContinueDayCount;
        }

        return $weeklyProgress;
    }
}
