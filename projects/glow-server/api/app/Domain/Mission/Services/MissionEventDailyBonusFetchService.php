<?php

declare(strict_types=1);

namespace App\Domain\Mission\Services;

use App\Domain\Mission\Models\UsrMissionEventDailyBonusProgressInterface;
use App\Domain\Mission\Repositories\UsrMissionEventDailyBonusProgressRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionEventDailyBonusScheduleRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MissionEventDailyBonusFetchService
{
    public function __construct(
        // Repository
        private MstMissionEventDailyBonusScheduleRepository $mstEventBonusScheduleRepository,
        private UsrMissionEventDailyBonusProgressRepository $usrEventBonusProgressRepository,
    ) {
    }

    /**
     * イベントデイリーボーナスの進捗情報を取得
     *
     * @return Collection<\App\Domain\Mission\Models\UsrMissionEventDailyBonusProgressInterface>
     */
    public function fetchProgresses(
        string $usrUserId,
        CarbonImmutable $now,
    ): Collection {
        $mstScheduleIds = $this->mstEventBonusScheduleRepository->getActiveMapAll($now)->keys();
        if ($mstScheduleIds->isEmpty()) {
            return collect();
        }
        // 期限内のイベントログボの進捗
        return $this->usrEventBonusProgressRepository->getByMstScheduleIds($usrUserId, $mstScheduleIds)
            ->keyBy(function (UsrMissionEventDailyBonusProgressInterface $usrMission) {
                return $usrMission->getMstMissionEventDailyBonusScheduleId();
            });
    }
}
