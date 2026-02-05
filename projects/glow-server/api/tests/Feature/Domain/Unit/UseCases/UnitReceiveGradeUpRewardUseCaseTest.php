<?php

namespace Tests\Feature\Domain\Unit\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitGradeUpReward;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\UseCases\UnitReceiveGradeUpRewardUseCase;
use App\Domain\User\Constants\UserConstant;
use Tests\TestCase;

class UnitReceiveGradeUpRewardUseCaseTest extends TestCase
{
    private UnitReceiveGradeUpRewardUseCase $unitReceiveGradeUpRewardUseCase;

    public function setUp(): void
    {
        parent::setUp();
        $this->unitReceiveGradeUpRewardUseCase = $this->app->make(UnitReceiveGradeUpRewardUseCase::class);
    }

    public function testExec_グレードアップ報酬を正常に受け取ることができる()
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

        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $gradeLevel,
            'last_reward_grade_level' => 0,
        ]);

        // Exercise
        $resultData = $this->unitReceiveGradeUpRewardUseCase->exec($usrUser->getId(), $usrUnit->getId(), UserConstant::PLATFORM_IOS);

        // Verify
        // 返り値の確認
        $this->assertNotNull($resultData);
        $this->assertEquals($usrUnit->getId(), $resultData->usrUnit->getId());

        // 報酬情報が含まれていること
        $this->assertNotEmpty($resultData->unitGradeUpRewards);
        $this->assertCount(1, $resultData->unitGradeUpRewards);

        // UsrUnitのlast_reward_grade_levelが更新されていること
        $updatedUsrUnit = UsrUnit::query()->find($usrUnit->getId());
        $this->assertEquals($gradeLevel, $updatedUsrUnit->getLastRewardGradeLevel());

        // アートワークが付与されていること
        $usrArtwork = UsrArtwork::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_artwork_id', $mstArtwork->getId())
            ->first();
        $this->assertNotNull($usrArtwork);
    }

    public function testExec_既に受け取り済みの場合はエラーになる()
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

        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $gradeLevel,
            'last_reward_grade_level' => $gradeLevel,  // 既に受け取り済み
        ]);

        // Expect
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_PARAMETER);

        // Exercise
        $this->unitReceiveGradeUpRewardUseCase->exec($usrUser->getId(), $usrUnit->getId(), UserConstant::PLATFORM_IOS);
    }

    public function testExec_報酬マスターデータが存在しない場合はエラーになる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstUnit = MstUnit::factory()->create()->toEntity();
        $gradeLevel = 5;

        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $gradeLevel,
            'last_reward_grade_level' => 0,
        ]);

        // Expect
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);

        // Exercise
        $this->unitReceiveGradeUpRewardUseCase->exec($usrUser->getId(), $usrUnit->getId(), UserConstant::PLATFORM_IOS);
    }

    public function testExec_複数グレードにまたがる報酬を一気に受け取ることができる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstUnit = MstUnit::factory()->create()->toEntity();
        $mstArtwork3 = MstArtwork::factory()->create()->toEntity();
        $mstArtwork4 = MstArtwork::factory()->create()->toEntity();
        $mstArtwork5 = MstArtwork::factory()->create()->toEntity();
        $currentGradeLevel = 5;
        $lastRewardGradeLevel = 2;

        // グレード3, 4, 5の報酬設定
        MstUnitGradeUpReward::factory()->create([
            'id' => 'reward_grade_3',
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => 3,
            'resource_type' => 'Artwork',
            'resource_id' => $mstArtwork3->getId(),
            'resource_amount' => 1,
        ]);

        MstUnitGradeUpReward::factory()->create([
            'id' => 'reward_grade_4',
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => 4,
            'resource_type' => 'Artwork',
            'resource_id' => $mstArtwork4->getId(),
            'resource_amount' => 1,
        ]);

        MstUnitGradeUpReward::factory()->create([
            'id' => 'reward_grade_5',
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => 5,
            'resource_type' => 'Artwork',
            'resource_id' => $mstArtwork5->getId(),
            'resource_amount' => 1,
        ]);

        // グレード5でlast_reward_grade_level=2の状態（グレード3,4,5の報酬未受取）
        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $currentGradeLevel,
            'last_reward_grade_level' => $lastRewardGradeLevel,
        ]);

        // Exercise
        $resultData = $this->unitReceiveGradeUpRewardUseCase->exec($usrUser->getId(), $usrUnit->getId(), UserConstant::PLATFORM_IOS);

        // Verify
        // 返り値の確認
        $this->assertNotNull($resultData);
        $this->assertEquals($usrUnit->getId(), $resultData->usrUnit->getId());

        // グレード3, 4, 5の報酬が全て付与されていること
        $this->assertNotEmpty($resultData->unitGradeUpRewards);
        $this->assertCount(3, $resultData->unitGradeUpRewards);

        // UsrUnitのlast_reward_grade_levelが更新されていること
        $updatedUsrUnit = UsrUnit::query()->find($usrUnit->getId());
        $this->assertEquals($currentGradeLevel, $updatedUsrUnit->getLastRewardGradeLevel());

        // 3つのアートワークが付与されていること
        $this->assertCount(3, $resultData->usrArtworks);
        
        $usrArtwork3 = UsrArtwork::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_artwork_id', $mstArtwork3->getId())
            ->first();
        $usrArtwork4 = UsrArtwork::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_artwork_id', $mstArtwork4->getId())
            ->first();
        $usrArtwork5 = UsrArtwork::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_artwork_id', $mstArtwork5->getId())
            ->first();
        
        $this->assertNotNull($usrArtwork3, 'グレード3の報酬が付与されていること');
        $this->assertNotNull($usrArtwork4, 'グレード4の報酬が付与されていること');
        $this->assertNotNull($usrArtwork5, 'グレード5の報酬が付与されていること');
    }
}
