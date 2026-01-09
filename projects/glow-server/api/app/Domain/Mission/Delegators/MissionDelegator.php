<?php

declare(strict_types=1);

namespace App\Domain\Mission\Delegators;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\MissionManager;
use App\Domain\Mission\Repositories\UsrMissionStatusRepository;
use App\Domain\Mission\Services\MissionBeginnerService;
use App\Domain\Mission\Services\MissionDailyBonusUpdateService;
use App\Domain\Mission\Services\MissionEventDailyBonusFetchService;
use App\Domain\Mission\Services\MissionEventDailyBonusUpdateService;
use App\Domain\Mission\Services\MissionUpdateHandleService;
use App\Domain\Resource\Entities\UserLoginCount;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MissionDelegator
{
    public function __construct(
        private MissionManager $missionManager,
        private MissionUpdateHandleService $missionUpdateHandleService,
        private MissionDailyBonusUpdateService $missionDailyBonusUpdateService,
        private MissionEventDailyBonusFetchService $missionEventDailyBonusFetchService,
        private MissionEventDailyBonusUpdateService $missionEventDailyBonusUpdateService,
        private UsrMissionStatusRepository $usrMissionStatusRepository,
        private MissionBeginnerService $missionBeginnerService,
    ) {
    }

    public function addTrigger(MissionTrigger $missionTrigger, ?MissionType $missionType = null): void
    {
        $this->missionManager->addTrigger($missionTrigger, $missionType);
    }

    public function addTriggers(Collection $missionTriggers, ?MissionType $missionType = null): void
    {
        $this->missionManager->addTriggers($missionTriggers, $missionType);
    }

    public function handleAllUpdateTriggeredMissions(string $usrUserId, CarbonImmutable $now): void
    {
        $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $now);
    }

    /**
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @param UserLoginCount $userLoginCount
     * @return void
     */
    public function updateDailyBonusStatuses(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
        UserLoginCount $userLoginCount,
    ): void {
        $this->missionDailyBonusUpdateService->updateStatuses(
            $usrUserId,
            $platform,
            $now,
            $userLoginCount,
        );
    }

    /**
     * @param string $usrUserId
     * @param int $platform
     * @param CarbonImmutable $now
     * @return void
     */
    public function updateEventDailyBonusStatuses(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
    ): void {
        $this->missionEventDailyBonusUpdateService->updateStatuses(
            $usrUserId,
            $platform,
            $now,
        );
    }

    /**
     * @param string $usrUserId
     * @param CarbonImmutable $now
     */
    public function unlockMission(string $usrUserId, CarbonImmutable $now): void
    {
        $this->usrMissionStatusRepository->setUnlockMission($usrUserId, $now);
    }

    public function unlockTodayBeginnerMissions(string $usrUserId): void
    {
        $this->missionBeginnerService->unlockTodayMissions($usrUserId);
    }

    /**
     * イベントデイリーボーナスの進捗情報を取得
     *
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return Collection
     */
    public function fetchEventDailyBonusProgresses(string $usrUserId, CarbonImmutable $now): Collection
    {
        return $this->missionEventDailyBonusFetchService->fetchProgresses($usrUserId, $now);
    }
}
