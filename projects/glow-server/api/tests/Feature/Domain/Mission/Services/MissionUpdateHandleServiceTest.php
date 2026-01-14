<?php

namespace Tests\Feature\Domain\Mission;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionLimitedTermCategory;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\MissionManager;
use App\Domain\Mission\Models\Eloquent\UsrMissionEvent;
use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;
use App\Domain\Resource\Mst\Models\MstEvent;
use App\Domain\Resource\Mst\Models\MstMissionAchievement;
use App\Domain\Resource\Mst\Models\MstMissionBeginner;
use App\Domain\Resource\Mst\Models\MstMissionDaily;
use App\Domain\Resource\Mst\Models\MstMissionEvent;
use App\Domain\Resource\Mst\Models\MstMissionEventDaily;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTerm;
use App\Domain\Resource\Mst\Models\MstMissionWeekly;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class MissionUpdateHandleServiceTest extends TestCase
{
    use TestMissionTrait;

    private MissionManager $missionManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->missionManager = $this->app->make(MissionManager::class);
    }

    public function test_handleAllUpdateTriggeredMissions_全ミッションタイプにおいてトリガーされたミッションの進捗が更新される()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-10-31 00:00:00');

        // mst
        MstMissionAchievement::factory()->createMany([
            /**
             * トリガーされるミッション
             */
            // 開放、クリア
            [
                'id' => 'achievement1',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'criterion_value' => 'stage1', 'criterion_count' => 1,
                'group_key' => 'group1',
            ],
            // 開放、クリア
            [
                'id' => 'achievement3',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 10,
            ],
            /**
             * トリガーされないミッション
             */
            // 未開放、未クリア
            [
                'id' => 'achievement10',
                'criterion_type' => MissionCriterionType::DEFEAT_ENEMY_COUNT, 'criterion_value' => null, 'criterion_count' => 100,
            ],
            /**
             * 複合ミッション
             */
            // 開放、未クリア
            [
                'id' => 'achievement22',
                'criterion_type' => MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'criterion_value' => 'group1', 'criterion_count' => 100,
            ],
            // 開放、クリア
            [
                'id' => 'achievement20',
                'criterion_type' => MissionCriterionType::MISSION_CLEAR_COUNT, 'criterion_value' => null, 'criterion_count' => 2,
            ],
        ]);
        MstMissionDaily::factory()->createMany([
            /**
             * トリガーされるミッション
             */
            // 開放、クリア
            [
                'id' => 'daily1',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'criterion_value' => 'stage1', 'criterion_count' => 1,
                'group_key' => 'group1',
            ],
            // 開放、未クリア
            [
                'id' => 'daily3',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 100,
            ],
            /**
             * トリガーされないミッション
             */
            // 未開放、未クリア
            [
                'id' => 'daily10',
                'criterion_type' => MissionCriterionType::DEFEAT_ENEMY_COUNT, 'criterion_value' => null, 'criterion_count' => 100,
            ],
            /**
             * 複合ミッション
             */
            // 開放、クリア
            [
                'id' => 'daily22',
                'criterion_type' => MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'criterion_value' => 'group1', 'criterion_count' => 1,
            ],
            // 開放、未クリア
            [
                'id' => 'daily20',
                'criterion_type' => MissionCriterionType::MISSION_CLEAR_COUNT, 'criterion_value' => null, 'criterion_count' => 2,
            ],
        ]);
        MstMissionWeekly::factory()->createMany([
            /**
             * トリガーされるミッション
             */
            // 開放、クリア
            [
                'id' => 'weekly1',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'criterion_value' => 'stage1', 'criterion_count' => 1,
                'group_key' => 'group1',
            ],
            // 開放、未クリア
            [
                'id' => 'weekly3',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 100,
            ],
            /**
             * トリガーされないミッション
             */
            // 未開放、未クリア
            [
                'id' => 'weekly10',
                'criterion_type' => MissionCriterionType::DEFEAT_ENEMY_COUNT, 'criterion_value' => null, 'criterion_count' => 100,
            ],
            /**
             * 複合ミッション
             */
            // 開放、クリア
            [
                'id' => 'weekly22',
                'criterion_type' => MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'criterion_value' => 'group1', 'criterion_count' => 1,
            ],
            // 開放、未クリア
            [
                'id' => 'weekly20',
                'criterion_type' => MissionCriterionType::MISSION_CLEAR_COUNT, 'criterion_value' => null, 'criterion_count' => 2,
            ],
        ]);
        MstMissionBeginner::factory()->createMany([
            /**
             * トリガーされるミッション
             */
            // 開放、クリア
            [
                'id' => 'beginner1',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'criterion_value' => 'stage1', 'criterion_count' => 1,
                'group_key' => 'group1',
            ],
            // 開放、未クリア
            [
                'id' => 'beginner3',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 100,
            ],
            /**
             * トリガーされないミッション
             */
            // 未開放、未クリア
            [
                'id' => 'beginner10',
                'criterion_type' => MissionCriterionType::DEFEAT_ENEMY_COUNT, 'criterion_value' => null, 'criterion_count' => 100,
            ],
            /**
             * 複合ミッション
             */
            // 開放、クリア
            [
                'id' => 'beginner22',
                'criterion_type' => MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'criterion_value' => 'group1', 'criterion_count' => 1,
            ],
            // 開放、未クリア
            [
                'id' => 'beginner20',
                'criterion_type' => MissionCriterionType::MISSION_CLEAR_COUNT, 'criterion_value' => null, 'criterion_count' => 2,
            ],
        ]);
        MstEvent::factory()->create([
            'id' => 'mst_event_id'
        ]);
        MstMissionEvent::factory()->createMany([
            /**
             * トリガーされるミッション
             */
            // 開放、クリア
            [
                'id' => 'event1',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'criterion_value' => 'stage1', 'criterion_count' => 1,
                'group_key' => 'group1',
                'mst_event_id' => 'mst_event_id',
            ],
            // 開放、クリア
            [
                'id' => 'event3',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 10,
                'mst_event_id' => 'mst_event_id',
            ],
            /**
             * トリガーされないミッション
             */
            // 未開放、未クリア
            [
                'id' => 'event10',
                'criterion_type' => MissionCriterionType::DEFEAT_ENEMY_COUNT, 'criterion_value' => null, 'criterion_count' => 100,
                'mst_event_id' => 'mst_event_id',
            ],
            /**
             * 複合ミッション
             */
            // 開放、未クリア
            [
                'id' => 'event22',
                'criterion_type' => MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'criterion_value' => 'group1', 'criterion_count' => 100,
                'mst_event_id' => 'mst_event_id',
            ],
            // 開放、クリア
            [
                'id' => 'event20',
                'criterion_type' => MissionCriterionType::MISSION_CLEAR_COUNT, 'criterion_value' => null, 'criterion_count' => 2,
                'mst_event_id' => 'mst_event_id',
            ],
            /**
             * 期間外のイベントミッション
             */
            // 開放、クリア
            [
                'id' => 'event100',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'criterion_value' => 'stage1', 'criterion_count' => 1,
                'group_key' => 'group1',
                'mst_event_id' => 'mst_event_id_out_period',
            ],
        ]);
        MstMissionEventDaily::factory()->createMany([
            /**
             * トリガーされるミッション
             */
            // 開放、クリア
            [
                'id' => 'event_daily1',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'criterion_value' => 'stage1', 'criterion_count' => 1,
                'group_key' => 'group1',
                'mst_event_id' => 'mst_event_id',
            ],
            // 開放、クリア
            [
                'id' => 'event_daily3',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 10,
                'mst_event_id' => 'mst_event_id',
            ],
            /**
             * トリガーされないミッション
             */
            // 未開放、未クリア
            [
                'id' => 'event_daily10',
                'criterion_type' => MissionCriterionType::DEFEAT_ENEMY_COUNT, 'criterion_value' => null, 'criterion_count' => 100,
                'mst_event_id' => 'mst_event_id',
            ],
            /**
             * 複合ミッション
             */
            // 開放、未クリア
            [
                'id' => 'event_daily22',
                'criterion_type' => MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'criterion_value' => 'group1', 'criterion_count' => 100,
                'mst_event_id' => 'mst_event_id',
            ],
            // 開放、クリア
            [
                'id' => 'event_daily20',
                'criterion_type' => MissionCriterionType::MISSION_CLEAR_COUNT, 'criterion_value' => null, 'criterion_count' => 2,
                'mst_event_id' => 'mst_event_id',
            ],
            /**
             * 期間外のイベントミッション
             */
            // 開放、クリア
            [
                'id' => 'event_daily100',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'criterion_value' => 'stage1', 'criterion_count' => 1,
                'group_key' => 'group1',
                'mst_event_id' => 'mst_event_id_out_period',
            ],
        ]);
        MstMissionLimitedTerm::factory()->createMany([
            /**
             * トリガーされるミッション
             */
            // 開放、クリア
            [
                'id' => 'mst_mission_limited_term_1',
                'progress_group_key' => 'progress_group_key_1',
                'criterion_type' => MissionCriterionType::ADVENT_BATTLE_CHALLENGE_COUNT, 'criterion_value' => null, 'criterion_count' => 10,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE->value,
                'start_at' => $now->subHours(1)->toDateTimeString(),
                'end_at' => $now->addHours(1)->toDateTimeString(),
            ],
            /**
             * トリガーされないミッション
             */
            // 未開放、未クリア
            [
                'id' => 'mst_mission_limited_term_2',
                'progress_group_key' => 'progress_group_key_1',
                'criterion_type' => MissionCriterionType::ADVENT_BATTLE_TOTAL_SCORE, 'criterion_value' => null, 'criterion_count' => 10,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE->value,
                'start_at' => $now->subHours(1)->toDateTimeString(),
                'end_at' => $now->addHours(1)->toDateTimeString(),
            ],
            // 期間外の期間限定ミッション
            [
                'id' => 'mst_mission_limited_term_3',
                'progress_group_key' => 'progress_group_key_2',
                'criterion_type' => MissionCriterionType::ADVENT_BATTLE_CHALLENGE_COUNT, 'criterion_value' => null, 'criterion_count' => 10,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE->value,
                'start_at' => $now->subHours(2)->toDateTimeString(),
                'end_at' => $now->subHours(1)->toDateTimeString(),
            ],
        ]);

        $this->prepareUpdateBeginnerMission($usrUserId);

        // ミッショントリガー
        $this->missionManager->addTriggers(collect([
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value,
                'stage1',
                2,
            ),
            new MissionTrigger(
                MissionCriterionType::COIN_COLLECT->value,
                null,
                50,
            ),
            new MissionTrigger(
                MissionCriterionType::ADVENT_BATTLE_CHALLENGE_COUNT->value,
                null,
                5,
            ),
        ]));

        // Exercise
        $this->handleAllUpdateTriggeredMissions($usrUserId, $now);

        /**
         * アチーブメントミッション
         */
        // Verify
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, MissionType::ACHIEVEMENT);
        $this->assertCount(4, $usrMissions);
        $this->checkUsrMissionNormal($usrMissions['achievement1'], MissionStatus::CLEAR, 1, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement3'], MissionStatus::CLEAR, 10, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement22'], MissionStatus::UNCLEAR, 1, $now, null, null);
        $this->checkUsrMissionNormal($usrMissions['achievement20'], MissionStatus::CLEAR, 2, $now, $now, null);

        /**
         * デイリーミッション
         */
        // Verify
        $usrMissions = UsrMissionNormal::query()->where('usr_user_id', $usrUserId)->where('mission_type', MissionType::DAILY->getIntValue())->get()->keyBy(fn($usrMission) => $usrMission->getMstMissionId());
        $this->assertCount(4, $usrMissions);
        $this->checkUsrMissionNormal($usrMissions['daily1'], MissionStatus::CLEAR, 1, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['daily3'], MissionStatus::UNCLEAR, 50, $now, null, null);
        $this->checkUsrMissionNormal($usrMissions['daily22'], MissionStatus::CLEAR, 1, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['daily20'], MissionStatus::UNCLEAR, 1, $now, null, null);

        /**
         * ウィークリーミッション
         */
        $usrMissions = UsrMissionNormal::query()->where('usr_user_id', $usrUserId)->where('mission_type', MissionType::WEEKLY->getIntValue())->get()->keyBy(fn($usrMission) => $usrMission->getMstMissionId());
        $this->checkUsrMissionNormal($usrMissions['weekly1'], MissionStatus::CLEAR, 1, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['weekly3'], MissionStatus::UNCLEAR, 50, $now, null, null);
        $this->checkUsrMissionNormal($usrMissions['weekly22'], MissionStatus::CLEAR, 1, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['weekly20'], MissionStatus::UNCLEAR, 1, $now, null, null);

        /**
         * 初心者ミッション
         */
        // Verify
        $usrMissions = UsrMissionNormal::query()->where('usr_user_id', $usrUserId)->where('mission_type', MissionType::BEGINNER->getIntValue())->get()->keyBy(fn($usrMission) => $usrMission->getMstMissionId());
        $this->assertCount(4, $usrMissions);
        $this->checkUsrMissionNormal($usrMissions['beginner1'], MissionStatus::CLEAR, 1, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['beginner3'], MissionStatus::UNCLEAR, 50, $now, null, null);
        $this->checkUsrMissionNormal($usrMissions['beginner22'], MissionStatus::CLEAR, 1, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['beginner20'], MissionStatus::UNCLEAR, 1, $now, null, null);

        /**
         * イベントミッション
         */
        // Verify
        $usrMissions = UsrMissionEvent::query()->where('usr_user_id', $usrUserId)->where('mission_type', MissionType::EVENT->getIntValue())->get()->keyBy(fn($usrMission) => $usrMission->getMstMissionId());
        $this->assertCount(4, $usrMissions);
        $this->checkUsrMissionEvent($usrMissions['event1'], MissionStatus::CLEAR, 1, $now, $now, null);
        $this->checkUsrMissionEvent($usrMissions['event3'], MissionStatus::CLEAR, 10, $now, $now, null);
        $this->checkUsrMissionEvent($usrMissions['event22'], MissionStatus::UNCLEAR, 1, $now, null, null);
        $this->checkUsrMissionEvent($usrMissions['event20'], MissionStatus::CLEAR, 2, $now, $now, null);

        /**
         * イベントデイリーミッション
         */
        // Verify
        $usrMissions = UsrMissionEvent::query()->where('usr_user_id', $usrUserId)->where('mission_type', MissionType::EVENT_DAILY->getIntValue())->get()->keyBy(fn($usrMission) => $usrMission->getMstMissionId());
        $this->assertCount(4, $usrMissions);
        $this->checkUsrMissionEvent($usrMissions['event_daily1'], MissionStatus::CLEAR, 1, $now, $now, null);
        $this->checkUsrMissionEvent($usrMissions['event_daily3'], MissionStatus::CLEAR, 10, $now, $now, null);
        $this->checkUsrMissionEvent($usrMissions['event_daily22'], MissionStatus::UNCLEAR, 1, $now, null, null);
        $this->checkUsrMissionEvent($usrMissions['event_daily20'], MissionStatus::CLEAR, 2, $now, $now, null);

        /**
         * 期間限定ミッション
         */
        // Verify
        $usrMissions = UsrMissionLimitedTerm::query()->where('usr_user_id', $usrUserId)->get()->keyBy->getMstMissionId();
        $this->assertCount(1, $usrMissions);
        $this->checkUsrMissionLimitedTerm($usrMissions['mst_mission_limited_term_1'], MissionStatus::UNCLEAR, 5, $now, null, null);
    }

    public function test_handleAllUpdateTriggeredMissions_異なるイベントの場合進捗が別れること()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2025-02-27 00:00:00');
        MstEvent::factory()->createMany([
            ['id' => 'mst_event_id_1', 'start_at' => '2025-02-01 00:00:00', 'end_at' => '2025-02-28 23:59:59'],
            ['id' => 'mst_event_id_2', 'start_at' => '2025-02-01 00:00:00', 'end_at' => '2025-02-28 23:59:59'],
        ]);
        MstMissionEvent::factory()->createMany([
            [
                'id' => 'mst_event_id_1_1',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 10,
                'mst_event_id' => 'mst_event_id_1',
            ],
            [
                'id' => 'mst_event_id_2_1',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 10,
                'mst_event_id' => 'mst_event_id_2',
            ],
        ]);
        UsrMissionEvent::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mission_type' => MissionType::EVENT->getIntValue(),
                'mst_mission_id' => 'mst_event_id_1_1',
                'status' => MissionStatus::UNCLEAR->value,
                'progress' => 5,
                'is_open' => MissionUnlockStatus::OPEN->value,
            ],
            // mst_event_id_2の方は、まだ進捗なしとする
        ]);

        $this->missionManager->addTriggers(collect([
            new MissionTrigger(
                MissionCriterionType::COIN_COLLECT->value,
                null,
                5,
            ),
        ]));

        // Exercise
        $this->handleAllUpdateTriggeredMissions($usrUserId, $now);

        // Verify
        $actuals = UsrMissionEvent::query()->where('usr_user_id', $usrUserId)->get()->keyBy->getMstMissionId();
        $this->assertCount(2, $actuals);

        $actual = $actuals['mst_event_id_1_1'];
        $this->assertEquals(10, $actual->getProgress());
        $this->assertEquals(MissionStatus::CLEAR->value, $actual->getStatus());
        $this->assertEquals(MissionUnlockStatus::OPEN->value, $actual->getIsOpen());

        $actual = $actuals['mst_event_id_2_1'];
        $this->assertEquals(5, $actual->getProgress());
        $this->assertEquals(MissionStatus::UNCLEAR->value, $actual->getStatus());
        $this->assertEquals(MissionUnlockStatus::OPEN->value, $actual->getIsOpen());
    }
}
