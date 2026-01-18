<?php

namespace Tests\Feature\Domain\Mission\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;
use App\Domain\Pvp\Services\PvpMissionTriggerService;
use App\Domain\Resource\Mst\Models\MstMissionDaily;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class MissionPvpWinCountDailyCriterionTest extends TestCase
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

    private function makeMasterData($now): void
    {
        // mst
        MstMissionDaily::factory()->createMany([
            // PVP勝利3回ミッション
            [
                'id' => 'mst_mission_daily_1',
                'criterion_type' => $this->targetCriterionType->value,
                'criterion_value' => null,
                'criterion_count' => 3,
                'sort_order' => 1,
            ],
            // PVP勝利5回ミッション
            [
                'id' => 'mst_mission_daily_2',
                'criterion_type' => $this->targetCriterionType->value,
                'criterion_value' => null,
                'criterion_count' => 5,
                'sort_order' => 2,
            ],
        ]);
    }

    public function test_missionUpdateHandleService_handleAllUpdateTriggeredMissions_進捗が更新されて未クリア()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();
        $this->makeMasterData($now);

        // Exercise
        $this->pvpMissionTriggerService->sendWinTriggers();
        $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $now);
        $this->saveAll();

        // Verify
        $usrMissions = UsrMissionNormal::query()->where('usr_user_id', $usrUserId)->get()->keyBy->getMstMissionId();
        $this->assertCount(2, $usrMissions);
        $this->checkUsrMissionNormal($usrMissions['mst_mission_daily_1'], MissionStatus::UNCLEAR, 1, $now, null, null);
        $this->checkUsrMissionNormal($usrMissions['mst_mission_daily_2'], MissionStatus::UNCLEAR, 1, $now, null, null);
    }

    public function test_pvp_missionUpdateHandleService_handleAllUpdateTriggeredMissions_進捗が更新されてクリア()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();
        $this->makeMasterData($now);

        UsrMissionNormal::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_mission_id' => 'mst_mission_daily_1',
            'mission_type' => MissionType::DAILY->getIntValue(), // DAILY
            'status' => MissionStatus::UNCLEAR,
            'progress' => 2,
            'cleared_at' => null,
            'received_reward_at' => null,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);
        $usrMissions = UsrMissionNormal::query()->where('usr_user_id', $usrUserId)->get()->keyBy->getMstMissionId();

        // Exercise
        $this->pvpMissionTriggerService->sendWinTriggers();
        $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $now);
        $this->saveAll();

        // Verify
        $usrMissions = UsrMissionNormal::query()->where('usr_user_id', $usrUserId)->get()->keyBy->getMstMissionId();
        $this->assertCount(2, $usrMissions);
        $this->checkUsrMissionNormal($usrMissions['mst_mission_daily_1'], MissionStatus::CLEAR, 3, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['mst_mission_daily_2'], MissionStatus::UNCLEAR, 1, $now, null, null);
    }
}