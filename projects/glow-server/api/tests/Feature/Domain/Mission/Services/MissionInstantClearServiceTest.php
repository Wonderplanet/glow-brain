<?php

namespace Tests\Feature\Domain\Mission;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;
use App\Domain\Mission\Models\UsrMissionStatus;
use App\Domain\Mission\Services\MissionInstantClearService;
use App\Domain\Resource\Mst\Models\OprMasterReleaseControl;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Models\UsrUserParameter;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class MissionInstantClearServiceTest extends TestCase
{
    use TestMissionTrait;

    private MissionInstantClearService $missionInstantClearService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->missionInstantClearService = $this->app->make(MissionInstantClearService::class);
    }

    public function test_execInstantClear_ユニット関連の即時達成判定ができている()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-11-06 00:00:00');

        // mst

        // mission
        //   トリガーされる
        //      unit1
        $this->createMstMission(MissionType::ACHIEVEMENT, 'trigger1', MissionCriterionType::SPECIFIC_UNIT_LEVEL, 'unit1', 10);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'trigger2', MissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT, 'unit1', 2);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'trigger3', MissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT, 'unit1', 3);
        //      unit2
        $this->createMstMission(MissionType::ACHIEVEMENT, 'trigger4', MissionCriterionType::SPECIFIC_UNIT_LEVEL, 'unit2', 20);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'trigger5', MissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT, 'unit2', 3);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'trigger6', MissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT, 'unit2', 4);
        //      開放条件あり
        $this->createMstMission(MissionType::ACHIEVEMENT, 'trigger7_lock_lock', MissionCriterionType::SPECIFIC_UNIT_LEVEL, 'unit1', 10, null,0,null,0, MissionCriterionType::LOGIN_COUNT, null, 999);
        //   トリガーされない：ユニット未所持
        $this->createMstMission(MissionType::ACHIEVEMENT, 'notTrigger1', MissionCriterionType::SPECIFIC_UNIT_LEVEL, 'invalidUnit', 10);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'notTrigger2', MissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT, 'invalidUnit', 2);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'notTrigger3', MissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT, 'invalidUnit', 3);
        //   トリガーされない：即時達成判定対象外のcriterion_type
        $this->createMstMission(MissionType::ACHIEVEMENT, 'notTrigger51', MissionCriterionType::COIN_COLLECT, null, 999);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'notTrigger52', MissionCriterionType::UNIT_LEVEL, null, 888);
        // master control
        OprMasterReleaseControl::factory()->create(['client_data_hash' => 'newMstHash']);

        // usr
        UsrMissionStatus::factory()->create([
            'latest_mst_hash' => 'oldMstHash',
        ]);
        UsrUnit::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1', 'level' => 10, 'rank' => 2, 'grade_level' => 3],
            ['usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2', 'level' => 20, 'rank' => 3, 'grade_level' => 4],
        ]);
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId, 'exp' => 0,]);

        // Exercise
        $this->missionInstantClearService->execInstantClear($usrUserId, $now);
        $this->saveAll();

        // Verify

        // アチーブメント（即時達成対象）
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, MissionType::ACHIEVEMENT);
        $this->assertCount(7, $usrMissions); // その他は未クリアかつ未解放
        // クリア
        $this->checkUsrMissionNormal($usrMissions['trigger1'], MissionStatus::CLEAR, 10, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['trigger2'], MissionStatus::CLEAR, 2, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['trigger3'], MissionStatus::CLEAR, 3, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['trigger4'], MissionStatus::CLEAR, 20, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['trigger5'], MissionStatus::CLEAR, 3, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['trigger6'], MissionStatus::CLEAR, 4, $now, $now, null, MissionUnlockStatus::OPEN, 0);

        // 即時達成されても未開放なら未開放のまま
        $this->checkUsrMissionNormal($usrMissions['trigger7_lock_lock'], MissionStatus::CLEAR, 10, $now, $now, null, MissionUnlockStatus::LOCK, 0);

        // その他ミッションタイプは進捗変動なし。レコード作成されず、アチーブメントのレコードだけ。
        $this->assertEquals(
            7,
            UsrMissionNormal::query()->where('usr_user_id', $usrUserId)->get()->count(),
        );
    }
}
