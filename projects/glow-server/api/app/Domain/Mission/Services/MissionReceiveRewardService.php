<?php

declare(strict_types=1);

namespace App\Domain\Mission\Services;

use App\Domain\Common\Entities\Clock;
use App\Domain\Mission\Entities\MissionReceiveRewardStatus;
use App\Domain\Mission\Entities\MissionUpdateBundle;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Factories\MissionCriterionFactory;
use App\Domain\Mission\MissionManager;
use App\Domain\Mission\Models\IUsrMission;
use App\Domain\Mission\Models\UsrMissionNormalInterface;
use App\Domain\Mission\Repositories\UsrMissionEventRepository;
use App\Domain\Mission\Repositories\UsrMissionLimitedTermRepository;
use App\Domain\Mission\Repositories\UsrMissionNormalRepository;
use App\Domain\Mission\Repositories\UsrMissionStatusRepository;
use App\Domain\Resource\Enums\UnreceivedRewardReason;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityInterface;
use App\Domain\Resource\Mst\Repositories\MstEventRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionAchievementRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionBeginnerRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionDailyRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionEventDailyRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionEventRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionLimitedTermRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionRewardRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionWeeklyRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MissionReceiveRewardService
{
    public function __construct(
        protected Clock $clock,
        protected MissionManager $missionManager,
        // MstRepository
        protected MstMissionRewardRepository $mstMissionRewardRepository,
        private MstMissionAchievementRepository $mstMissionAchievementRepository,
        private MstMissionBeginnerRepository $mstMissionBeginnerRepository,
        private MstMissionDailyRepository $mstMissionDailyRepository,
        private MstMissionWeeklyRepository $mstMissionWeeklyRepository,
        private MstEventRepository $mstEventRepository,
        private MstMissionEventRepository $mstMissionEventRepository,
        private MstMissionEventDailyRepository $mstMissionEventDailyRepository,
        private MstMissionLimitedTermRepository $mstMissionLimitedTermRepository,
        // UsrRepository
        protected UsrMissionNormalRepository $usrMissionNormalRepository,
        private UsrMissionEventRepository $usrMissionEventRepository,
        private UsrMissionLimitedTermRepository $usrMissionLimitedTermRepository,
        protected UsrMissionStatusRepository $usrMissionStatusRepository,
        // Service
        protected MissionRewardService $missionRewardService,
        private MissionUpdateService $missionUpdateService,
        protected MissionCriterionFactory $missionCriterionFactory,
        protected MissionStatusService $missionStatusService,
        // Delegator
        protected RewardDelegator $rewardDelegator,
    ) {
    }

    /**
     * 報酬受取可の全ミッションの報酬を一括で受け取る
     *
     * @param Collection<string> $mstMissionIds ミッションマスタIDの配列
     * @return Collection<MissionReceiveRewardStatus>
     */
    public function bulkReceiveReward(
        string $usrUserId,
        CarbonImmutable $now,
        int $platform,
        MissionType $missionType,
        Collection $mstMissionIds,
    ): Collection {
        if ($mstMissionIds->isEmpty()) {
            return collect();
        }

        /**
         * 受け取り処理対象となったミッションマスタIDの配列。
         * ボーナスポイントの自動受け取りをした場合はIDをこの配列に追加する。
         */
        $responseMstMissionIds = $mstMissionIds->unique();
        // 必要ならボーナスポイントのマスタも含めて取得する
        $mstMissions = $this->getMstMissions($missionType, $responseMstMissionIds, $now);

        // 有効なマスタデータがなかった場合は、全て受取不可として返す
        if ($mstMissions->isEmpty()) {
            return $this->createMissionReceiveRewardStatuses(
                $missionType,
                $responseMstMissionIds,
                $mstMissions,
                collect(),
            );
        }

        // 受け取り確認対象のユーザーミッションデータ配列
        $usrMissions = $this->getUsrMissions($usrUserId, $missionType, $mstMissions->keys());

        // 進捗リセット処理
        $usrMissions = $this->missionUpdateService->resetUsrMissionsByMissionType(
            $missionType,
            $usrMissions,
            $mstMissions,
            $now,
        );

        // 受取可のユーザーミッションデータを抽出
        $receivableUsrMissions = $usrMissions
            ->filter(fn (IUsrMission $usrMission) => $usrMission->canReceiveReward());

        // 受取可ミッションがない場合は終了
        if ($receivableUsrMissions->isEmpty()) {
            return $this->createMissionReceiveRewardStatuses(
                $missionType,
                $responseMstMissionIds,
                $mstMissions,
                $usrMissions,
            );
        }

        // ボーナスポイント付与と宝箱受取
        $changedBonusPointUsrMissions = $this->updateBonusPoint(
            $usrUserId,
            $now,
            $missionType,
            $mstMissions,
            $usrMissions,
        );
        foreach ($changedBonusPointUsrMissions as $usrMission) {
            $mstMissionId = $usrMission->getMstMissionId();
            // 進捗変動があったもののうちで、初クリアしたもののみを対象とする
            if ($usrMission->isClear()) {
                // 受取処理対象に追加
                $responseMstMissionIds->push($mstMissionId);
                // 報酬配布対象に追加
                $receivableUsrMissions->put($mstMissionId, $usrMission);
                // 受取対象ミッションに追加
                $usrMissions->put($mstMissionId, $usrMission);
            }
        }

        // 報酬配布
        $rewards = $this->missionRewardService->calcRewards(
            $missionType,
            $mstMissions,
            $receivableUsrMissions,
        );
        $this->rewardDelegator->addRewards($rewards);

        // レスポンス用の受け取り状態インスタンスを生成
        // 受取済へ更新する前に実行する。そうしない場合はレスポンスに含まれなくなってしまう。
        $receiveRewardStatuses = $this->createMissionReceiveRewardStatuses(
            $missionType,
            $responseMstMissionIds,
            $mstMissions,
            $usrMissions,
        );

        // ユーザーデータ更新して受取済にする
        $this->updateToReceived($missionType, $receivableUsrMissions, $now);

        return $receiveRewardStatuses;
    }
    /**
     * 引数指定された$mstMissionIdsのミッションに対して、受け取り状態を表現するインスタンス(MissionReceiveRewardStatus)を生成する
     *
     * $mstMissionIdsはリクエストパラメータ由来のIDを想定していて、
     * マスタデータとして存在しないような不正なデータも含まれる可能性があることを想定している。
     *
     * 存在しないマスタの場合でもエラーは出さず、UnreceivedRewardReason::INVALID_DATA としてレスポンスする。
     *
     * @param Collection<string, MstMissionEntityInterface> $mstMissions key: mst_mission_id
     * @param Collection<string, IUsrMission> $usrMissions key: mst_mission_id
     * @return Collection<MissionReceiveRewardStatus>
     */
    private function createMissionReceiveRewardStatuses(
        MissionType $missionType,
        Collection $mstMissionIds,
        Collection $mstMissions,
        Collection $usrMissions,
    ): Collection {
        $result = collect();

        $targetMstMissionIds = $mstMissionIds->unique();

        foreach ($targetMstMissionIds as $mstMissionId) {
            $mstMission = $mstMissions->get($mstMissionId);
            $usrMission = $usrMissions->get($mstMissionId);

            // ボーナスポイントミッションで自動受取対象ではないデータはレスポンスに含めない
            if ($mstMission?->isBonusPointMission() && $usrMission?->canReceiveReward() === false) {
                continue;
            }

            if (
                $mstMission === null
                || $usrMission === null
                || $usrMission->canReceiveReward() === false
            ) {
                // 受け取りしなかったデータ
                $result->push(
                    new MissionReceiveRewardStatus(
                        $missionType,
                        $mstMissionId,
                        UnreceivedRewardReason::INVALID_DATA,
                    )
                );
                continue;
            }

            // 受取済データ
            $result->push(
                new MissionReceiveRewardStatus(
                    MissionType::getFromInt($usrMission->getMissionType()),
                    $mstMissionId,
                    null,
                )
            );
        }

        return $result;
    }

    /**
     * ミッションボーナスポイントを付与する
     * ボーナスポイントはミッションとして実装しているが、トリガー送信をせずに、直接進捗更新処理を実行する
     *
     * @param Collection<string, MstMissionEntityInterface> $mstMissions key: mst_mission_id
     * @param Collection<string, IUsrMission> $usrMissions key: mst_mission_id
     * @return Collection<string, IUsrMission> key: mst_mission_id 変動があったボーナスポイントミッションのユーザーデータ
     */
    public function updateBonusPoint(
        string $usrUserId,
        CarbonImmutable $now,
        MissionType $missionType,
        Collection $mstMissions,
        Collection $usrMissions,
    ): Collection {
        [$bonusPointMstMissions, $receiveMstMissions] = $mstMissions
            ->partition(function (MstMissionEntityInterface $mstMission) {
                return $mstMission->getCriterionType() === MissionCriterionType::MISSION_BONUS_POINT->value;
            });

        if ($bonusPointMstMissions->isEmpty()) {
            return collect();
        }

        // 新規で獲得したボーナスポイントを算出
        // ボーナスポイントミッションに設定されているボーナスポイントは付与しない
        $totalBonusPoint = 0;
        foreach ($usrMissions as $usrMission) {
            /** @var IUsrMission $usrMission */

            $mstMissionId = $usrMission->getMstMissionId();

            /** @var ?MstMissionEntityInterface $mstMission */
            $mstMission = $receiveMstMissions->get($mstMissionId);

            if (
                // 受取不可のミッションは対象外
                $usrMission->canReceiveReward() === false
                // マスタデータがない不正なデータなので対象外
                || $mstMission === null
            ) {
                continue;
            }

            // 0未満の場合は加算しないように下限を0とする
            $totalBonusPoint += max(0, $mstMission->getBonusPoint());
        }

        // ボーナスポイントミッションの進捗更新用のCriterionを生成
        $criterion = $this->missionCriterionFactory->createMissionBonusPointCriterion(
            $totalBonusPoint,
        );

        $states = $this->missionUpdateService->createStates(
            new MissionUpdateBundle(
                $missionType,
                $bonusPointMstMissions,
                collect(),
                collect([
                    $criterion->getCriterionKey() => $criterion,
                ]),
                $usrMissions->only($bonusPointMstMissions->keys()),
            ),
        );

        // ボーナスポイントミッションの進捗更新判定
        foreach ($states as $state) {
            $state->checkAndUpdate();
        }

        // ユーザーデータ更新
        $this->missionUpdateService->updateUsrMission(
            $usrUserId,
            $now,
            $missionType->getIntValue(),
            $states,
        );

        // 変動があったボーナスポイントミッションのユーザーデータを返す
        return $this->usrMissionNormalRepository->getChangedModels()
            ->filter(function (UsrMissionNormalInterface $usrMission) use ($bonusPointMstMissions) {
                return $bonusPointMstMissions->has($usrMission->getMstMissionId());
            });
    }

    /**
     * 指定されたidのマスタデータを取得する
     * ボーナスポイントがあるミッションタイプの場合は、ボーナスポイントのマスタデータも合わせて取得する
     *
     * @param \App\Domain\Mission\Enums\MissionType $missionType
     * @param \Illuminate\Support\Collection $mstMissionIds
     * @param \Carbon\CarbonImmutable $now
     * @return Collection<string, MstMissionEntityInterface> key: mst_mission_id
     */
    private function getMstMissions(
        MissionType $missionType,
        Collection $mstMissionIds,
        CarbonImmutable $now,
    ): Collection {
        switch ($missionType) {
            case MissionType::ACHIEVEMENT:
                return $this->mstMissionAchievementRepository->getByIds($mstMissionIds);
            case MissionType::BEGINNER:
                return $this->mstMissionBeginnerRepository->getByIdsAndBonusPoints($mstMissionIds);
            case MissionType::DAILY:
                return $this->mstMissionDailyRepository->getByIdsAndBonusPoints($mstMissionIds);
            case MissionType::WEEKLY:
                return $this->mstMissionWeeklyRepository->getByIdsAndBonusPoints($mstMissionIds);
            case MissionType::EVENT:
                $mstEventIds = $this->mstEventRepository->getAllActiveEvents($now)->keys();
                return $this->mstMissionEventRepository->getByIdsAndMstEventIds($mstMissionIds, $mstEventIds);
            case MissionType::EVENT_DAILY:
                $mstEventIds = $this->mstEventRepository->getAllActiveEvents($now)->keys();
                return $this->mstMissionEventDailyRepository->getByIdsAndMstEventIds($mstMissionIds, $mstEventIds);
            case MissionType::LIMITED_TERM:
                return $this->mstMissionLimitedTermRepository->getActivesByIds($mstMissionIds, $now);
            default:
                return collect();
        }
    }

    /**
     * @return Collection<string, IUsrMission> string: mst_mission_id
     */
    private function getUsrMissions(
        string $usrUserId,
        MissionType $missionType,
        Collection $mstMissionIds,
    ): Collection {
        switch ($missionType) {
            case MissionType::ACHIEVEMENT:
                return $this->usrMissionNormalRepository->getByMstMissionIds(
                    $usrUserId,
                    mstMissionAchievementIds: $mstMissionIds->all(),
                )->getByMissionType($missionType);
            case MissionType::BEGINNER:
                return $this->usrMissionNormalRepository->getByMstMissionIds(
                    $usrUserId,
                    mstMissionBeginnerIds: $mstMissionIds->all(),
                )->getByMissionType($missionType);
            case MissionType::DAILY:
                return $this->usrMissionNormalRepository->getByMstMissionIds(
                    $usrUserId,
                    mstMissionDailyIds: $mstMissionIds->all(),
                )->getByMissionType($missionType);
            case MissionType::WEEKLY:
                return $this->usrMissionNormalRepository->getByMstMissionIds(
                    $usrUserId,
                    mstMissionWeeklyIds: $mstMissionIds->all(),
                )->getByMissionType($missionType);

            case MissionType::EVENT:
                return $this->usrMissionEventRepository->getByMstMissionIds(
                    $usrUserId,
                    mstMissionEventIds: $mstMissionIds->all(),
                )->getByMissionType($missionType);
            case MissionType::EVENT_DAILY:
                return $this->usrMissionEventRepository->getByMstMissionIds(
                    $usrUserId,
                    mstMissionEventDailyIds: $mstMissionIds->all(),
                )->getByMissionType($missionType);

            case MissionType::LIMITED_TERM:
                return $this->usrMissionLimitedTermRepository->getByMstMissionIds($usrUserId, $mstMissionIds);

            default:
                return collect();
        }
    }

    /**
     * ユーザーデータ更新して受取済にする
     * @param Collection<IUsrMission> $usrMissions
     */
    private function updateToReceived(
        MissionType $missionType,
        Collection $usrMissions,
        CarbonImmutable $now,
    ): void {
        $receiveds = collect();
        foreach ($usrMissions as $usrMission) {
            if ($usrMission->canReceiveReward() === false) {
                continue;
            }
            /** @var IUsrMission $usrMission */
            $usrMission->receiveReward($now);
            $receiveds->push($usrMission);
        }

        // ミッションタイプごとに更新するユーザーテーブルを切り替える
        match ($missionType) {
            MissionType::ACHIEVEMENT,
            MissionType::BEGINNER,
            MissionType::DAILY,
            MissionType::WEEKLY => $this->usrMissionNormalRepository->syncModels($receiveds),
            MissionType::EVENT,
            MissionType::EVENT_DAILY => $this->usrMissionEventRepository->syncModels($receiveds),
            MissionType::LIMITED_TERM => $this->usrMissionLimitedTermRepository->syncModels($receiveds),
            default => null,
        };
    }
}
