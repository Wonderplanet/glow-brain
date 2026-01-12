<?php

declare(strict_types=1);

namespace App\Domain\Mission\Services;

use App\Domain\Common\Entities\Clock;
use App\Domain\Mission\Enums\MissionBeginnerStatus;
use App\Domain\Mission\Models\UsrMissionStatusInterface;
use App\Domain\Mission\Repositories\UsrMissionNormalRepository;
use App\Domain\Mission\Repositories\UsrMissionStatusRepository;
use App\Domain\Resource\Mng\Services\MngMasterReleaseService;
use App\Domain\Resource\Mst\Repositories\MstMissionBeginnerRepository;
use Carbon\CarbonImmutable;

class MissionStatusService
{
    public function __construct(
        private MstMissionBeginnerRepository $mstMissionBeginnerRepository,
        private UsrMissionStatusRepository $usrMissionStatusRepository,
        private UsrMissionNormalRepository $usrMissionNormalRepository,
        // Other
        private Clock $clock,
        private MngMasterReleaseService $mngMasterReleaseService,
    ) {
    }

    /**
     * 全ての初心者ミッションの報酬を受取済のとき、初心者ミッションを完了状態にする。
     * 完了状態にすることで、今後の処理で、初心者ミッションの進捗更新処理をスキップできる。
     */
    public function completeBeginnerMission(string $usrUserId): void
    {
        $usrMissionStatus = $this->usrMissionStatusRepository->getOrCreate($usrUserId);
        if (
            $usrMissionStatus->isBeginnerMissionCompleted()
            || $usrMissionStatus->isBeginnerMissionFullyUnlocked() === false
        ) {
            // 既に完了済み または まだ全開放されていないので、全受取済チェックは不要
            return;
        }

        $mstMissionBeginners = $this->mstMissionBeginnerRepository->getMapAll();
        if ($mstMissionBeginners->isEmpty()) {
            // 初心者ミッションのマスタデータが存在しないなら、ステータス更新しない
            return;
        }

        $rewardReceivedCount = $this->usrMissionNormalRepository
            ->getBeginnerReceivedRewardCountByMstMissionIds(
                $usrUserId,
                $mstMissionBeginners->keys(),
            );

        if ($mstMissionBeginners->count() === $rewardReceivedCount) {
            $usrMissionStatus->setBeginnerMissionStatus(MissionBeginnerStatus::COMPLETED);
            $this->usrMissionStatusRepository->syncModel($usrMissionStatus);
        }
    }

    public function isBeginnerMissionCompleted(string $usrUserId): bool
    {
        $usrMissionStatus = $this->usrMissionStatusRepository->get($usrUserId);
        if (is_null($usrMissionStatus)) {
            return false;
        }
        return $usrMissionStatus->isBeginnerMissionCompleted();
    }

    private function isBeginnerMissionLocked(?UsrMissionStatusInterface $usrMissionStatus): bool
    {
        return is_null($usrMissionStatus) || is_null($usrMissionStatus->getMissionUnlockedAt());
    }

    /**
     * 初心者ミッションの進捗判定が必要かどうか
     * true: 進捗判定が必要, false: 進捗判定が不要
     */
    public function isBeginnerMissionUpdateRequired(?UsrMissionStatusInterface $usrMissionStatus): bool
    {
        if ($this->isBeginnerMissionLocked($usrMissionStatus)) {
            // ミッション機能未解放の場合は、初心者ミッション未解放とみなして、進捗判定不要
            return false;
        }

        // 初心者ミッションが未完了の場合、進捗判定が必要
        return $usrMissionStatus->isBeginnerMissionCompleted() === false;
    }

    /**
     * ミッション機能解放からの経過日数を計算する
     */
    public function calcDaysFromMissionUnlockedAt(?UsrMissionStatusInterface $usrMissionStatus): int
    {
        if ($this->isBeginnerMissionLocked($usrMissionStatus)) {
            // ミッション機能未解放の場合は、0日目として、初心者ミッションを開放させない
            return 0;
        }

        return $this->clock->diffDays($usrMissionStatus->getMissionUnlockedAt()) + 1;
    }

    /**
     * マスタデータハッシュを比較して、即時達成判定が必要かどうかを返す
     * 基本的に判定は行いつつ、
     * 前回判定時とマスタデータが全く同じ場合は、判定不要とする。
     *
     * true: 必要, false: 不要
     */
    public function needInstantClear(string $usrUserId, CarbonImmutable $now): bool
    {
        $mstHash = $this->getMstHash($now);
        if (is_null($mstHash)) {
            return true;
        }

        // 即時判定の初回とみなし、判定必要
        $usrMissionStatus = $this->usrMissionStatusRepository->get($usrUserId);
        if (is_null($usrMissionStatus)) {
            return true;
        }

        // マスタデータハッシュを比較して、判定必要かどうかを返す
        return $usrMissionStatus->needInstantClear($mstHash);
    }

    /**
     * 即時達成判定を実行したタイミングのマスタデータハッシュを更新する
     */
    public function updateLatestMstHash(string $usrUserId, CarbonImmutable $now): void
    {
        $mstHash = $this->getMstHash($now);
        if (is_null($mstHash)) {
            // データがないと更新できないので、何もしない
            return;
        }

        $usrMissionStatus = $this->usrMissionStatusRepository->getOrCreate($usrUserId);
        $usrMissionStatus->setLatestMstHash($mstHash);
    }

    /**
     * マスタデータに変更があった場合のみ即時達成判定を行うために、マスタデータハッシュを取得する
     */
    private function getMstHash(CarbonImmutable $now): ?string
    {
        try {
            $versionEntity = $this->mngMasterReleaseService->getMngMasterReleaseVersionEntityByConfigDatabase($now);
        } catch (\Exception $e) {
            return null;
        }

        return $versionEntity->getClientMstDataHash();
    }
}
