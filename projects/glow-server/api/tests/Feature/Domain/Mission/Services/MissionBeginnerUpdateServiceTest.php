<?php

namespace Tests\Feature\Domain\Mission;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\MissionManager;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;
use App\Domain\Mission\Services\MissionUpdateService;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class MissionBeginnerUpdateServiceTest extends TestCase
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

    public function test_updateTriggeredMissions_未開放でも進捗が進んでいる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();

        // 現在日時が、ミッション開放から6日目になるように設定
        $this->prepareUpdateBeginnerMission($usrUserId, $now->subDays(5)->toDateTimeString());

        // mst
        /**
         * トリガーされるミッション
         */
        // 開放、クリア
        $this->createMstMission(MissionType::BEGINNER, 'beginner1', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 1, null, 0, 'group1', 6);
        // 開放、未クリア
        $this->createMstMission(MissionType::BEGINNER, 'beginner2', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 3, null, 0, 'group1', 6);
        // 未開放、クリア
        $this->createMstMission(MissionType::BEGINNER, 'beginner3', MissionCriterionType::COIN_COLLECT, null, 10, null, 0, null, 7);
        // 未開放、未クリア
        $this->createMstMission(MissionType::BEGINNER, 'beginner10', MissionCriterionType::DEFEAT_ENEMY_COUNT, null, 100, null, 0, null, 7);
        /**
         * 複合ミッション
         */
        // 開放、クリア
        $this->createMstMission(MissionType::BEGINNER, 'beginner20', MissionCriterionType::MISSION_CLEAR_COUNT, null, 1, null, 0, null, 6);
        // 開放、未クリア
        $this->createMstMission(MissionType::BEGINNER, 'beginner21', MissionCriterionType::MISSION_CLEAR_COUNT, null, 100, null, 0, null, 6);
        // 未開放、クリア
        $this->createMstMission(MissionType::BEGINNER, 'beginner22', MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'group1', 1, null, 0, null, 7);
        // 未開放、未クリア
        $this->createMstMission(MissionType::BEGINNER, 'beginner23', MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'group1', 100, null, 0, null, 7);
        /**
         * トリガーされないミッション ユーザーレコードされない
         */
        // 開放、未クリア
        $this->createMstMission(MissionType::BEGINNER, 'beginner31', MissionCriterionType::USER_LEVEL, null, 1, null, 0, null, 6);
        // 未開放、未クリア
        $this->createMstMission(MissionType::BEGINNER, 'beginner32', MissionCriterionType::USER_LEVEL, null, 1, null, 0, null, 7);

        // トリガー
        $this->missionManager->addTriggers(collect([
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value,
                'stage1',
                2,
            ),
            new MissionTrigger(
                MissionCriterionType::COIN_COLLECT->value,
                null,
                100,
            ),
            new MissionTrigger(
                MissionCriterionType::DEFEAT_ENEMY_COUNT->value,
                null,
                50,
            ),
        ]), MissionType::BEGINNER);

        // Exercise
        $this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);
        $this->saveAll();

        // Verify
        $usrMissions = UsrMissionNormal::query()->where('usr_user_id', $usrUserId)->get()->keyBy('mst_mission_id');
        $this->assertCount(8, $usrMissions);
        $this->checkUsrMissionNormal($usrMissions['beginner1'], MissionStatus::CLEAR, 1, $now, $now, null, MissionUnlockStatus::OPEN, 6);
        $this->checkUsrMissionNormal($usrMissions['beginner2'], MissionStatus::UNCLEAR, 2, $now, null, null, MissionUnlockStatus::OPEN, 6);
        $this->checkUsrMissionNormal($usrMissions['beginner3'], MissionStatus::CLEAR, 10, $now, $now, null, MissionUnlockStatus::LOCK, 6);
        $this->checkUsrMissionNormal($usrMissions['beginner10'], MissionStatus::UNCLEAR, 50, $now, null, null, MissionUnlockStatus::LOCK, 6);
        $this->checkUsrMissionNormal($usrMissions['beginner20'], MissionStatus::CLEAR, 1, $now, $now, null, MissionUnlockStatus::OPEN, 6);
        $this->checkUsrMissionNormal($usrMissions['beginner21'], MissionStatus::UNCLEAR, 1, $now, null, null, MissionUnlockStatus::OPEN, 6);
        $this->checkUsrMissionNormal($usrMissions['beginner22'], MissionStatus::CLEAR, 1, $now, $now, null, MissionUnlockStatus::LOCK, 6);
        $this->checkUsrMissionNormal($usrMissions['beginner23'], MissionStatus::UNCLEAR, 1, $now, null, null, MissionUnlockStatus::LOCK, 6);
    }
}
