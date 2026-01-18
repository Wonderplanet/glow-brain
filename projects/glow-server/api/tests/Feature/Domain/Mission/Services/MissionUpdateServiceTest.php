<?php

namespace Tests\Feature\Domain\Mission;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Enums\MissionBeginnerStatus;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionLimitedTermCategory;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\MissionManager;
use App\Domain\Mission\Models\Eloquent\UsrMissionEvent;
use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;
use App\Domain\Mission\Models\UsrMissionStatus;
use App\Domain\Mission\Services\MissionUpdateService;
use App\Domain\Resource\Mst\Models\MstEvent;
use App\Domain\Resource\Mst\Models\MstMissionAchievement;
use App\Domain\Resource\Mst\Models\MstMissionBeginner;
use App\Domain\Resource\Mst\Models\MstMissionDaily;
use App\Domain\Resource\Mst\Models\MstMissionEvent;
use App\Domain\Resource\Mst\Models\MstMissionEventDaily;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTerm;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTermDependency;
use App\Domain\Resource\Mst\Models\MstMissionWeekly;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class MissionUpdateServiceTest extends TestCase
{
    use TestMissionTrait;

    private MissionUpdateService $missionUpdateService;
    private MissionManager $missionManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->missionUpdateService = $this->app->make(MissionUpdateService::class);
        $this->missionManager = $this->app->make(MissionManager::class);
    }

    private function checkUsrMission(
        null|UsrMissionNormal|UsrMissionEvent $usrMission,
        int $status,
        int $progress,
        string $latestResetAt,
        ?string $clearedAt,
        ?string $receivedRewardAt,
    ) {
        if (is_null($usrMission)) {
            $this->fail('usrMission is null');
        }

        $this->assertEquals($status, $usrMission->getStatus(), 'status is not match');
        $this->assertEquals($progress, $usrMission->getProgress(), 'progress is not match');
        $this->assertEquals($latestResetAt, $usrMission->getLatestResetAt(), 'latest_reset_at is not match');
        $this->assertEquals($clearedAt, $usrMission->getClearedAt(), 'cleared_at is not match');
        $this->assertEquals($receivedRewardAt, $usrMission->getReceivedRewardAt(), 'received_reward_at is not match');
    }

    private function createMstMission(
        string $id,
        MissionType $missionType,
        MissionCriterionType $criterionType,
        ?string $criterionValue,
        int $criterionCount,
        ?string $groupKey = null,
        int $beginnerUnlockDay = 0,
    ) {
        switch ($missionType) {
            case MissionType::DAILY:
                return MstMissionDaily::factory()->create([
                    'id' => $id,
                    'criterion_type' => $criterionType->value,
                    'criterion_value' => $criterionValue,
                    'criterion_count' => $criterionCount,
                    'group_key' => $groupKey,
                ]);
            case MissionType::WEEKLY:
                return MstMissionWeekly::factory()->create([
                    'id' => $id,
                    'criterion_type' => $criterionType->value,
                    'criterion_value' => $criterionValue,
                    'criterion_count' => $criterionCount,
                    'group_key' => $groupKey,
                ]);
            case MissionType::ACHIEVEMENT:
                return MstMissionAchievement::factory()->create([
                    'id' => $id,
                    'criterion_type' => $criterionType->value,
                    'criterion_value' => $criterionValue,
                    'criterion_count' => $criterionCount,
                    'group_key' => $groupKey,
                ]);
            case MissionType::BEGINNER:
                return MstMissionBeginner::factory()->create([
                    'id' => $id,
                    'criterion_type' => $criterionType->value,
                    'criterion_value' => $criterionValue,
                    'criterion_count' => $criterionCount,
                    'group_key' => $groupKey,
                    'unlock_day' => $beginnerUnlockDay,
                ]);
            case MissionType::EVENT_DAILY:
                return MstMissionEventDaily::factory()->create([
                    'id' => $id,
                    'mst_event_id' => 'event1',
                    'criterion_type' => $criterionType->value,
                    'criterion_value' => $criterionValue,
                    'criterion_count' => $criterionCount,
                    'group_key' => $groupKey,
                ]);
            default:
                throw new \Exception('not support missionType');
        }
    }

    private function createUsrMission(
        string $usrUserId,
        MissionType $missionType,
        string $mstMissionId,
        MissionStatus $status,
        int $progress,
        ?string $clearedAt,
        ?string $receivedRewardAt,
        string $latestResetAt,
    ) {
        switch ($missionType) {
            case MissionType::ACHIEVEMENT:
            case MissionType::BEGINNER:
            case MissionType::WEEKLY;
            case MissionType::DAILY:
                return UsrMissionNormal::factory()->create([
                    'usr_user_id' => $usrUserId,
                    'mission_type' => $missionType->getIntValue(),
                    'mst_mission_id' => $mstMissionId,
                    'status' => $status->value,
                    'progress' => $progress,
                    'latest_reset_at' => $latestResetAt,
                    'cleared_at' => $clearedAt,
                    'received_reward_at' => $receivedRewardAt,
                ]);
            case MissionType::EVENT_DAILY:
                return UsrMissionEvent::factory()->create([
                    'usr_user_id' => $usrUserId,
                    'mission_type' => $missionType->getIntValue(),
                    'mst_mission_id' => $mstMissionId,
                    'status' => $status->value,
                    'progress' => $progress,
                    'latest_reset_at' => $latestResetAt,
                    'cleared_at' => $clearedAt,
                    'received_reward_at' => $receivedRewardAt,
                ]);
            default:
                throw new \Exception('not support missionType');
        }
    }

    public function test_updateTriggeredMissions_同じパターンのマスタを用意して時間経過リセットを考慮しつつ各ミッションタイプが別々に進捗管理できる(): void
    {
        // Setup

        // テスト対象とするミッションタイプ
        $targetMissionTypes = [
            MissionType::DAILY,
            MissionType::WEEKLY,
            MissionType::EVENT_DAILY,
        ];

        $usrUserId = $this->createUsrUser()->getId();

        $now = $this->fixTime('2024-10-10 05:00:00');
        $nowDateTimeString = $now->toDateTimeString();

        $subDay = $now->copy()->subDay();
        $subDayDateTimeString = $subDay->toDateTimeString();

        $receivedRewardAt = '2024-10-09 05:00:00'; // クリア済かつ報酬受取済となった日時

        // 現在日時(fixTime)と比較したときに書く日付設定
        // 要リセット判定になる日時
        $resettableNextResetAts = [
            MissionType::DAILY->value => '2024-10-09 00:00:00',
            MissionType::WEEKLY->value => '2024-10-06 00:00:00',
            MissionType::EVENT_DAILY->value => '2024-10-09 00:00:00',
        ];

        MstEvent::factory()->create([
            'id' => 'event1',
            'start_at' => '2024-10-01 00:00:00',
            'end_at' => '2024-10-31 23:59:59',
        ]);

        foreach ($targetMissionTypes as $missionType) {
            // mst
            $mstIdPrefix = $missionType->value;
            // トリガーされるミッション
            $this->createMstMission($mstIdPrefix.'1', $missionType, MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 1, 'group1');
            $this->createMstMission($mstIdPrefix.'2', $missionType, MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 2, 'group1');
            $this->createMstMission($mstIdPrefix.'3', $missionType, MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 3, 'group1');
            $this->createMstMission($mstIdPrefix.'4', $missionType, MissionCriterionType::COIN_COLLECT, null, 1, 'group2');
            $this->createMstMission($mstIdPrefix.'5', $missionType, MissionCriterionType::COIN_COLLECT, null, 100, 'group2');
            $this->createMstMission($mstIdPrefix.'6', $missionType, MissionCriterionType::COIN_COLLECT, null, 101, 'group2');
            $this->createMstMission($mstIdPrefix.'7', $missionType, MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage2', 1, 'group2');
            // トリガーされないミッション
            $this->createMstMission($mstIdPrefix.'10', $missionType, MissionCriterionType::DEFEAT_ENEMY_COUNT, null, 100);
            // 複合ミッション
            $this->createMstMission($mstIdPrefix.'20', $missionType, MissionCriterionType::MISSION_CLEAR_COUNT, null, 1);
            $this->createMstMission($mstIdPrefix.'21', $missionType, MissionCriterionType::MISSION_CLEAR_COUNT, null, 3);
            $this->createMstMission($mstIdPrefix.'22', $missionType, MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'group1', 100);
            $this->createMstMission($mstIdPrefix.'23', $missionType, MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'group2', 3);

            $resettableNextResetAt = $resettableNextResetAts[$missionType->value];

            // usr

            // トリガーされるミッション
            // クリア済みで、リセットされるが、新規トリガーでクリア
            $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'1', MissionStatus::CLEAR, 100, $receivedRewardAt, $receivedRewardAt, $resettableNextResetAt);
            // 未クリアで、リセットされず、新規トリガーで進捗が進み、クリア
            $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'2', MissionStatus::UNCLEAR, 1, null, null, $now);
            // 未クリアで、リセットされず、新規トリガーで進捗が進むが、まだ未クリア
            $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'3', MissionStatus::UNCLEAR, 1, null, null, $now);

            // id 4,5,6,7 のユーザーレコードは用意せずに、新規作成されることを確認する

            // トリガーされないミッション
            // クリア済みだが、トリガーされず関係ないので、リセットされないし、データ変更がない
            $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'10', MissionStatus::CLEAR, 100, $receivedRewardAt, $receivedRewardAt, $resettableNextResetAt);

            // 複合ミッション
            // クリア済みで、リセットされるが、新規トリガーでクリア
            $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'20', MissionStatus::CLEAR, 100, $receivedRewardAt, $receivedRewardAt, $resettableNextResetAt);
            // id 21 のユーザーレコードは用意せずに、新規作成されることを確認する
            // クリア済みで、リセットされ、進捗が0から進むが、未クリア
            $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'22', MissionStatus::CLEAR, 100, $subDayDateTimeString, $subDayDateTimeString, $resettableNextResetAt);
            // id 23 のユーザーレコードは用意せずに、新規作成されることを確認する
        }

        // trigger
        $this->missionManager->addTriggers(collect([
            new MissionTrigger(MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value, 'stage1', 1),
            new MissionTrigger(MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value, 'stage2', 1),
            new MissionTrigger(MissionCriterionType::COIN_COLLECT->value, null, 100),
        ]), null); // 全ミッションタイプに対してトリガー追加

        // Exercise
        $this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);
        $this->saveAll();

        // Verify
        foreach ($targetMissionTypes as $missionType) {
            $mstIdPrefix = $missionType->value;

            switch ($missionType) {
                case MissionType::DAILY:
                case MissionType::WEEKLY:
                    $usrMissions = UsrMissionNormal::query()->where('usr_user_id', $usrUserId)->where('mission_type', $missionType->getIntValue())->get()->keyBy('mst_mission_id');
                    break;
                case MissionType::EVENT_DAILY:
                    $usrMissions = UsrMissionEvent::query()->where('usr_user_id', $usrUserId)->where('mission_type', $missionType->getIntValue())->get()->keyBy('mst_mission_id');
                    break;
            }
            $this->assertCount(12, $usrMissions, 'usr_missions count is not match: '.$missionType->value);

            $resettableNextResetAt = $resettableNextResetAts[$missionType->value];

            // トリガーされたミッション
            // クリア済みで、リセットされるが、新規トリガーでクリア
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'1'), MissionStatus::CLEAR->value, 1, $nowDateTimeString, $nowDateTimeString, null);
            // 未クリアで、リセットされず、新規トリガーで進捗が進み、クリア
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'2'), MissionStatus::CLEAR->value, 2, $nowDateTimeString, $nowDateTimeString, null);
            // 未クリアで、リセットされず、新規トリガーで進捗が進むが、まだ未クリア
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'3'), MissionStatus::UNCLEAR->value, 2, $nowDateTimeString, null, null);
            // 新規作成され、新規トリガーでクリア
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'4'), MissionStatus::CLEAR->value, 1, $nowDateTimeString, $nowDateTimeString, null);
            // 新規作成され、新規トリガーでクリア
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'5'), MissionStatus::CLEAR->value, 100, $nowDateTimeString, $nowDateTimeString, null);
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'6'), MissionStatus::UNCLEAR->value, 100, $nowDateTimeString, null, null);
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'7'), MissionStatus::CLEAR->value, 1, $nowDateTimeString, $nowDateTimeString, null);

            // トリガーされな勝ったミッション
            // クリア済みだが、トリガーされず関係ないので、リセットされないし、データ変更がない
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'10'), MissionStatus::CLEAR->value, 100, $resettableNextResetAt, $receivedRewardAt, $receivedRewardAt);

            // 複合ミッション
            // クリア済みで、リセットされるが、新規トリガーでクリア
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'20'), MissionStatus::CLEAR->value, 1, $nowDateTimeString, $nowDateTimeString, null);
            // クリア済みで、リセットされ、進捗が0から進むが、未クリア
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'22'), MissionStatus::UNCLEAR->value, 2, $nowDateTimeString, null, null);
            // 新規作成されたユーザーレコード
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'21'), MissionStatus::CLEAR->value, 3, $nowDateTimeString, $nowDateTimeString, null);
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'23'), MissionStatus::CLEAR->value, 3, $nowDateTimeString, $nowDateTimeString, null);
        }
    }

    public function test_updateTriggeredMissions_同じパターンのマスタを用意してリセットなしの各ミッションタイプが別々に進捗管理できる(): void
    {
        // Setup

        // テスト対象とするミッションタイプ
        $targetMissionTypes = [
            MissionType::BEGINNER,
            MissionType::ACHIEVEMENT,
        ];

        $usrUserId = $this->createUsrUser()->getId();

        $now = $this->fixTime('2024-10-10 05:00:00');
        $nowDateTimeString = $now->toDateTimeString();

        $subDay = $now->copy()->subDay();
        $subDayDateTimeString = $subDay->toDateTimeString();

        $receivedRewardAt = '2024-10-09 05:00:00'; // クリア済かつ報酬受取済となった日時

        UsrMissionStatus::factory()->create([
            'usr_user_id' => $usrUserId,
            'beginner_mission_status' => MissionBeginnerStatus::HAS_LOCKED->value,
            'mission_unlocked_at' => '2023-10-01 05:00:00', // 初心者ミッションが全開放される日時で設定
        ]);

        foreach ($targetMissionTypes as $missionType) {
            // mst
            $mstIdPrefix = $missionType->value;
            // トリガーされるミッション
            $this->createMstMission($mstIdPrefix.'1', $missionType, MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 1, 'group1');
            $this->createMstMission($mstIdPrefix.'2', $missionType, MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 2, 'group1');
            $this->createMstMission($mstIdPrefix.'3', $missionType, MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 3, 'group1');
            $this->createMstMission($mstIdPrefix.'4', $missionType, MissionCriterionType::COIN_COLLECT, null, 1, 'group2');
            $this->createMstMission($mstIdPrefix.'5', $missionType, MissionCriterionType::COIN_COLLECT, null, 100, 'group2');
            $this->createMstMission($mstIdPrefix.'6', $missionType, MissionCriterionType::COIN_COLLECT, null, 101, 'group2');
            $this->createMstMission($mstIdPrefix.'7', $missionType, MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage2', 1, 'group2');
            // トリガーされないミッション
            $this->createMstMission($mstIdPrefix.'10', $missionType, MissionCriterionType::DEFEAT_ENEMY_COUNT, null, 100);
            // 複合ミッション
            $this->createMstMission($mstIdPrefix.'20', $missionType, MissionCriterionType::MISSION_CLEAR_COUNT, null, 1);
            $this->createMstMission($mstIdPrefix.'21', $missionType, MissionCriterionType::MISSION_CLEAR_COUNT, null, 3);
            $this->createMstMission($mstIdPrefix.'22', $missionType, MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'group1', 100);
            $this->createMstMission($mstIdPrefix.'23', $missionType, MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'group2', 3);

            // usr

            // トリガーされるミッション
            // クリア済みで、データ変更なし
            $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'1', MissionStatus::CLEAR, 100, $receivedRewardAt, $receivedRewardAt, $nowDateTimeString);
            // 未クリアで、新規トリガーで進捗が進み、クリア
            $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'2', MissionStatus::UNCLEAR, 1, null, null, $nowDateTimeString);
            // 未クリアで、新規トリガーで進捗が進むが、まだ未クリア
            $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'3', MissionStatus::UNCLEAR, 1, null, null, $nowDateTimeString);

            // id 4,5,6,7 のユーザーレコードは用意せずに、新規作成されることを確認する

            // トリガーされないミッション
            // クリア済みだが、トリガーされず関係ないので、データ変更なし
            $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'10', MissionStatus::CLEAR, 100, $receivedRewardAt, $receivedRewardAt, $nowDateTimeString);

            // 複合ミッション
            // クリア済みで、データ変更なし
            $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'20', MissionStatus::CLEAR, 100, $receivedRewardAt, $receivedRewardAt, $nowDateTimeString);
            // id 21 のユーザーレコードは用意せずに、新規作成され、クリア
            // 未クリアで、進捗が進むが、未クリア
            $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'22', MissionStatus::UNCLEAR, 1, null, null, $nowDateTimeString);
            // id 23 のユーザーレコードは用意せずに、新規作成され、クリア
        }

        // trigger
        $this->missionManager->addTriggers(collect([
            new MissionTrigger(MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value, 'stage1', 1),
            new MissionTrigger(MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value, 'stage2', 1),
            new MissionTrigger(MissionCriterionType::COIN_COLLECT->value, null, 100),
        ]), null); // 全ミッションタイプに対してトリガー追加

        // Exercise
        $this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);
        $this->saveAll();

        // Verify
        foreach ($targetMissionTypes as $missionType) {
            $mstIdPrefix = $missionType->value;
            $usrMissions = UsrMissionNormal::query()->where('usr_user_id', $usrUserId)->where('mission_type', $missionType->getIntValue())->get()->keyBy('mst_mission_id');

            // トリガーされたミッション
            // クリア済みで、データ変更なし
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'1'), MissionStatus::CLEAR->value, 100, $nowDateTimeString, $receivedRewardAt, $receivedRewardAt);
            // 未クリアで、新規トリガーで進捗が進み、クリア
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'2'), MissionStatus::CLEAR->value, 2, $nowDateTimeString, $nowDateTimeString, null);
            // 未クリアで、新規トリガーで進捗が進むが、まだ未クリア
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'3'), MissionStatus::UNCLEAR->value, 2, $nowDateTimeString, null, null);
            // 新規作成され、新規トリガーでクリア
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'4'), MissionStatus::CLEAR->value, 1, $nowDateTimeString, $nowDateTimeString, null);
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'5'), MissionStatus::CLEAR->value, 100, $nowDateTimeString, $nowDateTimeString, null);
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'7'), MissionStatus::CLEAR->value, 1, $nowDateTimeString, $nowDateTimeString, null);
            // 新規作成され、新規トリガーで進捗が進むが、未クリア
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'6'), MissionStatus::UNCLEAR->value, 100, $nowDateTimeString, null, null);

            // トリガーされなかったミッション
            // クリア済みだが、トリガーされず関係ないので、データ変更がない
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'10'), MissionStatus::CLEAR->value, 100, $nowDateTimeString, $receivedRewardAt, $receivedRewardAt);

            // 複合ミッション
            // クリア済みで、データ変更なし
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'20'), MissionStatus::CLEAR->value, 100, $nowDateTimeString, $receivedRewardAt, $receivedRewardAt);
            // 未クリアで、進捗が進むが、未クリア
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'22'), MissionStatus::UNCLEAR->value, 2, $nowDateTimeString, null, null);
            // 新規作成され、新規トリガーでクリア
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'21'), MissionStatus::CLEAR->value, 3, $nowDateTimeString, $nowDateTimeString, null);
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'23'), MissionStatus::CLEAR->value, 3, $nowDateTimeString, $nowDateTimeString, null);

            // 上記で確認したデータ以外のユーザーレコードが存在しないことを確認
            $this->assertCount(12, $usrMissions);
        }
    }

    public function test_updateTriggeredMissions_期間内のイベントのみミッションがカウントされる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();

        // mst
        MstEvent::factory()->createMany([
            // 開催中のイベント
            [
                'id' => 'mst_event_id_1',
                'start_at' => $now->subHours(1)->toDateTimeString(),
                'end_at' => $now->addHours(1)->toDateTimeString()
            ],
            // 開催外(過去)のイベント
            [
                'id' => 'mst_event_id_2',
                'start_at' => $now->subHours(2)->toDateTimeString(),
                'end_at' => $now->subHours(1)->toDateTimeString()
            ],
            // 開催外(未来)のイベント
            [
                'id' => 'mst_event_id_3',
                'start_at' => $now->addHours(1)->toDateTimeString(),
                'end_at' => $now->addHours(2)->toDateTimeString()
            ],
        ]);
        MstMissionEvent::factory()->createMany([
            [
                'id' => 'mst_event_mission_id_1',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 10,
                'mst_event_id' => 'mst_event_id_1',
            ],
            [
                'id' => 'mst_event_mission_id_2',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 10,
                'mst_event_id' => 'mst_event_id_2',
            ],
            [
                'id' => 'mst_event_mission_id_3',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 10,
                'mst_event_id' => 'mst_event_id_3',
            ],
        ]);

        $this->missionManager->addTriggers(collect([
            new MissionTrigger(
                MissionCriterionType::COIN_COLLECT->value,
                null,
                50,
            ),
        ]));

        // Exercise
        $this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);
        $this->saveAll();

        // Verify
        $usrMissions = UsrMissionEvent::where('usr_user_id', $usrUserId)->get()->keyBy->getMstMissionId();
        $this->assertCount(1, $usrMissions);
        $this->checkUsrMissionEvent($usrMissions['mst_event_mission_id_1'], MissionStatus::CLEAR, 10, $now, $now, null);
    }

    public function test_updateTriggeredMissions_期間内の期間限定ミッションのみ進捗更新される()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2025-03-13 00:00:00');

        // mst
        MstMissionLimitedTerm::factory()->createMany([
            // 開催中の期間限定ミッション
            [
                'id' => 'term1-1',
                'criterion_type' => MissionCriterionType::COIN_COLLECT,
                'criterion_value' => null,
                'criterion_count' => 10,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE->value,
                'start_at' => '2025-03-12 00:00:00',
                'end_at' => '2025-03-20 00:00:00',
            ],
            [
                'id' => 'term1-2',
                'criterion_type' => MissionCriterionType::COIN_COLLECT,
                'criterion_value' => null,
                'criterion_count' => 20,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE->value,
                'start_at' => '2025-03-12 00:00:00',
                'end_at' => '2025-03-20 00:00:00',
            ],
            [
                'id' => 'term1-3',
                'criterion_type' => MissionCriterionType::COIN_COLLECT,
                'criterion_value' => null,
                'criterion_count' => 50,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE->value,
                'start_at' => '2025-03-12 00:00:00',
                'end_at' => '2025-03-20 00:00:00',
            ],
            // 未開催の期間限定ミッション
            [
                'id' => 'term2-1',
                'criterion_type' => MissionCriterionType::COIN_COLLECT,
                'criterion_value' => null,
                'criterion_count' => 1,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE->value,
                'start_at' => '2025-02-12 00:00:00',
                'end_at' => '2025-02-20 00:00:00',
            ],
        ]);
        $groupId = 'groupTerm1';
        foreach (['term1-1', 'term1-2', 'term1-3'] as $i => $mstMissionId) {
            MstMissionLimitedTermDependency::factory()->create([
                'group_id' => $groupId,
                'mst_mission_limited_term_id' => $mstMissionId,
                'unlock_order' => $i + 1,
            ]);
        }

        $this->missionManager->addTriggers(collect([
            new MissionTrigger(
                MissionCriterionType::COIN_COLLECT->value,
                null,
                19,
            ),
        ]));

        // Exercise
        $this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);
        $this->saveAll();

        // Verify
        $usrMissions = UsrMissionLimitedTerm::where('usr_user_id', $usrUserId)->get()->keyBy->getMstMissionId();
        $this->assertCount(3, $usrMissions);
        // クリア
        $this->checkUsrMissionLimitedTerm($usrMissions['term1-1'], MissionStatus::CLEAR, 10, $now, $now, null, MissionUnlockStatus::OPEN);
        // term1-1クリアなので解放されるが、進捗足らず未クリア
        $this->checkUsrMissionLimitedTerm($usrMissions['term1-2'], MissionStatus::UNCLEAR, 19, $now, null, null, MissionUnlockStatus::OPEN);
        // term1-2が未クリアなので、未解放のまま、達成進捗は進む
        $this->checkUsrMissionLimitedTerm($usrMissions['term1-3'], MissionStatus::UNCLEAR, 19, $now, null, null, MissionUnlockStatus::LOCK);
    }
}
