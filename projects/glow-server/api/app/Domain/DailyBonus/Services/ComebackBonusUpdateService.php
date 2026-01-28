<?php

declare(strict_types=1);

namespace App\Domain\DailyBonus\Services;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Services\ClockService;
use App\Domain\DailyBonus\Models\UsrComebackBonusProgressInterface;
use App\Domain\DailyBonus\Repositories\UsrComebackBonusProgressRepository;
use App\Domain\Resource\Mst\Repositories\MstComebackBonusRepository;
use App\Domain\Resource\Mst\Repositories\MstComebackBonusScheduleRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use Carbon\CarbonImmutable;

class ComebackBonusUpdateService
{
    public function __construct(
        // Repository
        private MstComebackBonusRepository $mstComebackBonusRepository,
        private MstComebackBonusScheduleRepository $mstComebackBonusScheduleRepository,
        private UsrComebackBonusProgressRepository $usrComebackBonusProgressRepository,
        // Service
        private DailyBonusRewardService $dailyBonusRewardService,
        // Delegator
        private RewardDelegator $rewardDelegator,
        // Common
        private Clock $clock,
        private ClockService $clockService,
    ) {
    }

    /**
     * 進捗更新メソッド
     */
    public function updateStatuses(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
        int $comebackDayCount,
    ): void {
        $mstSchedules = $this->mstComebackBonusScheduleRepository->getActiveMapAll($now);
        if ($mstSchedules->isEmpty()) {
            return;
        }
        // 期限内のカムハックボーナスログボの進捗
        $mstScheduleIds = $mstSchedules->keys();
        $usrProgresses = $this->usrComebackBonusProgressRepository->getByMstScheduleIds($usrUserId, $mstScheduleIds)
            ->keyBy(function (UsrComebackBonusProgressInterface $usrProgress) {
                return $usrProgress->getMstScheduleId();
            });
        // 開始条件日数をチェックして、開始orリセットを行う
        foreach ($mstSchedules as $mstSchedule) {
            /** @var \App\Domain\Resource\Mst\Entities\MstComebackBonusScheduleEntity $mstSchedule */
            if ($comebackDayCount <= $mstSchedule->getInactiveConditionDays()) {
                continue;
            }
            // 開始
            if (!$usrProgresses->has($mstSchedule->getId())) {
                $receiveTerm = $this->clockService->calcDaysRange($now, $mstSchedule->getDurationDays());
                $usrProgresses->put(
                    $mstSchedule->getId(),
                    $this->usrComebackBonusProgressRepository->create(
                        $usrUserId,
                        $mstSchedule->getId(),
                        $receiveTerm->startAt,
                        $receiveTerm->endAt,
                    )
                );
            } else {
                // リセット
                $receiveTerm = $this->clockService->calcDaysRange($now, $mstSchedule->getDurationDays());
                $usrProgress = $usrProgresses->get($mstSchedule->getId());
                $usrProgress->resetProgress($now);
                $usrProgress->resetTerm($receiveTerm->startAt, $receiveTerm->endAt);
            }
        }
        // 期間内のカムバックログインボーナスが同時に複数ある場合のみ2回以上ループする
        /** @var UsrComebackBonusProgressInterface $usrProgress */
        foreach ($usrProgresses as $usrProgressKey => $usrProgress) {
            // 有効期間外と、初回ログインじゃなければスキップ
            if (
                !$now->between($usrProgress->getStartAt(), $usrProgress->getEndAt()) ||
                ($usrProgress->getProgress() !== 0 && !$this->clock->isFirstToday($usrProgress->getLatestUpdateAt()))
            ) {
                $usrProgresses->forget($usrProgressKey);
            } else {
                $usrProgress->incrementProgress($now);
                $this->usrComebackBonusProgressRepository->syncModel($usrProgress);
            }
        }
        // 進捗がなければ報酬受け取りをスキップ
        if ($usrProgresses->isEmpty()) {
            return;
        }

        // 報酬受け取り
        $mstComebackBonuses = $this->mstComebackBonusRepository->getMapByMstScheduleIds($usrProgresses->keys());
        $rewards = $this->dailyBonusRewardService->calcRewards(
            $mstComebackBonuses,
            $usrProgresses,
        );
        $this->rewardDelegator->addRewards($rewards);
        $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);
    }
}
