<?php

declare(strict_types=1);

namespace App\Domain\Mission\Services;

use App\Domain\Mission\Repositories\UsrMissionDailyBonusRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionDailyBonusRepository;
use App\Domain\Resource\Usr\Entities\UsrUserLoginEntity;
use App\Http\Responses\Data\UsrMissionStatusData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MissionDailyBonusFetchService
{
    public function __construct(
        // Repository
        private MstMissionDailyBonusRepository $mstMissionDailyBonusRepository,
        private UsrMissionDailyBonusRepository $usrMissionDailyBonusRepository,
    ) {
    }

    /**
     * ミッションのステータス情報を全て取得
     *
     * @return Collection<UsrMissionStatusData>
     */
    public function fetchAllStatuses(
        string $usrUserId,
        CarbonImmutable $now,
        UsrUserLoginEntity $usrUserLogin,
    ): Collection {
        $mstMissions = $this->mstMissionDailyBonusRepository->getMapAll();
        $mstMissionIds = $mstMissions->keys();

        $usrMissions = $this->usrMissionDailyBonusRepository->getByMstMissionIds(
            $usrUserId,
            $mstMissionIds,
        );

        $usrStatusDataList = collect();
        foreach ($usrMissions as $usrMission) {
            /** @var \App\Domain\Mission\Models\UsrMissionDailyBonusInterface $usrMission */
            $mstMission = $mstMissions->get($usrMission->getMstMissionId());
            if ($mstMission === null) {
                continue;
            }

            if ($mstMission->isDailyBonusType()) {
                $progress = $usrUserLogin->getLoginContinueDayCount();
            } else {
                // foreachループでcontinueする
                continue;
            }

            $progress = min($progress, $mstMission->getLoginDayCount());

            $usrStatusData = new UsrMissionStatusData(
                $mstMission->getId(),
                $progress,
                $usrMission->isClear(),
                $usrMission->isReceivedReward(),
            );
            $usrStatusDataList->push($usrStatusData);
        }

        return $usrStatusDataList;
    }
}
