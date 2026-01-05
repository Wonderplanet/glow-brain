<?php

namespace Tests\Feature\Domain\Mission\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionEvent;
use App\Domain\Pvp\Services\PvpMissionTriggerService;
use App\Domain\Resource\Mst\Models\MstMissionEvent;
use App\Domain\Resource\Mst\Models\MstEvent;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class MissionPvpWinCountEventCriterionTest extends TestCase
{
    use TestMissionTrait;

    private MissionCriterionType $targetCriterionType = MissionCriterionType::PVP_WIN_COUNT;

    private PvpMissionTriggerService $pvpMissionTriggerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pvpMissionTriggerService = $this->app->make(PvpMissionTriggerService::class);
        $this->missionUpdateHandleService = $this->app->make(\App\Domain\Mission\Services\MissionUpdateHandleService::class);
    }

    public function test_PVPイベントミッション_勝利で進捗更新()
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
            'id' => 'event_pvp_win_15',
            'mst_event_id' => $mstEvent->id,
            'criterion_type' => $this->targetCriterionType->value,
            'criterion_value' => null,
            'criterion_count' => 15,
            'sort_order' => 1,
        ]);

        $this->pvpMissionTriggerService->sendWinTriggers();
        $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $now);
        $this->saveAll();

        $usrMission = UsrMissionEvent::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_mission_id', 'event_pvp_win_15')
            ->first();

        $this->assertNotNull($usrMission);
        $this->assertEquals(MissionStatus::UNCLEAR->value, $usrMission->status);
        $this->assertEquals(1, $usrMission->progress);
    }

    public function test_PVPイベントミッション_勝利で達成()
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
            'id' => 'event_pvp_win_3',
            'mst_event_id' => $mstEvent->id,
            'criterion_type' => $this->targetCriterionType->value,
            'criterion_value' => null,
            'criterion_count' => 3,
            'sort_order' => 1,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $this->pvpMissionTriggerService->sendWinTriggers();
            $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $now);
            $this->saveAll();
        }

        $usrMission = UsrMissionEvent::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_mission_id', 'event_pvp_win_3')
            ->first();

        $this->assertNotNull($usrMission);
        $this->assertEquals(MissionStatus::CLEAR->value, $usrMission->status);
        $this->assertEquals(3, $usrMission->progress);
        $this->assertNotNull($usrMission->cleared_at);
    }
}