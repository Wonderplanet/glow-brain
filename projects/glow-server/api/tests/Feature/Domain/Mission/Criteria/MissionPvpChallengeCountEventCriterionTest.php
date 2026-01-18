<?php

namespace Tests\Feature\Domain\Mission\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionEvent;
use App\Domain\Pvp\Services\PvpMissionTriggerService;
use App\Domain\Resource\Mst\Models\MstMissionEvent;
use App\Domain\Resource\Mst\Models\MstMissionEventDaily;
use App\Domain\Resource\Mst\Models\MstEvent;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class MissionPvpChallengeCountEventCriterionTest extends TestCase
{
    use TestMissionTrait;

    private MissionCriterionType $targetCriterionType = MissionCriterionType::PVP_CHALLENGE_COUNT;

    private PvpMissionTriggerService $pvpMissionTriggerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pvpMissionTriggerService = $this->app->make(PvpMissionTriggerService::class);
        $this->missionUpdateHandleService = $this->app->make(\App\Domain\Mission\Services\MissionUpdateHandleService::class);
    }

    public function test_PVPイベントミッション_挑戦で進捗更新()
    {
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        // イベントマスターデータを作成
        $mstEvent = MstEvent::factory()->create([
            'id' => 'test_event_1',
            'start_at' => $now->subHour()->format('Y-m-d H:i:s'),
            'end_at' => $now->addHour()->format('Y-m-d H:i:s'),
        ]);

        MstMissionEvent::factory()->create([
            'id' => 'event_pvp_challenge_15',
            'mst_event_id' => $mstEvent->id,
            'criterion_type' => $this->targetCriterionType->value,
            'criterion_value' => null,
            'criterion_count' => 15,
            'sort_order' => 1,
        ]);

        $this->pvpMissionTriggerService->sendStartTriggers();
        $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $now);
        $this->saveAll();

        $usrMission = UsrMissionEvent::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_mission_id', 'event_pvp_challenge_15')
            ->first();

        $this->assertNotNull($usrMission);
        $this->assertEquals(MissionStatus::UNCLEAR->value, $usrMission->status);
        $this->assertEquals(1, $usrMission->progress);
    }

    public function test_PVPイベントミッション_挑戦で達成()
    {
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        // イベントマスターデータを作成
        $mstEvent = MstEvent::factory()->create([
            'id' => 'test_event_2',
            'start_at' => $now->subHour()->format('Y-m-d H:i:s'),
            'end_at' => $now->addHour()->format('Y-m-d H:i:s'),
        ]);

        MstMissionEvent::factory()->create([
            'id' => 'event_pvp_challenge_3',
            'mst_event_id' => $mstEvent->id,
            'criterion_type' => $this->targetCriterionType->value,
            'criterion_value' => null,
            'criterion_count' => 3,
            'sort_order' => 1,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $this->pvpMissionTriggerService->sendStartTriggers();
            $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $now);
            $this->saveAll();
        }

        $usrMission = UsrMissionEvent::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_mission_id', 'event_pvp_challenge_3')
            ->first();

        $this->assertNotNull($usrMission);
        $this->assertEquals(MissionStatus::CLEAR->value, $usrMission->status);
        $this->assertEquals(3, $usrMission->progress);
        $this->assertNotNull($usrMission->cleared_at);
    }

    public function test_PVPイベントデイリーミッション_挑戦で進捗更新()
    {
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        // イベントマスターデータを作成
        $mstEvent = MstEvent::factory()->create([
            'id' => 'test_event_daily_1',
            'start_at' => $now->subHour()->format('Y-m-d H:i:s'),
            'end_at' => $now->addHour()->format('Y-m-d H:i:s'),
        ]);

        // イベントデイリーミッションマスターデータを作成
        MstMissionEventDaily::factory()->create([
            'id' => 'event_daily_pvp_challenge_5',
            'mst_event_id' => $mstEvent->id,
            'criterion_type' => $this->targetCriterionType->value,
            'criterion_value' => null,
            'criterion_count' => 5,
            'sort_order' => 1,
        ]);

        $this->pvpMissionTriggerService->sendStartTriggers();
        $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $now);
        $this->saveAll();

        $usrMission = UsrMissionEvent::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_mission_id', 'event_daily_pvp_challenge_5')
            ->first();

        $this->assertNotNull($usrMission);
        $this->assertEquals(MissionStatus::UNCLEAR->value, $usrMission->status);
        $this->assertEquals(1, $usrMission->progress);
    }

    public function test_PVPイベントデイリーミッション_挑戦で達成()
    {
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        // イベントマスターデータを作成
        $mstEvent = MstEvent::factory()->create([
            'id' => 'test_event_daily_2',
            'start_at' => $now->subHour()->format('Y-m-d H:i:s'),
            'end_at' => $now->addHour()->format('Y-m-d H:i:s'),
        ]);

        // イベントデイリーミッションマスターデータを作成
        MstMissionEventDaily::factory()->create([
            'id' => 'event_daily_pvp_challenge_2',
            'mst_event_id' => $mstEvent->id,
            'criterion_type' => $this->targetCriterionType->value,
            'criterion_value' => null,
            'criterion_count' => 2,
            'sort_order' => 1,
        ]);

        for ($i = 1; $i <= 2; $i++) {
            $this->pvpMissionTriggerService->sendStartTriggers();
            $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $now);
            $this->saveAll();
        }

        $usrMission = UsrMissionEvent::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_mission_id', 'event_daily_pvp_challenge_2')
            ->first();

        $this->assertNotNull($usrMission);
        $this->assertEquals(MissionStatus::CLEAR->value, $usrMission->status);
        $this->assertEquals(2, $usrMission->progress);
        $this->assertNotNull($usrMission->cleared_at);
    }

    public function test_PVPイベントデイリーミッション_複数日にわたる達成()
    {
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        // イベントマスターデータを作成
        $mstEvent = MstEvent::factory()->create([
            'id' => 'test_event_daily_3',
            'start_at' => $now->subDays(2)->format('Y-m-d H:i:s'),
            'end_at' => $now->addDays(2)->format('Y-m-d H:i:s'),
        ]);

        // イベントデイリーミッションマスターデータを作成
        MstMissionEventDaily::factory()->create([
            'id' => 'event_daily_pvp_challenge_3',
            'mst_event_id' => $mstEvent->id,
            'criterion_type' => $this->targetCriterionType->value,
            'criterion_value' => null,
            'criterion_count' => 3,
            'sort_order' => 1,
        ]);

        // 1日目：2回挑戦
        for ($i = 1; $i <= 2; $i++) {
            $this->pvpMissionTriggerService->sendStartTriggers();
            $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $now);
            $this->saveAll();
        }

        $usrMissionDay1 = UsrMissionEvent::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_mission_id', 'event_daily_pvp_challenge_3')
            ->first();

        $this->assertNotNull($usrMissionDay1);
        $this->assertEquals(MissionStatus::UNCLEAR->value, $usrMissionDay1->status);
        $this->assertEquals(2, $usrMissionDay1->progress);

        // 2日目：1回挑戦してリセット確認
        $nextDay = $now->addDay();
        $this->setTestNow($nextDay); // テスト時刻を翌日に設定
        $this->pvpMissionTriggerService->sendStartTriggers();
        $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $nextDay);
        $this->saveAll();

        $usrMissionDay2 = UsrMissionEvent::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_mission_id', 'event_daily_pvp_challenge_3')
            ->first();

        $this->assertNotNull($usrMissionDay2);
        $this->assertEquals(MissionStatus::UNCLEAR->value, $usrMissionDay2->status);
        $this->assertEquals(1, $usrMissionDay2->progress);
        $this->assertNull($usrMissionDay2->cleared_at);
    }
}
