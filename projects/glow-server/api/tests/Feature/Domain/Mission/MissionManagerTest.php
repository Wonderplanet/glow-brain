<?php

namespace Tests\Feature\Domain\Mission;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\MissionManager;
use App\Domain\Resource\Mst\Models\MstMissionAchievement;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\User\Models\UsrUserParameter;
use Carbon\CarbonImmutable;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class MissionManagerTest extends TestCase
{
    use TestMissionTrait;

    private MissionManager $missionManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->missionManager = $this->app->make(MissionManager::class);
    }

    public function test_handleAllUpdateTriggeredMissions_ミッション同士に依存関係がある状態で想定通り判定できることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = CarbonImmutable::now();

        // mst
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-1', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 1); // triggered, open, clear
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-2', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 3); // triggered, open, clear
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-3', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 8); // triggered, open, unclear
        $this->createMstDependencyEntities('dependency1', collect(['achievement1-1', 'achievement1-2', 'achievement1-3']));

        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement2-1', MissionCriterionType::COIN_COLLECT,                  null, 10); // triggered, open, clear
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement2-2', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 1); // triggered, open, clear
        $this->createMstDependencyEntities('dependency2', collect(['achievement2-1', 'achievement2-2']));

        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement3-1', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 1); // triggered, open, clear
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement3-2', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage2', 1); // notTriggered, open, unclear
        $this->createMstDependencyEntities('dependency3', collect(['achievement3-1', 'achievement3-2']));

        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement4-1', MissionCriterionType::DEFEAT_ENEMY_COUNT,            null, 50); // notTriggered, open, unclear
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement4-2', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 5); // triggered, close, clear
        $this->createMstDependencyEntities('dependency4', collect(['achievement4-1', 'achievement4-2']));

        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement5-1', MissionCriterionType::COIN_COLLECT,                  null, 30); // triggered, open, unclear
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement5-2', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 8); // triggered, close, unclear
        $this->createMstDependencyEntities('dependency5', collect(['achievement5-1', 'achievement5-2']));

        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement6-1', MissionCriterionType::COIN_COLLECT,                  null, 10); // triggered, open, clear
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement6-2', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage5', 1); // notTriggered, close, cleared
        $this->createMstDependencyEntities('dependency6', collect(['achievement6-1', 'achievement6-2']));

        // 依存関係なし かつ 開放条件あり
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement10',  MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 3, null,0,null,0, MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 1); // triggered    open,  triggered    clear
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement11',  MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 3, null,0,null,0, MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1',99); // triggered    close, triggered    clear
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement12',  MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage2', 3, null,0,null,0, MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1',99); // triggered    close, notTriggered unclear
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement13',  MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 3, null,0,null,0, MissionCriterionType::USER_LEVEL,                    null, 10); // notTriggered close, triggered    clear
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement14',  MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 8, null,0,null,0, MissionCriterionType::USER_LEVEL,                    null, 10); // notTriggered close, triggered    unclear
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement15',  MissionCriterionType::DEFEAT_ENEMY_COUNT,            null, 50, null,0,null,0, MissionCriterionType::USER_LEVEL,                    null, 10); // notTriggered close, notTriggered unclear (no record)

        // usr
        // mission
        $this->createUsrMissionNormal($usrUserId, MissionType::ACHIEVEMENT, 'achievement6-2', MissionStatus::CLEAR, 1, $now, null, $now, MissionUnlockStatus::LOCK, 0);
        // other
        $usrUserParameter = UsrUserParameter::factory()->create(
            ['usr_user_id' => $usrUserId, 'level' => 1, 'coin' => 10],
        );

        // trigger
        $this->missionManager->addTriggers(collect([
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value,
                'stage1',
                5,
            ),
            new MissionTrigger(
                MissionCriterionType::COIN_COLLECT->value,
                null,
                10,
            ),
        ]));

        // Exercise
        $this->handleAllUpdateTriggeredMissions($usrUserId, $now);

        // Verify
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, MissionType::ACHIEVEMENT);
        $this->assertCount(18, $usrMissions);
        $this->checkUsrMissionNormal($usrMissions['achievement1-1'], MissionStatus::CLEAR, 1, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-2'], MissionStatus::CLEAR, 3, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-3'], MissionStatus::UNCLEAR, 5, $now, null, null, MissionUnlockStatus::OPEN, 0);

        $this->checkUsrMissionNormal($usrMissions['achievement2-1'], MissionStatus::CLEAR, 10, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement2-2'], MissionStatus::CLEAR, 1, $now, $now, null, MissionUnlockStatus::OPEN, 0);

        $this->checkUsrMissionNormal($usrMissions['achievement3-1'], MissionStatus::CLEAR, 1, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement3-2'], MissionStatus::UNCLEAR, 0, $now, null, null, MissionUnlockStatus::OPEN, 0);

        $this->checkUsrMissionNormal($usrMissions['achievement4-1'], MissionStatus::UNCLEAR, 0, $now, null, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement4-2'], MissionStatus::CLEAR, 5, $now, $now, null, MissionUnlockStatus::LOCK, 0);

        $this->checkUsrMissionNormal($usrMissions['achievement5-1'], MissionStatus::UNCLEAR, 10, $now, null, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement5-2'], MissionStatus::UNCLEAR, 5, $now, null, null, MissionUnlockStatus::LOCK, 0);

        $this->checkUsrMissionNormal($usrMissions['achievement6-1'], MissionStatus::CLEAR, 10, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement6-2'], MissionStatus::CLEAR, 1, $now, $now, null, MissionUnlockStatus::OPEN, 0);

        $this->checkUsrMissionNormal($usrMissions['achievement10'], MissionStatus::CLEAR, 3, $now, $now, null, MissionUnlockStatus::OPEN, 1);
        $this->checkUsrMissionNormal($usrMissions['achievement11'], MissionStatus::CLEAR, 3, $now, $now, null, MissionUnlockStatus::LOCK, 5);
        $this->checkUsrMissionNormal($usrMissions['achievement12'], MissionStatus::UNCLEAR, 0, $now, null, null, MissionUnlockStatus::LOCK, 5);
        $this->checkUsrMissionNormal($usrMissions['achievement13'], MissionStatus::CLEAR, 3, $now, $now, null, MissionUnlockStatus::LOCK, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement14'], MissionStatus::UNCLEAR, 5, $now, null, null, MissionUnlockStatus::LOCK, 0);
        // achievement15 レコードなし
    }

    public function test_handleAllUpdateTriggeredMissions_依存関係のあるミッション群が2つあって同じミッションが含まれる場合でも判定ができる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = CarbonImmutable::now();

        // mst
        MstMissionAchievement::factory()->createMany([
            [
                'id' => 'achievement1',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'criterion_value' => 'stage1', 'criterion_count' => 1,
            ],
            [
                'id' => 'achievement2',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'criterion_value' => 'stage1', 'criterion_count' => 3,
            ],
            [
                'id' => 'achievement3',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'criterion_value' => 'stage1', 'criterion_count' => 5,
            ],
            [
                'id' => 'achievement4',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'criterion_value' => 'stage2', 'criterion_count' => 1,
            ],
            [
                'id' => 'achievement5',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'criterion_value' => 'stage1', 'criterion_count' => 10,
            ],
        ]);
        // 同じミッションを含む依存関係が2つある。（'achievement1', 'achievement2', 'achievement3'が同じ）
        $this->createMstDependencyEntities('dependency1', collect(['achievement1', 'achievement2', 'achievement3', 'achievement4']));
        $this->createMstDependencyEntities('dependency2', collect(['achievement1', 'achievement2', 'achievement3', 'achievement5']));
        // usr
        $usrStage = UsrStage::factory()->create(
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'stage1', 'clear_count' => 5],
        );

        $missionTriggers = collect([
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value,
                'stage1',
                5,
            ),
        ]);
        $this->missionManager->addTriggers($missionTriggers);

        // Exercise
        $this->handleAllUpdateTriggeredMissions($usrUserId, $now);

        // Verify
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, MissionType::ACHIEVEMENT);
        $this->assertCount(5, $usrMissions);
        $this->checkUsrMissionNormal($usrMissions['achievement1'], MissionStatus::CLEAR, 1, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement2'], MissionStatus::CLEAR, 3, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement3'], MissionStatus::CLEAR, 5, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement4'], MissionStatus::UNCLEAR, 0, $now, null, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement5'], MissionStatus::UNCLEAR, 5, $now, null, null, MissionUnlockStatus::OPEN, 0);
    }

    /**
     * 確認内容
     * - トリガーと更新処理を続けて2回行い、段階的に進捗を進めることができていることも確認
     */
    public function test_handleAllUpdateTriggeredMissions_MissionClearCountミッションを達成できる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = CarbonImmutable::now();

        // mst
        // 1回目でクリア
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 1);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement2', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 3);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement3', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 5);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement4', MissionCriterionType::MISSION_CLEAR_COUNT, null, 3);
        // 2回目でクリア
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement5', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 6);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement6', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 8);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement7', MissionCriterionType::MISSION_CLEAR_COUNT, null, 5);

        /**
         * 1回目のトリガー
         */
        // trigger
        $this->missionManager->addTrigger(new MissionTrigger(MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value, 'stage1', 5));
        // Exercise
        $this->handleAllUpdateTriggeredMissions($usrUserId, $now);
        // Verify
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, MissionType::ACHIEVEMENT);
        $this->assertCount(7, $usrMissions);
        $this->checkUsrMissionNormal($usrMissions['achievement1'], MissionStatus::CLEAR, 1, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement2'], MissionStatus::CLEAR, 3, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement3'], MissionStatus::CLEAR, 5, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement4'], MissionStatus::CLEAR, 3, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement5'], MissionStatus::UNCLEAR, 5, $now, null, null);
        $this->checkUsrMissionNormal($usrMissions['achievement6'], MissionStatus::UNCLEAR, 5, $now, null, null);
        $this->checkUsrMissionNormal($usrMissions['achievement7'], MissionStatus::UNCLEAR, 3, $now, null, null);

        /**
         * 2回目のトリガー
         */
        // trigger
        $this->missionManager->addTrigger(new MissionTrigger(MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value, 'stage1', 8));
        // Exercise
        $this->handleAllUpdateTriggeredMissions($usrUserId, $now);
        // Verify
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, MissionType::ACHIEVEMENT);
        $this->assertCount(7, $usrMissions);
        $this->checkUsrMissionNormal($usrMissions['achievement1'], MissionStatus::CLEAR, 1, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement2'], MissionStatus::CLEAR, 3, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement3'], MissionStatus::CLEAR, 5, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement4'], MissionStatus::CLEAR, 3, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement5'], MissionStatus::CLEAR, 6, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement6'], MissionStatus::CLEAR, 8, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement7'], MissionStatus::CLEAR, 5, $now, $now, null);
    }

    /**
     * 確認内容
     * - group1,2を用意して、グループごとのSpecificMissionClearCount進捗を別管理できていること
     * - トリガーと更新処理を続けて2回行い、段階的に進捗を進めることができていることも確認
     */
    public function test_handleAllUpdateTriggeredMissions_SpecificMissionClearCountミッションを達成できる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = CarbonImmutable::now();

        // mst
        // group1 1回目でクリア
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 1, null, 0, 'group1');
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement2', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 3, null, 0, 'group1');
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement3', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 5, null, 0, 'group1');
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement4', MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'group1', 3);
        // group1 2回目でクリア
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement5', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 6, null, 0, 'group1');
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement6', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 8, null, 0, 'group1');
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement7', MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'group1', 5);
        // group2 1回目でクリア
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement21', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 1, null, 0, 'group2');
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement22', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 3, null, 0, 'group2');
        // group2 2回目でクリア
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement23', MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'group2', 3);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement24', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 6, null, 0, 'group2');

        /**
         * 1回目のトリガー
         */
        // trigger
        $this->missionManager->addTrigger(
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value,
                'stage1',
                5,
            )
        );
        // Exercise
        $this->handleAllUpdateTriggeredMissions($usrUserId, $now);
        // Verify
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, MissionType::ACHIEVEMENT);
        $this->assertCount(11, $usrMissions);
        $this->checkUsrMissionNormal($usrMissions['achievement1'], MissionStatus::CLEAR, 1, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement2'], MissionStatus::CLEAR, 3, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement3'], MissionStatus::CLEAR, 5, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement4'], MissionStatus::CLEAR, 3, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement5'], MissionStatus::UNCLEAR, 5, $now, null, null);
        $this->checkUsrMissionNormal($usrMissions['achievement6'], MissionStatus::UNCLEAR, 5, $now, null, null);
        $this->checkUsrMissionNormal($usrMissions['achievement7'], MissionStatus::UNCLEAR, 3, $now, null, null);
        $this->checkUsrMissionNormal($usrMissions['achievement21'], MissionStatus::CLEAR, 1, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement22'], MissionStatus::CLEAR, 3, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement23'], MissionStatus::UNCLEAR, 2, $now, null, null);
        $this->checkUsrMissionNormal($usrMissions['achievement24'], MissionStatus::UNCLEAR, 5, $now, null, null);

        /**
         * 2回目のトリガー
         */
        // trigger
        $this->missionManager->addTrigger(
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value,
                'stage1',
                5,
            )
        );
        // Exercise
        $this->handleAllUpdateTriggeredMissions($usrUserId, $now);
        // Verify
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, MissionType::ACHIEVEMENT);
        $this->assertCount(11, $usrMissions);
        $this->checkUsrMissionNormal($usrMissions['achievement1'], MissionStatus::CLEAR, 1, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement2'], MissionStatus::CLEAR, 3, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement3'], MissionStatus::CLEAR, 5, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement4'], MissionStatus::CLEAR, 3, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement5'], MissionStatus::CLEAR, 6, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement6'], MissionStatus::CLEAR, 8, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement7'], MissionStatus::CLEAR, 5, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement21'], MissionStatus::CLEAR, 1, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement22'], MissionStatus::CLEAR, 3, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement23'], MissionStatus::CLEAR, 3, $now, $now, null);
        $this->checkUsrMissionNormal($usrMissions['achievement24'], MissionStatus::CLEAR, 6, $now, $now, null);
    }

    public function test_handleAllUpdateTriggeredMissions_missionClearCountを複数回含む依存関係を持つミッション群の判定ができる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = CarbonImmutable::now();

        // mst
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-1', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 1);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-2', MissionCriterionType::MISSION_CLEAR_COUNT, null, 1);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-3', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 2);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-4', MissionCriterionType::MISSION_CLEAR_COUNT, null, 2);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-5', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 3);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-6', MissionCriterionType::MISSION_CLEAR_COUNT, null, 3);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-7', MissionCriterionType::MISSION_CLEAR_COUNT, null, 4);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-8', MissionCriterionType::MISSION_CLEAR_COUNT, null, 5);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-9', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 4);
        $this->createMstDependencyEntities('
            dependency1',
            collect([
                'achievement1-1',
                'achievement1-2',
                'achievement1-3',
                'achievement1-4',
                'achievement1-5',
                'achievement1-6',
                'achievement1-7',
                'achievement1-8',
                'achievement1-9',
            ])
        );

        // trigger
        $this->missionManager->addTriggers(collect([
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value,
                'stage1',
                5,
            ),
        ]));

        // Exercise
        $this->handleAllUpdateTriggeredMissions($usrUserId, $now);

        // Verify
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, MissionType::ACHIEVEMENT);
        $this->assertCount(9, $usrMissions);
        $this->checkUsrMissionNormal($usrMissions['achievement1-1'], MissionStatus::CLEAR, 1, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-2'], MissionStatus::CLEAR, 1, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-3'], MissionStatus::CLEAR, 2, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-4'], MissionStatus::CLEAR, 2, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-5'], MissionStatus::CLEAR, 3, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-6'], MissionStatus::CLEAR, 3, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-7'], MissionStatus::UNCLEAR, 3, $now, null, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-8'], MissionStatus::UNCLEAR, 3, $now, null, null, MissionUnlockStatus::LOCK, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-9'], MissionStatus::CLEAR, 4, $now, $now, null, MissionUnlockStatus::LOCK, 0);
    }

    public function test_handleAllUpdateTriggeredMissions_CompositeMissionを複雑に含む依存関係を持つ複数のミッション群の判定ができる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = CarbonImmutable::now();

        // mst
        // 依存関係グループ1
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-1', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 1);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-2', MissionCriterionType::MISSION_CLEAR_COUNT, null, 1);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-3', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 2);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-4', MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT, 'group2', 2);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-5', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 3);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-6', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 100);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1-7', MissionCriterionType::MISSION_CLEAR_COUNT, null, 100);
        // 依存関係グループ2
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement2-1', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 1, null, 0, 'group2');
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement2-2', MissionCriterionType::MISSION_CLEAR_COUNT, null, 5);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement2-3', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 2);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement2-4', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 3);
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement2-5', MissionCriterionType::MISSION_CLEAR_COUNT, null, 100);
        // 依存関係なし
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement3', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 2, null, 0, 'group2');
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement4', MissionCriterionType::MISSION_CLEAR_COUNT, null, 6);
        // 依存関係設定
        $this->createMstDependencyEntities(
            'dependency1',
            collect([
                'achievement1-1',
                'achievement1-2',
                'achievement1-3',
                'achievement1-4',
                'achievement1-5',
                'achievement1-6',
                'achievement1-7'
            ])
        );
        $this->createMstDependencyEntities(
            'dependency2',
            collect([
                'achievement2-1',
                'achievement2-2',
                'achievement2-3',
                'achievement2-4',
                'achievement2-5'
            ]
        ));

        // trigger
        $this->missionManager->addTriggers(collect([
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value,
                'stage1',
                5,
            ),
        ]));

        // Exercise
        $this->handleAllUpdateTriggeredMissions($usrUserId, $now);

        // Verify
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, MissionType::ACHIEVEMENT);
        $this->assertCount(14, $usrMissions);
        $this->checkUsrMissionNormal($usrMissions['achievement1-1'], MissionStatus::CLEAR, 1, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-2'], MissionStatus::CLEAR, 1, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-3'], MissionStatus::CLEAR, 2, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-4'], MissionStatus::CLEAR, 2, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-5'], MissionStatus::CLEAR, 3, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-6'], MissionStatus::UNCLEAR, 5, $now, null, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement1-7'], MissionStatus::UNCLEAR, 7, $now, null, null, MissionUnlockStatus::LOCK, 0);

        $this->checkUsrMissionNormal($usrMissions['achievement2-1'], MissionStatus::CLEAR, 1, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement2-2'], MissionStatus::CLEAR, 5, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement2-3'], MissionStatus::CLEAR, 2, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement2-4'], MissionStatus::CLEAR, 3, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement2-5'], MissionStatus::UNCLEAR, 7, $now, null, null, MissionUnlockStatus::OPEN, 0);

        $this->checkUsrMissionNormal($usrMissions['achievement3'], MissionStatus::CLEAR, 2, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement4'], MissionStatus::CLEAR, 6, $now, $now, null, MissionUnlockStatus::OPEN, 0);
    }
}
