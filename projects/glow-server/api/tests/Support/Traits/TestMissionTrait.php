<?php

declare(strict_types=1);

namespace Tests\Support\Traits;

use App\Domain\Mission\Enums\MissionBeginnerStatus;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Factories\MissionCriterionFactory;
use App\Domain\Mission\MissionManager;
use App\Domain\Mission\Models\Eloquent\UsrMissionEvent;
use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;
use App\Domain\Mission\Models\UsrMissionDailyBonus;
use App\Domain\Mission\Models\UsrMissionInterface;
use App\Domain\Mission\Models\UsrMissionStatus;
use App\Domain\Mission\Services\MissionUpdateHandleService;
use App\Domain\Mission\Utils\MissionUtil;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstMissionAchievement;
use App\Domain\Resource\Mst\Models\MstMissionAchievementDependency;
use App\Domain\Resource\Mst\Models\MstMissionBeginner;
use App\Domain\Resource\Mst\Models\MstMissionDaily;
use App\Domain\Resource\Mst\Models\MstMissionReward;
use App\Domain\Resource\Mst\Models\MstMissionWeekly;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use ReflectionClass;

trait TestMissionTrait
{
    protected MissionUpdateHandleService $missionUpdateHandleService;

    protected function getUsrMissions(
        string $usrUserId,
        MissionType $missionType,
    ): Collection {
        switch ($missionType) {
            case MissionType::ACHIEVEMENT:
            case MissionType::DAILY:
            case MissionType::WEEKLY:
            case MissionType::BEGINNER:
                $query = UsrMissionNormal::query();
                break;
            case MissionType::DAILY_BONUS:
                $query = UsrMissionDailyBonus::query();
                break;

            case MissionType::EVENT:
            case MissionType::EVENT_DAILY:
                $query = UsrMissionEvent::query();
                break;
            case MissionType::LIMITED_TERM:
                $query = UsrMissionLimitedTerm::query();
                break;
            default:
                return collect();
        }

        return $query->where('usr_user_id', $usrUserId)->get()->keyBy->getMstMissionId();
    }

    // TODO: checkUsrMissionStatusを使うように変更したい。checkMissionStatusの内部でcheckUsrMissionStatusを使えば工数軽いかも
    protected function checkMissionStatus(
        Collection $usrMissions,
        string $missionId,
        bool $isExist,
        bool $isClear = false,
        ?CarbonImmutable $now = null,
    ) {
        /** @var UsrMissionInterface $actual */
        $actual = $usrMissions->get($missionId);
        if ($isExist) {
            $this->assertNotNull($actual, 'not null');
            if ($isClear) {
                $this->assertEquals(MissionStatus::CLEAR->value, $actual->getStatus(), 'status');
                $this->assertEquals($now->toDateTimeString(), $actual->getClearedAt(), 'cleared_at');
            } else {
                $this->assertEquals(MissionStatus::UNCLEAR->value, $actual->getStatus(), 'status');
                $this->assertNull($actual->getClearedAt(), 'cleared_at');
            }
        } else {
            $this->assertNull($actual);
        }
    }

    /**
     * 当初checkMissionStatusを使っていたが、報酬受け取りステータスも確認したくなり、このメソッドを途中から追加しました。
     */
    protected function checkUsrMissionStatus(
        Collection $usrMissions,
        string $mstMissionId,
        bool $isExist,
        bool $isClear,
        ?string $clearedAt,
        bool $isReceiveReward,
        ?string $receivedRewardAt
    ): void {
        $targetInfoString = " (mstMissionId: $mstMissionId)";

        /** @var UsrMissionInterface $actual */
        $usrMission = $usrMissions->get($mstMissionId);

        if ($isExist) {
            $this->assertNotNull($usrMission, 'not exist' . $targetInfoString);
        } else {
            $this->assertNull($usrMission, 'exist' . $targetInfoString);
            return;
        }

        if ($isClear) {
            // $this->assertEquals(MissionStatus::CLEAR->value, $usrMission->status, 'clear' . $targetInfoString);
            $this->assertContains($usrMission->status, [MissionStatus::CLEAR->value, MissionStatus::RECEIVED_REWARD->value], 'clear' . $targetInfoString);
            $this->assertEquals($clearedAt, $usrMission->cleared_at, 'clearAt' . $targetInfoString);
        } else {
            $this->assertEquals(MissionStatus::UNCLEAR->value, $usrMission->status, 'unclear' . $targetInfoString);
            $this->assertEquals($clearedAt, $usrMission->cleared_at, 'clearAt' . $targetInfoString);
        }

        if ($isReceiveReward) {
            $this->assertEquals(MissionStatus::RECEIVED_REWARD->value, $usrMission->status, 'reward received' . $targetInfoString);
            $this->assertEquals($receivedRewardAt, $usrMission->received_reward_at, 'receivedRewardAt' . $targetInfoString);
        }
    }

    protected function createMstDependencyEntities(
        string $groupId,
        Collection $mstMissionIds,
    ): Collection
    {
        $result = collect();
        foreach ($mstMissionIds->values() as $idx => $mstMissionId) {
            $result->push(
                MstMissionAchievementDependency::factory()->create([
                    'group_id' => $groupId,
                    'mst_mission_achievement_id' => $mstMissionId,
                    'unlock_order' => $idx + 1,
                ])->toEntity()
            );
        }

        return $result;
    }

    protected function handleAllUpdateTriggeredMissions(
        string $usrUserId,
        CarbonImmutable $now,
    ): void {
        $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $now);
        $this->saveAll();
    }

    protected function createUsrMissionNormal(
        string $usrUserId,
        MissionType $missionType,
        string $mstMissionId,
        MissionStatus $status,
        int $progress,
        null|CarbonImmutable|string $clearedAt,
        null|CarbonImmutable|string $receivedRewardAt,
        null|CarbonImmutable|string $latestResetAt,
        MissionUnlockStatus $unlockStatus = MissionUnlockStatus::OPEN,
        int $unlockProgress = 0,
    ) {
        return UsrMissionNormal::factory()->create([
            'usr_user_id' => $usrUserId,
            'mission_type' => $missionType->getIntValue(),
            'mst_mission_id' => $mstMissionId,
            'status' => $status->value,
            'progress' => $progress,
            'latest_reset_at' => $latestResetAt instanceof CarbonImmutable ? $latestResetAt->toDateTimeString() : $latestResetAt,
            'cleared_at' => $clearedAt instanceof CarbonImmutable ? $clearedAt->toDateTimeString() : $clearedAt,
            'received_reward_at' => $receivedRewardAt instanceof CarbonImmutable ? $receivedRewardAt->toDateTimeString() : $receivedRewardAt,
            'is_open' => $unlockStatus->value,
            'unlock_progress' => $unlockProgress,
        ]);
    }

    /**
     * usr_mission_normalsデータをDBから取得する
     * @param string $usrUserId
     * @param ?MissionType $missionType nullの場合は全ミッションタイプ取得
     * @return Collection<string, UsrMissionNormal> key: mst_mission_id, value: UsrMissionNormal
     */
    protected function getMstMissionIdUsrMissionNormalMap(
        string $usrUserId,
        ?MissionType $missionType = null,
    ): Collection
    {
        $query = UsrMissionNormal::query()->where('usr_user_id', $usrUserId);
        if ($missionType !== null) {
            $query->where('mission_type', $missionType->getIntValue());
        }

        return $query->get()
            ->keyBy(fn($usrMission) => $usrMission->getMstMissionId());
    }

    private function checkUsrMissionNormal(
        ?UsrMissionNormal $usrMission,
        MissionStatus $status,
        int $progress,
        null|CarbonImmutable|string $latestResetAt,
        null|CarbonImmutable|string $clearedAt,
        null|CarbonImmutable|string $receivedRewardAt,
        MissionUnlockStatus $unlockStatus = MissionUnlockStatus::OPEN,
        int $unlockProgress = 0,
    ) {
        if (is_null($usrMission)) {
            $this->fail('usrMission is null');
        }

        $latestResetAt = $latestResetAt instanceof CarbonImmutable ? $latestResetAt->toDateTimeString() : $latestResetAt;
        $clearedAt = $clearedAt instanceof CarbonImmutable ? $clearedAt->toDateTimeString() : $clearedAt;
        $receivedRewardAt = $receivedRewardAt instanceof CarbonImmutable ? $receivedRewardAt->toDateTimeString() : $receivedRewardAt;

        $this->assertEquals($status->value, $usrMission->getStatus(), 'status is not match');
        $this->assertEquals($progress, $usrMission->getProgress(), 'progress is not match');
        $this->assertEquals($latestResetAt, $usrMission->getLatestResetAt(), 'latest_reset_at is not match');
        $this->assertEquals($clearedAt, $usrMission->getClearedAt(), 'cleared_at is not match');
        $this->assertEquals($receivedRewardAt, $usrMission->getReceivedRewardAt(), 'received_reward_at is not match');

        $this->assertEquals($unlockStatus->value, $usrMission->getIsOpen(), 'is_open is not match');
        $this->assertEquals($unlockProgress, $usrMission->getUnlockProgress(), 'unlock_progress is not match');
    }

    private function checkUsrMissionEvent(
        ?UsrMissionEvent $usrMission,
        MissionStatus $status,
        int $progress,
        null|CarbonImmutable|string $latestResetAt,
        null|CarbonImmutable|string $clearedAt,
        null|CarbonImmutable|string $receivedRewardAt,
        MissionUnlockStatus $unlockStatus = MissionUnlockStatus::OPEN,
        int $unlockProgress = 0,
    ) {
        if (is_null($usrMission)) {
            $this->fail('usrMission is null');
        }

        $latestResetAt = $latestResetAt instanceof CarbonImmutable ? $latestResetAt->toDateTimeString() : $latestResetAt;
        $clearedAt = $clearedAt instanceof CarbonImmutable ? $clearedAt->toDateTimeString() : $clearedAt;
        $receivedRewardAt = $receivedRewardAt instanceof CarbonImmutable ? $receivedRewardAt->toDateTimeString() : $receivedRewardAt;

        $this->assertEquals($status->value, $usrMission->getStatus(), 'status is not match');
        $this->assertEquals($progress, $usrMission->getProgress(), 'progress is not match');
        $this->assertEquals($latestResetAt, $usrMission->getLatestResetAt(), 'latest_reset_at is not match');
        $this->assertEquals($clearedAt, $usrMission->getClearedAt(), 'cleared_at is not match');
        $this->assertEquals($receivedRewardAt, $usrMission->getReceivedRewardAt(), 'received_reward_at is not match');

        $this->assertEquals($unlockStatus->value, $usrMission->getIsOpen(), 'is_open is not match');
        $this->assertEquals($unlockProgress, $usrMission->getUnlockProgress(), 'unlock_progress is not match');
    }

    private function checkUsrMissionLimitedTerm(
        ?UsrMissionLimitedTerm $usrMission,
        MissionStatus $status,
        int $progress,
        null|CarbonImmutable|string $latestResetAt,
        null|CarbonImmutable|string $clearedAt,
        null|CarbonImmutable|string $receivedRewardAt,
        MissionUnlockStatus $unlockStatus = MissionUnlockStatus::OPEN,
    ) {
        if (is_null($usrMission)) {
            $this->fail('usrMission is null');
        }

        $latestResetAt = $latestResetAt instanceof CarbonImmutable ? $latestResetAt->toDateTimeString() : $latestResetAt;
        $clearedAt = $clearedAt instanceof CarbonImmutable ? $clearedAt->toDateTimeString() : $clearedAt;
        $receivedRewardAt = $receivedRewardAt instanceof CarbonImmutable ? $receivedRewardAt->toDateTimeString() : $receivedRewardAt;

        $this->assertEquals($status->value, $usrMission->getStatus(), 'status is not match');
        $this->assertEquals($progress, $usrMission->getProgress(), 'progress is not match');
        $this->assertEquals($latestResetAt, $usrMission->getLatestResetAt(), 'latest_reset_at is not match');
        $this->assertEquals($clearedAt, $usrMission->getClearedAt(), 'cleared_at is not match');
        $this->assertEquals($receivedRewardAt, $usrMission->getReceivedRewardAt(), 'received_reward_at is not match');

        $this->assertEquals($unlockStatus->value, $usrMission->getIsOpen(), 'is_open is not match');
    }

    // /**
    //  * デイリーミッションが現在日時でリセットされない日時を取得
    //  * @param \Carbon\CarbonImmutable $now
    //  * @return string
    //  */
    // public function getDailyNotResetNextResetAt(CarbonImmutable $now): string
    // {
    //     return $now->addDay()->format(Clock::DAY_START_FORMAT);
    // }

    // /**
    //  * 現在日時で処理したのちに、次回デイリーリセットする日時を取得
    //  * @param \Carbon\CarbonImmutable $now
    //  * @return string
    //  */
    // public function getDailyNextResetAt(CarbonImmutable $now): string
    // {
    //     return $now->addDay()->format(Clock::DAY_START_FORMAT);
    // }

    // public function getDailyResettableAt(CarbonImmutable $now): string
    // {
    //     return $now->subDay()->format(Clock::DAY_START_FORMAT);
    // }

    // public function getWeeklyNotResetNextResetAt(CarbonImmutable $now): string
    // {
    //     return $now->addWeek()->format(Clock::DAY_START_FORMAT);
    // }

    // public function getWeeklyNextResetAt(CarbonImmutable $now): string
    // {
    //     // TODO: テストに直接関係ないクラスにテストを依存させたくない。他の良い方法あれば対応する
    //     return app(Clock::class)->getNextWeekStartAt($now)->format(Clock::DAY_START_FORMAT);
    // }

    // public function getWeeklyResettableAt(CarbonImmutable $now): string
    // {
    //     return $now->subWeek()->format(Clock::DAY_START_FORMAT);
    // }

    // /**
    //  * usr_missionsのnext_reset_atに設定して、次回リセットされる日時を取得
    //  */
    // public function getNextResetAt(MissionType $missionType, CarbonImmutable $now): ?string
    // {
    //     switch ($missionType) {
    //         case MissionType::DAILY:
    //             return $this->getDailyNextResetAt($now);
    //         case MissionType::WEEKLY:
    //             return $this->getWeeklyNextResetAt($now);
    //         default:
    //             return null;
    //     }
    // }

    /**
     * usr_missionsのnext_reset_atに設定して、リセットされる日時を取得
     */
    public function getResettableAt(MissionType $missionType, CarbonImmutable $now): ?string
    {
        switch ($missionType) {
            case MissionType::DAILY:
                return $now->subDay()->toDateTimeString();
            case MissionType::WEEKLY:
                return $now->subWeek()->toDateTimeString();
            default:
                return null;
        }
    }

    public function getMstFactory(MissionType $missionType)
    {
        return match ($missionType) {
            MissionType::ACHIEVEMENT => MstMissionAchievement::factory(),
            MissionType::DAILY => MstMissionDaily::factory(),
            MissionType::WEEKLY => MstMissionWeekly::factory(),
            MissionType::BEGINNER => MstMissionBeginner::factory(),
            default => $this->fail("invalid missionType: {$missionType->value}"),
        };
    }

    /**
     * 初心者ミッションは、ミッション機能解放されていない場合は、進捗管理をしないので
     * 進捗管理を行うためのユーザーデータを準備する
     *
     * @param string $usrUserId
     * @param mixed $missionUnlockedAt
     * @return void
     */
    public function prepareUpdateBeginnerMission(
        string $usrUserId,
        ?string $missionUnlockedAt = null,
    ): void {
        if (is_null($missionUnlockedAt)) {
            // 初心者ミッションが全開放される想定の日時（現在から10年以上前）をデフォルトとする
            $missionUnlockedAt = '2010-01-01 00:00:00';
        }

        UsrMissionStatus::factory()->create([
            'usr_user_id' => $usrUserId,
            'beginner_mission_status' => MissionBeginnerStatus::HAS_LOCKED->value,
            'mission_unlocked_at' => $missionUnlockedAt,
        ]);
    }

    public function createMstMission(
        MissionType $missionType,
        string $mstMissionId,
        MissionCriterionType $criterionType,
        ?string $criterionValue,
        int $criterionCount,
        ?string $rewardGroupId = null,
        int $bonusPoint = 0,
        ?string $groupKey = null,
        int $beginnerUnlockDay = 0,
        ?MissionCriterionType $unlockCriterionType = null,
        ?string $unlockCriterionValue = null,
        int $unlockCriterionCount = 0,
    ) {
        if (is_null($rewardGroupId)) {
            $rewardGroupId = ''; // DB列ではnot nullなので空文字で未指定とする
        }

        return match ($missionType) {
            MissionType::ACHIEVEMENT => MstMissionAchievement::factory()->create([
                'id' => $mstMissionId,
                'criterion_type' => $criterionType->value,
                'criterion_value' => $criterionValue,
                'criterion_count' => $criterionCount,
                'mst_mission_reward_group_id' => $rewardGroupId,
                'group_key' => $groupKey,
                'unlock_criterion_type' => $unlockCriterionType?->value ?? '',
                'unlock_criterion_value' => $unlockCriterionValue,
                'unlock_criterion_count' => $unlockCriterionCount,
            ]),
            MissionType::DAILY => MstMissionDaily::factory()->create([
                'id' => $mstMissionId,
                'criterion_type' => $criterionType->value,
                'criterion_value' => $criterionValue,
                'criterion_count' => $criterionCount,
                'mst_mission_reward_group_id' => $rewardGroupId,
                'bonus_point' => $bonusPoint,
                'group_key' => $groupKey,
            ]),
            MissionType::WEEKLY => MstMissionWeekly::factory()->create([
                'id' => $mstMissionId,
                'criterion_type' => $criterionType->value,
                'criterion_value' => $criterionValue,
                'criterion_count' => $criterionCount,
                'mst_mission_reward_group_id' => $rewardGroupId,
                'bonus_point' => $bonusPoint,
                'group_key' => $groupKey,
            ]),
            MissionType::BEGINNER => MstMissionBeginner::factory()->create([
                'id' => $mstMissionId,
                'criterion_type' => $criterionType->value,
                'criterion_value' => $criterionValue,
                'criterion_count' => $criterionCount,
                'mst_mission_reward_group_id' => $rewardGroupId,
                'bonus_point' => $bonusPoint,
                'group_key' => $groupKey,
                'unlock_day' => $beginnerUnlockDay,
            ]),
            default => $this->fail("invalid missionType: {$missionType->value}"),
        };
    }

    public function createMstReward(
        string $groupId,
        RewardType $resourceType,
        ?string $resourceId,
        int $resourceAmount,
    ) {
        return MstMissionReward::factory()->create([
            'group_id' => $groupId,
            'resource_type' => $resourceType->value,
            'resource_id' => $resourceId,
            'resource_amount' => $resourceAmount,
        ]);
    }

    /**
     * MissionManagerに登録されたトリガーを取得
     */
    public function getMissionManagerTriggers(): Collection
    {
        $missionManager = app(MissionManager::class);

        $reflectionClass = new ReflectionClass($missionManager);
        $property = $reflectionClass->getProperty('triggers');
        $property->setAccessible(true);
        return $property->getValue($missionManager);
    }

    /**
     * 指定されたCriterionを使って、トリガーの進捗値を集約した値を取得
     */
    public function aggregateCriterionProgress(Collection $triggers, MissionCriterionType $criterionType, ?string $criterionValue): int
    {
        // TODO: テストが具体実装に依存しているので、MissionCriterionFactoryのロジック変更時は注意
        $criterion = app(MissionCriterionFactory::class)->createMissionCriterion($criterionType->value, $criterionValue);

        foreach ($triggers as $trigger) {
            if ($trigger->getCriterionKey() !== $criterion->getCriterionKey()) {
                continue;
            }
            $criterion->aggregateProgress($trigger->getProgress());
        }

        return $criterion->getProgress();
    }

    /**
     * MissionManagerに登録されたトリガーを取得し、指定されたCriterionに関する進捗値を集約し、その値が想定通りかを確認する
     */
    public function checkTriggerAndAggregatedProgress(
        MissionCriterionType $criterionType,
        ?string $criterionValue,
        int $expectedProgress,
        bool $isExist=True,
        ?array $targetMissionTypes=null, // nullの場合は全ミッションタイプを対象とする。指定があれば、指定ミッションタイプのみ対象とする
    ): void {
        $missionTypeTriggersMap = $this->getMissionManagerTriggers();
        $this->assertNotEmpty($missionTypeTriggersMap, 'missionTypeTriggersMap is empty.');

        foreach ($missionTypeTriggersMap as $missionType => $triggers) {
            $triggers = $triggers->groupBy(fn($trigger) => $trigger->getCriterionKey());

            $messageSuffix = sprintf('(%s:%s-%s)', $missionType, $criterionType->value, $criterionValue);

            $criterionKey = MissionUtil::makeCriterionKey($criterionType->value, $criterionValue);
            $targetTriggers = $triggers->get($criterionKey);

            // 対象外のミッションタイプであれば、指定トリガーがないことを確認
            if ($targetMissionTypes !== null && !in_array($missionType, $targetMissionTypes)) {
                $this->assertNull($targetTriggers, 'targetTriggers is not null. ' . $messageSuffix);
                continue;
            }

            // 存在するトリガーかどうか指定されている場合、その通りかを確認
            if ($isExist) {
                $this->assertNotNull($targetTriggers, 'targetTriggers is null. ' . $messageSuffix);
            } else {
                // 想定通り存在しない場合は、次のミッションタイプの確認に進む
                $this->assertNull($targetTriggers, 'targetTriggers is not null. ' . $messageSuffix);
                continue;
            }

            // 集約した進捗値が想定通りかを確認
            $this->assertEquals(
                $expectedProgress,
                $this->aggregateCriterionProgress($targetTriggers, $criterionType, $criterionValue),
                'aggregated progress is not expected. ' . $messageSuffix
            );
        }
    }

    /**
     * UseCaseTestでcheckTriggerAndAggregatedProgressメソッドを使って、トリガー進捗値の集約値を確認したい場合に使う。
     *
     * トリガーは、UseCase実行時に、UseCaseTrait内のミッション進捗更新処理(handleAllUpdateTriggeredMissions)で、MissionManagerから削除される。
     * MissionManagerに登録されたトリガーをそのまま残しておくために、handleAllUpdateTriggeredMissionsの処理を実行しないようにモックする。
     */
    public function mockExecHandleAllUpdateTriggeredMissions(): void
    {
        // mockして進捗判定を進めないようにする
        // useCaseテストを使ったテストの場合、UseCaseTraitでのミッション進捗更新が実行されMissionManagerに入ったトリガーが消えてしまうため
        $this->mock(MissionUpdateHandleService::class)
            ->makePartial()
            ->shouldReceive('handleAllUpdateTriggeredMissions')
            ->andReturn();
    }

    /**
     * MissionManagerに登録されたトリガーが存在するかどうかを確認。
     * 何もトリガーされていないことを確認したい場合などに使う。
     */
    public function checkExistMissionManagerTriggers(bool $isExist): void
    {
        $missionManagerTriggers = $this->getMissionManagerTriggers();
        if ($isExist) {
            $this->assertNotEmpty($missionManagerTriggers, 'missionManagerTriggers is empty.');
        } else {
            $this->assertEmpty($missionManagerTriggers, 'missionManagerTriggers is not empty.');
        }
    }
}
