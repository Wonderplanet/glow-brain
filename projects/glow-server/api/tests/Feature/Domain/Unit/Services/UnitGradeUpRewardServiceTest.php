<?php

namespace Tests\Feature\Domain\Unit\Services;

use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Resource\Entities\Rewards\UnitGradeUpReward;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitGradeUpReward;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\Repositories\UsrUnitRepository;
use App\Domain\Unit\Services\UnitGradeUpRewardService;
use Tests\TestCase;

class UnitGradeUpRewardServiceTest extends TestCase
{
    private UnitGradeUpRewardService $unitGradeUpRewardService;
    private UsrUnitRepository $usrUnitRepository;
    private RewardDelegator $rewardDelegator;

    public function setUp(): void
    {
        parent::setUp();
        $this->unitGradeUpRewardService = $this->app->make(UnitGradeUpRewardService::class);
        $this->usrUnitRepository = $this->app->make(UsrUnitRepository::class);
        $this->rewardDelegator = $this->app->make(RewardDelegator::class);
    }

    public function testGrantGradeUpReward_グレードアップ報酬を正常に付与できる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstUnit = MstUnit::factory()->create()->toEntity();
        $mstArtwork = MstArtwork::factory()->create()->toEntity();
        $gradeLevel = 5;
        $mstUnitGradeUpRewardId = 'unit_grade_up_reward_1';

        MstUnitGradeUpReward::factory()->create([
            'id' => $mstUnitGradeUpRewardId,
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $gradeLevel,
            'resource_type' => 'Artwork',
            'resource_id' => $mstArtwork->getId(),
            'resource_amount' => 1,
        ]);

        $usrUnitEloquent = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $gradeLevel,
            'last_reward_grade_level' => 1,
        ]);
        $usrUnit = $this->usrUnitRepository->getById($usrUnitEloquent->id, $usrUser->getId());

        // Exercise
        $result = $this->unitGradeUpRewardService->grantGradeUpReward($usrUnit);
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $this->assertTrue($result);

        // フラグが更新されていること
        $usrUnit = $this->usrUnitRepository->getById($usrUnit->getId(), $usrUser->getId());
        $this->assertEquals($gradeLevel, $usrUnit->getLastRewardGradeLevel());
    }

    public function testGrantGradeUpReward_既に受け取り済みの場合はfalseを返す()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstUnit = MstUnit::factory()->create()->toEntity();
        $mstArtwork = MstArtwork::factory()->create()->toEntity();
        $gradeLevel = 5;

        MstUnitGradeUpReward::factory()->create([
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $gradeLevel,
            'resource_type' => 'Artwork',
            'resource_id' => $mstArtwork->getId(),
            'resource_amount' => 1,
        ]);

        $usrUnitEloquent = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $gradeLevel,
            'last_reward_grade_level' => $gradeLevel,
        ]);
        $usrUnit = $this->usrUnitRepository->getById($usrUnitEloquent->id, $usrUser->getId());

        // Exercise
        $result = $this->unitGradeUpRewardService->grantGradeUpReward($usrUnit);

        // Verify
        $this->assertFalse($result);
    }

    public function testGrantGradeUpReward_報酬マスターデータが存在しない場合はfalseを返す()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstUnit = MstUnit::factory()->create()->toEntity();
        $gradeLevel = 5;

        $usrUnitEloquent = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $gradeLevel,
            'last_reward_grade_level' => 1,
        ]);
        $usrUnit = $this->usrUnitRepository->getById($usrUnitEloquent->id, $usrUser->getId());

        // Exercise
        $result = $this->unitGradeUpRewardService->grantGradeUpReward($usrUnit);

        // Verify
        $this->assertFalse($result);

        // フラグは更新されないこと
        $usrUnit = $this->usrUnitRepository->getById($usrUnit->getId(), $usrUser->getId());
        $this->assertEquals(1, $usrUnit->getLastRewardGradeLevel());
    }

    public function testGrantGradeUpReward_複数の報酬を正常に付与できる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstUnit = MstUnit::factory()->create()->toEntity();
        $mstArtwork1 = MstArtwork::factory()->create()->toEntity();
        $mstArtwork2 = MstArtwork::factory()->create()->toEntity();
        $gradeLevel = 5;
        $mstUnitGradeUpRewardId1 = 'unit_grade_up_reward_1';
        $mstUnitGradeUpRewardId2 = 'unit_grade_up_reward_2';

        // 同一グレードレベルに複数の報酬を設定
        MstUnitGradeUpReward::factory()->create([
            'id' => $mstUnitGradeUpRewardId1,
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $gradeLevel,
            'resource_type' => 'Artwork',
            'resource_id' => $mstArtwork1->getId(),
            'resource_amount' => 1,
        ]);

        MstUnitGradeUpReward::factory()->create([
            'id' => $mstUnitGradeUpRewardId2,
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $gradeLevel,
            'resource_type' => 'Artwork',
            'resource_id' => $mstArtwork2->getId(),
            'resource_amount' => 2,
        ]);

        $usrUnitEloquent = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $gradeLevel,
            'last_reward_grade_level' => 1,
        ]);
        $usrUnit = $this->usrUnitRepository->getById($usrUnitEloquent->id, $usrUser->getId());

        // Exercise
        $result = $this->unitGradeUpRewardService->grantGradeUpReward($usrUnit);
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $this->assertTrue($result);

        // フラグが更新されていること
        $usrUnit = $this->usrUnitRepository->getById($usrUnit->getId(), $usrUser->getId());
        $this->assertEquals($gradeLevel, $usrUnit->getLastRewardGradeLevel());
    }

    public function testGrantGradeUpReward_複数グレードにまたがる報酬を一気に付与できる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstUnit = MstUnit::factory()->create()->toEntity();
        $mstArtwork1 = MstArtwork::factory()->create()->toEntity();
        $mstArtwork2 = MstArtwork::factory()->create()->toEntity();
        $mstArtwork3 = MstArtwork::factory()->create()->toEntity();
        $lastRewardGradeLevel = 2;
        $currentGradeLevel = 5;

        // グレード3, 4, 5の報酬を設定
        MstUnitGradeUpReward::factory()->create([
            'id' => 'reward_grade_3',
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => 3,
            'resource_type' => 'Artwork',
            'resource_id' => $mstArtwork1->getId(),
            'resource_amount' => 1,
        ]);

        MstUnitGradeUpReward::factory()->create([
            'id' => 'reward_grade_4',
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => 4,
            'resource_type' => 'Artwork',
            'resource_id' => $mstArtwork2->getId(),
            'resource_amount' => 1,
        ]);

        MstUnitGradeUpReward::factory()->create([
            'id' => 'reward_grade_5',
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => 5,
            'resource_type' => 'Artwork',
            'resource_id' => $mstArtwork3->getId(),
            'resource_amount' => 1,
        ]);

        $usrUnitEloquent = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $currentGradeLevel,
            'last_reward_grade_level' => $lastRewardGradeLevel,
        ]);
        $usrUnit = $this->usrUnitRepository->getById($usrUnitEloquent->id, $usrUser->getId());

        // Exercise
        $result = $this->unitGradeUpRewardService->grantGradeUpReward($usrUnit);
        $this->rewardDelegator->sendRewards($usrUser->getId(), 1, $this->fixTime());
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $this->assertTrue($result);

        // フラグが更新されていること
        $usrUnit = $this->usrUnitRepository->getById($usrUnit->getId(), $usrUser->getId());
        $this->assertEquals($currentGradeLevel, $usrUnit->getLastRewardGradeLevel());

        // グレード3, 4, 5の報酬が全て付与されていること
        $sentRewards = $this->rewardDelegator->getSentRewards(UnitGradeUpReward::class);
        $this->assertCount(3, $sentRewards);

        // 各アートワークが付与されていることを確認
        $usrArtwork1 = UsrArtwork::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_artwork_id', $mstArtwork1->getId())
            ->first();
        $usrArtwork2 = UsrArtwork::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_artwork_id', $mstArtwork2->getId())
            ->first();
        $usrArtwork3 = UsrArtwork::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_artwork_id', $mstArtwork3->getId())
            ->first();

        $this->assertNotNull($usrArtwork1, 'グレード3の報酬が付与されていること');
        $this->assertNotNull($usrArtwork2, 'グレード4の報酬が付与されていること');
        $this->assertNotNull($usrArtwork3, 'グレード5の報酬が付与されていること');
    }
}
