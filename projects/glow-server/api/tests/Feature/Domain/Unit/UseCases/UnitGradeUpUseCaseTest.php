<?php

namespace Tests\Feature\Domain\Unit\UseCases;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitGradeUp;
use App\Domain\Resource\Mst\Models\MstUnitGradeUpReward;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\UseCases\UnitGradeUpUseCase;
use App\Domain\User\Constants\UserConstant;
use Tests\TestCase;

class UnitGradeUpUseCaseTest extends TestCase
{
    private UnitGradeUpUseCase $unitGradeUpUseCase;

    public function setUp(): void
    {
        parent::setUp();
        $this->unitGradeUpUseCase = $this->app->make(UnitGradeUpUseCase::class);
    }

    public function testExec_グレードアップが正常に実行できる()
    {
        // Setup
        $this->fixTime();
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId(), $usrUser->getGameStartAt());
        
        $mstUnit = MstUnit::factory()->create([
            'fragment_mst_item_id' => 'fragment_item_1',
        ])->toEntity();
        
        $beforeGradeLevel = 1;
        $afterGradeLevel = 2;
        $requireAmount = 10;

        MstUnitGradeUp::factory()->create([
            'unit_label' => $mstUnit->getUnitLabel(),
            'grade_level' => $afterGradeLevel,
            'require_amount' => $requireAmount,
        ]);

        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $beforeGradeLevel,
            'last_reward_grade_level' => $beforeGradeLevel,
        ]);

        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => 'fragment_item_1',
            'amount' => $requireAmount,
        ]);

        // Exercise
        $resultData = $this->unitGradeUpUseCase->exec($currentUser, $usrUnit->getId(), UserConstant::PLATFORM_IOS);

        // Verify
        $this->assertNotNull($resultData);
        $this->assertEquals($usrUnit->getId(), $resultData->usrUnit->getId());
        $this->assertEquals($afterGradeLevel, $resultData->usrUnit->getGradeLevel());
        
        // アイテムが消費されていること
        $this->assertNotEmpty($resultData->usrItems);
        
        // データベースの確認
        $updatedUsrUnit = UsrUnit::query()->find($usrUnit->getId());
        $this->assertEquals($afterGradeLevel, $updatedUsrUnit->getGradeLevel());
        
        // unitGradeUpRewardsは空（報酬マスタがない場合）
        $this->assertEmpty($resultData->unitGradeUpRewards);
    }

    public function testExec_グレードアップ時に報酬が付与される()
    {
        // Setup
        $this->fixTime();
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId(), $usrUser->getGameStartAt());
        
        $mstUnit = MstUnit::factory()->create([
            'fragment_mst_item_id' => 'fragment_item_1',
        ])->toEntity();
        
        $mstArtwork = MstArtwork::factory()->create()->toEntity();
        
        $beforeGradeLevel = 4;
        $afterGradeLevel = 5;
        $requireAmount = 10;

        MstUnitGradeUp::factory()->create([
            'unit_label' => $mstUnit->getUnitLabel(),
            'grade_level' => $afterGradeLevel,
            'require_amount' => $requireAmount,
        ]);

        // グレード5の報酬設定
        MstUnitGradeUpReward::factory()->create([
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $afterGradeLevel,
            'resource_type' => 'Artwork',
            'resource_id' => $mstArtwork->getId(),
            'resource_amount' => 1,
        ]);

        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $beforeGradeLevel,
            'last_reward_grade_level' => $beforeGradeLevel,
        ]);

        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => 'fragment_item_1',
            'amount' => $requireAmount,
        ]);

        // Exercise
        $resultData = $this->unitGradeUpUseCase->exec($currentUser, $usrUnit->getId(), UserConstant::PLATFORM_IOS);

        // Verify
        $this->assertNotNull($resultData);
        $this->assertEquals($usrUnit->getId(), $resultData->usrUnit->getId());
        $this->assertEquals($afterGradeLevel, $resultData->usrUnit->getGradeLevel());
        
        // 報酬情報が含まれていること
        $this->assertNotEmpty($resultData->unitGradeUpRewards);
        $this->assertCount(1, $resultData->unitGradeUpRewards);
        
        // UsrUnitのlast_reward_grade_levelが更新されていること
        $updatedUsrUnit = UsrUnit::query()->find($usrUnit->getId());
        $this->assertEquals($afterGradeLevel, $updatedUsrUnit->getLastRewardGradeLevel());
        
        // アートワークが付与されていること
        $this->assertNotEmpty($resultData->usrArtworks);
        
        $usrArtwork = UsrArtwork::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_artwork_id', $mstArtwork->getId())
            ->first();
        $this->assertNotNull($usrArtwork);
    }

    public function testExec_既に報酬を受け取り済みの場合は再度付与されない()
    {
        // Setup
        $this->fixTime();
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId(), $usrUser->getGameStartAt());
        
        $mstUnit = MstUnit::factory()->create([
            'fragment_mst_item_id' => 'fragment_item_1',
        ])->toEntity();
        
        $mstArtwork = MstArtwork::factory()->create()->toEntity();
        
        $beforeGradeLevel = 5;
        $afterGradeLevel = 6;
        $requireAmount = 10;

        MstUnitGradeUp::factory()->create([
            'unit_label' => $mstUnit->getUnitLabel(),
            'grade_level' => $afterGradeLevel,
            'require_amount' => $requireAmount,
        ]);

        // グレード5の報酬設定（既に受け取り済み）
        MstUnitGradeUpReward::factory()->create([
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $beforeGradeLevel,
            'resource_type' => 'Artwork',
            'resource_id' => $mstArtwork->getId(),
            'resource_amount' => 1,
        ]);

        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => $beforeGradeLevel,
            'last_reward_grade_level' => $beforeGradeLevel, // 既に受け取り済み
        ]);

        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => 'fragment_item_1',
            'amount' => $requireAmount,
        ]);

        // Exercise
        $resultData = $this->unitGradeUpUseCase->exec($currentUser, $usrUnit->getId(), UserConstant::PLATFORM_IOS);

        // Verify
        $this->assertNotNull($resultData);
        $this->assertEquals($usrUnit->getId(), $resultData->usrUnit->getId());
        $this->assertEquals($afterGradeLevel, $resultData->usrUnit->getGradeLevel());
        
        // 報酬は付与されないこと
        $this->assertEmpty($resultData->unitGradeUpRewards);
        
        // last_reward_grade_levelは更新されない
        $updatedUsrUnit = UsrUnit::query()->find($usrUnit->getId());
        $this->assertEquals($beforeGradeLevel, $updatedUsrUnit->getLastRewardGradeLevel());
    }

}
