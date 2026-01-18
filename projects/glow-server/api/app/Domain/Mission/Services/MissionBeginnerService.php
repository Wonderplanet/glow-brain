<?php

declare(strict_types=1);

namespace App\Domain\Mission\Services;

use App\Domain\Mission\Constants\MissionConstant;
use App\Domain\Mission\Entities\UsrMissionNormalBundle;
use App\Domain\Mission\Enums\MissionBeginnerStatus;
use App\Domain\Mission\Models\UsrMissionNormalInterface;
use App\Domain\Mission\Repositories\UsrMissionNormalRepository;
use App\Domain\Mission\Repositories\UsrMissionStatusRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionBeginnerRepository;

class MissionBeginnerService
{
    public function __construct(
        private MissionStatusService $missionStatusService,
        private MstMissionBeginnerRepository $mstMissionBeginnerRepository,
        private UsrMissionNormalRepository $usrMissionNormalRepository,
        private UsrMissionStatusRepository $usrMissionStatusRepository,
    ) {
    }

    /**
     * 初心者ミッションの当日分を開放する
     *
     * 1日の内の初回ログイン時に毎回DAYS_FROM_UNLOCKED_MISSIONをトリガーして開放判定をすると
     * 全初心者ミッションの開放進捗を更新する必要があり、更新量が多くなってしまう。
     * 上記を避けるために、ログイン処理時に、経過日数的に開放可能なミッションを直接開放する
     *
     * @param string $usrUserId
     * @return void
     */
    public function unlockTodayMissions(string $usrUserId)
    {
        $usrMissionStatus = $this->usrMissionStatusRepository->getOrCreate($usrUserId);
        if (
            $usrMissionStatus->isBeginnerMissionFullyUnlocked()
            || $usrMissionStatus->isBeginnerMissionCompleted()
        ) {
            // 既に全ての初心者ミッションが開放されているとみなして、何もしない
            // 初心者ミッションを全てクリアした場合もここに該当する
            return;
        }

        $unlockDay = $this->missionStatusService->calcDaysFromMissionUnlockedAt($usrMissionStatus);
        $mstMissions = $this->mstMissionBeginnerRepository->getByMaxUnlockDay(
            $unlockDay,
        );
        /** @var UsrMissionNormalBundle $usrMissionNormalBundle */
        $usrMissionNormalBundle = $this->usrMissionNormalRepository->getByMstMissionIds(
            $usrUserId,
            mstMissionBeginnerIds: $mstMissions->keys()->toArray(),
        );
        $usrMissions = $usrMissionNormalBundle->getBeginners();

        // ミッション開放
        $usrMissions->each(function (UsrMissionNormalInterface $usrMission) use ($unlockDay) {
            if ($usrMission->isOpen()) {
                // 既に開放済みなら何もしない
                return;
            }
            $usrMission->updateUnlockProgress($unlockDay);
            $usrMission->open();
        });
        $this->usrMissionNormalRepository->syncModels($usrMissions);

        // 全初心者ミッションを開放した場合、初心者ミッションステータスを更新
        if ($unlockDay >= MissionConstant::MAX_BEGINNER_UNLOCK_DAY_COUNT) {
            $usrMissionStatus->setBeginnerMissionStatus(MissionBeginnerStatus::FULLY_UNLOCKED);
            $this->usrMissionStatusRepository->syncModel($usrMissionStatus);
        }
    }
}
