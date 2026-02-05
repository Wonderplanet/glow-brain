<?php

namespace Tests\Feature\Domain\Mission\Criteria;

use App\Domain\AdventBattle\Entities\AdventBattleInGameBattleLog;
use App\Domain\AdventBattle\Services\AdventBattleMissionTriggerService;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionLimitedTermCategory;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTerm;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class MissionAdventBattleTotalScoreCriterionTest extends TestCase
{
    use TestMissionTrait;

    private MissionCriterionType $targetCriterionType = MissionCriterionType::ADVENT_BATTLE_TOTAL_SCORE;

    private AdventBattleMissionTriggerService $adventBattleMissionTriggerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adventBattleMissionTriggerService = $this->app->make(AdventBattleMissionTriggerService::class);
    }

    private function makeMasterData($now): void
    {
        // mst
        MstMissionLimitedTerm::factory()->createMany([
            // 開催中の期間限定ミッション
            [
                'id' => 'mst_mission_limited_term_1',
                'progress_group_key' => 'progress_group_key_1',
                'criterion_type' => $this->targetCriterionType->value,
                'criterion_value' => null,
                'criterion_count' => 100,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE->value,
                'start_at' => $now->subHours(1)->toDateTimeString(),
                'end_at' => $now->addHours(1)->toDateTimeString(),
            ],
            // 未開催の期間限定ミッション
            [
                'id' => 'mst_mission_limited_term_2',
                'progress_group_key' => 'progress_group_key_2',
                'criterion_type' => $this->targetCriterionType->value,
                'criterion_value' => null,
                'criterion_count' => 100,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE->value,
                'start_at' => $now->subHours(2)->toDateTimeString(),
                'end_at' => $now->subHours(1)->toDateTimeString(),
            ],
        ]);
    }

    public function test_missionUpdateHandleService_handleAllUpdateTriggeredMissions_進捗が更新されて未クリア()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();
        $this->makeMasterData($now);

        $adventBattleInGameBattleLog = new AdventBattleInGameBattleLog(
            0,
            0,
            50,
            collect(),
            0,
            collect(),
            collect(), // artworkPartyStatus
        );

        // Exercise
        $this->adventBattleMissionTriggerService->sendEndTriggers($adventBattleInGameBattleLog);
        $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $now);
        $this->saveAll();

        // Verify
        $usrMissions = UsrMissionLimitedTerm::query()->where('usr_user_id', $usrUserId)->get()->keyBy->getMstMissionId();
        $this->assertCount(1, $usrMissions);
        $this->checkUsrMissionLimitedTerm($usrMissions['mst_mission_limited_term_1'], MissionStatus::UNCLEAR, 50, $now, null, null);
    }

    public function test_missionUpdateHandleService_handleAllUpdateTriggeredMissions_進捗が更新されてクリア()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();
        $this->makeMasterData($now);
        UsrMissionLimitedTerm::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_mission_limited_term_id' => 'mst_mission_limited_term_1',
            'status' => MissionStatus::UNCLEAR,
            'progress' => 50,
            'latest_reset_at' => $now,
        ]);
        $adventBattleInGameBattleLog = new AdventBattleInGameBattleLog(
            0,
            0,
            50,
            collect(),
            0,
            collect(),
            collect(), // artworkPartyStatus
        );

        // Exercise
        $this->adventBattleMissionTriggerService->sendEndTriggers($adventBattleInGameBattleLog);
        $this->missionUpdateHandleService->handleAllUpdateTriggeredMissions($usrUserId, $now);
        $this->saveAll();

        // Verify
        $usrMissions = UsrMissionLimitedTerm::query()->where('usr_user_id', $usrUserId)->get()->keyBy->getMstMissionId();
        $this->assertCount(1, $usrMissions);
        $this->checkUsrMissionLimitedTerm($usrMissions['mst_mission_limited_term_1'], MissionStatus::CLEAR, 50+50, $now, $now, null);
    }
}
