<?php

declare(strict_types=1);

namespace App\Domain\Mission\Services;

use App\Domain\Common\Entities\Clock;
use App\Domain\Mission\Entities\MissionReceiveRewardStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\UsrMissionEventDailyBonusInterface;
use App\Domain\Mission\Models\UsrMissionEventDailyBonusProgressInterface;
use App\Domain\Mission\Repositories\UsrMissionEventDailyBonusProgressRepository;
use App\Domain\Mission\Repositories\UsrMissionEventDailyBonusRepository;
use App\Domain\Resource\Mst\Entities\MstMissionEventDailyBonusEntity;
use App\Domain\Resource\Mst\Repositories\MstMissionEventDailyBonusRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionEventDailyBonusScheduleRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MissionEventDailyBonusUpdateService
{
    public function __construct(
        // Repository
        private MstMissionEventDailyBonusRepository $mstEventBonusRepository,
        private MstMissionEventDailyBonusScheduleRepository $mstEventBonusScheduleRepository,
        private UsrMissionEventDailyBonusRepository $usrEventBonusRepository,
        private UsrMissionEventDailyBonusProgressRepository $usrEventBonusProgressRepository,
        // Service
        private MissionRewardService $missionRewardService,
        // Delegator
        private RewardDelegator $rewardDelegator,
        // Common
        private Clock $clock,
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
    ): Collection {
        $mstScheduleIds = $this->mstEventBonusScheduleRepository->getActiveMapAll($now)->keys();
        if ($mstScheduleIds->isEmpty()) {
            return collect();
        }
        // 期限内のイベントログボの進捗
        $usrProgresses = $this->usrEventBonusProgressRepository->getByMstScheduleIds($usrUserId, $mstScheduleIds)
            ->keyBy(function (UsrMissionEventDailyBonusProgressInterface $usrMission) {
                return $usrMission->getMstMissionEventDailyBonusScheduleId();
            });
        if ($mstScheduleIds->count() !== $usrProgresses->count()) {
            foreach ($mstScheduleIds as $mstScheduleId) {
                if (!$usrProgresses->has($mstScheduleId)) {
                    $usrProgresses->put(
                        $mstScheduleId,
                        $this->usrEventBonusProgressRepository->create($usrUserId, $mstScheduleId)
                    );
                }
            }
        }
        $usrMissions = collect();
        $mstMissions = $this->mstEventBonusRepository->getMapByMstScheduleIds($mstScheduleIds);
        // 期間内のイベントログインボーナスイベントが同時に複数ある場合のみ2回以上ループする
        /** @var UsrMissionEventDailyBonusProgressInterface $usrProgress */
        foreach ($usrProgresses as $mstScheduleId => $usrProgress) {
            // 初回ログインじゃなければスキップ
            if ($usrProgress->getProgress() !== 0 && !$this->clock->isFirstToday($usrProgress->getLatestUpdateAt())) {
                continue;
            } else {
                $usrProgress->incrementProgress($now);
                $this->usrEventBonusProgressRepository->syncModel($usrProgress);
            }

            $mstScheduleMissions = $mstMissions
                ->filter(function (MstMissionEventDailyBonusEntity $mstMission) use ($mstScheduleId) {
                    return $mstMission->getMstMissionEventDailyBonusScheduleId() === $mstScheduleId;
                })->keyBy(function (MstMissionEventDailyBonusEntity $mstMission) {
                    return $mstMission->getLoginDayCount();
                });
            /** @var MstMissionEventDailyBonusEntity|null $mstMission */
            $mstMission = $mstScheduleMissions->get($usrProgress->getProgress());
            if ($mstMission === null) {
                continue;
            }
            $usrMission = $this->updateStatus(
                $usrUserId,
                $now,
                $mstMission,
            );

            $usrMissions->put($mstScheduleId, $usrMission);
        }

        // 報酬自動受け取り
        $rewards = $this->missionRewardService->calcRewards(
            MissionType::EVENT_DAILY_BONUS,
            $mstMissions,
            $usrMissions,
        );
        $this->rewardDelegator->addRewards($rewards);
        $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

        // 受取済ステータスへ更新
        $receiveStatuses = collect();
        foreach ($usrMissions as $usrMission) {
            /** @var UsrMissionEventDailyBonusInterface $usrMission */
            if ($usrMission->canReceiveReward() === false) {
                continue;
            }

            $usrMission->receiveReward($now);
            $usrMissions->put($usrMission->getMstMissionId(), $usrMission);

            $receiveStatuses->push(
                new MissionReceiveRewardStatus(
                    MissionType::EVENT_DAILY_BONUS,
                    $usrMission->getMstMissionId(),
                    null,
                )
            );
        }
        $this->usrEventBonusRepository->syncModels($usrMissions);

        return $receiveStatuses;
    }

    /**
     * ユーザーデータ1つ当たりの進捗更新処理
     */
    private function updateStatus(
        string $usrUserId,
        CarbonImmutable $now,
        MstMissionEventDailyBonusEntity $mstMission,
    ): UsrMissionEventDailyBonusInterface {
        $usrMission = $this->usrEventBonusRepository->getByMstMissionId($usrUserId, $mstMission->getId());
        $mstMissionId = $mstMission->getId();

        if ($usrMission === null) {
            $usrMission = $this->usrEventBonusRepository->create(
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
}
