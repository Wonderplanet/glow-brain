<?php

namespace Tests\Feature\Domain\Mission;

use Tests\Support\Entities\CurrentUser;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\UseCases\MissionClearOnCallUseCase;
use App\Domain\Resource\Mst\Models\MstMissionAchievement;
use App\Domain\Resource\Mst\Models\MstMissionBeginner;
use App\Http\Responses\ResultData\MissionClearOnCallResultData;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class MissionClearOnCallUseCaseTest extends TestCase
{
    use TestMissionTrait;

    private MissionClearOnCallUseCase $usecase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usecase = app(MissionClearOnCallUseCase::class);
    }

    public function test_exec_ミッションをクリアできる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);

        // mst
        MstMissionAchievement::factory()->createMany([
            [
                'id' => 'achievement-review_completed',
                'criterion_type' => MissionCriterionType::REVIEW_COMPLETED, 'criterion_value' => null, 'criterion_count' => 1,
            ],
        ]);
        MstMissionBeginner::factory()->createMany([
            // 開放、クリア
            [
                'id' => 'beginner-follow_completed',
                'criterion_type' => MissionCriterionType::FOLLOW_COMPLETED, 'criterion_value' => 'https://example.com', 'criterion_count' => 1,'unlock_day' => 2,
            ],
        ]);

        $this->prepareUpdateBeginnerMission($usrUserId);

        // Exercise
        $resultAchievement = $this->usecase->exec($currentUser, MissionType::ACHIEVEMENT->value, 'achievement-review_completed');
        $resultBeginner = $this->usecase->exec($currentUser, MissionType::BEGINNER->value, 'beginner-follow_completed');

        // Verify

        // DB
        // achievement
        $usrMissionNormals = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, MissionType::ACHIEVEMENT);
        $this->assertCount(1, $usrMissionNormals);
        $this->checkUsrMissionNormal($usrMissionNormals->first(), MissionStatus::CLEAR, 1, $now, $now, null);
        // beginner
        $usrMissionNormals = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, MissionType::BEGINNER);
        $this->assertCount(1, $usrMissionNormals);
        $this->checkUsrMissionNormal($usrMissionNormals->first(), MissionStatus::CLEAR, 1, $now, $now, null, MissionUnlockStatus::OPEN, 2);

        // resultData
        $this->assertInstanceOf(MissionClearOnCallResultData::class, $resultAchievement);

        $actuals = $resultAchievement->usrMissionAchievementStatusDataList;
        $this->assertCount(1, $actuals);
        /** @var \App\Http\Responses\Data\UsrMissionStatusData */
        $actual = $actuals->first();
        $this->assertEquals('achievement-review_completed', $actual->getMstMissionId());
        $this->assertEquals(1, $actual->getProgress());
        $this->assertEquals(true, $actual->getIsCleared());
        $this->assertEquals(false, $actual->getIsReceivedReward());

        $this->assertInstanceOf(MissionClearOnCallResultData::class, $resultBeginner);

        $actuals = $resultBeginner->usrMissionBeginnerStatusDataList;
        $this->assertCount(1, $actuals);
        /** @var \App\Http\Responses\Data\UsrMissionStatusData */
        $actual = $actuals->first();
        $this->assertEquals('beginner-follow_completed', $actual->getMstMissionId());
        $this->assertEquals(1, $actual->getProgress());
        $this->assertEquals(true, $actual->getIsCleared());
        $this->assertEquals(false, $actual->getIsReceivedReward());
    }

    public function test_exec_指定したミッションをクリアし依存関係が同じミッションもクリアされる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);

        // mst
        MstMissionAchievement::factory()->createMany([
            [
                'id' => 'achievement1',
                'criterion_type' => MissionCriterionType::REVIEW_COMPLETED, 'criterion_value' => null, 'criterion_count' => 1,
            ],
            [
                'id' => 'achievement2',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 10,
            ],
        ]);
        $this->createMstDependencyEntities('group1', collect(['achievement1', 'achievement2']));

        // usr
        // achievement1がクリアされれば、開放され、ミッション完了になるように準備
        $this->createUsrMissionNormal($usrUserId, MissionType::ACHIEVEMENT, 'achievement2', MissionStatus::CLEAR, 10, $now, null, $now, MissionUnlockStatus::LOCK, 0);

        // Exercise
        $result = $this->usecase->exec($currentUser, MissionType::ACHIEVEMENT->value, 'achievement1');

        // Verify

        // DB
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, MissionType::ACHIEVEMENT);
        $this->checkUsrMissionNormal($usrMissions['achievement1'], MissionStatus::CLEAR, 1, $now, $now, null, MissionUnlockStatus::OPEN, 0);
        $this->checkUsrMissionNormal($usrMissions['achievement2'], MissionStatus::CLEAR, 10, $now, $now, null, MissionUnlockStatus::OPEN, 0);

        // resultData
        $this->assertInstanceOf(MissionClearOnCallResultData::class, $result);

        $actuals = $result->usrMissionAchievementStatusDataList->keyBy->getMstMissionId();
        $this->assertCount(2, $actuals);
        /** @var \App\Http\Responses\Data\UsrMissionStatusData */
        $actual = $actuals['achievement1'];
        $this->assertEquals(1, $actual->getProgress());
        $this->assertEquals(true, $actual->getIsCleared());
        $this->assertEquals(false, $actual->getIsReceivedReward());

        $actual = $actuals['achievement2'];
        $this->assertEquals(10, $actual->getProgress());
        $this->assertEquals(true, $actual->getIsCleared());
        $this->assertEquals(false, $actual->getIsReceivedReward());
    }
}
