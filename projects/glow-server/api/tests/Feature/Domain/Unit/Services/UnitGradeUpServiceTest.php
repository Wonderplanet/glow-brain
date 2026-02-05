<?php

namespace Tests\Feature\Domain\Unit\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitGradeUp;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\Models\UsrUnitSummary;
use App\Domain\Unit\Services\UnitGradeUpService;
use Tests\TestCase;

class UnitGradeUpServiceTest extends TestCase
{
    private UnitGradeUpService $unitGradeUpService;

    public function setUp(): void
    {
        parent::setUp();
        $this->unitGradeUpService = $this->app->make(UnitGradeUpService::class);
    }

    public function testGradeUp_グレードアップを正常に実行できる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstItem = MstItem::factory()->create()->toEntity();
        $mstUnit = MstUnit::factory()->create(['fragment_mst_item_id' => $mstItem->getId()])->toEntity();
        $mstUnitGradeUp = MstUnitGradeUp::factory()->create([
            'unit_label' => $mstUnit->getUnitLabel(),
            'grade_level' => 1,
            'require_amount' => 10,
        ])->toEntity();
        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => 0,
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem->getId(),
            'amount' => $mstUnitGradeUp->getRequireAmount(),
        ]);

        // Exercise
        $this->unitGradeUpService->gradeUp($usrUnit->getId(), $usrUser->getId());
        $this->saveAll();

        // グレードが更新されていること
        $usrUnit = UsrUnit::query()->where('id', $usrUnit->getId())->first();
        $this->assertEquals(1, $usrUnit->getGradeLevel());

        // UnitSummaryのグレードアップがカウントアップされていること
        $usrUnitSummary = UsrUnitSummary::query()
            ->where('usr_user_id', $usrUser->getId())
            ->first();
        $this->assertEquals(1, $usrUnitSummary->getGradeLevelTotalCount());

        // かけらが減っていること
        $usrItem = UsrItem::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_item_id', $mstItem->getId())
            ->first();
        $this->assertEquals(0, $usrItem->getAmount());
    }

    public function testGradeUp_UnitSummaryのグレードアップがカウントアップされる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstItem = MstItem::factory()->create()->toEntity();
        $mstUnit = MstUnit::factory()->create(['fragment_mst_item_id' => $mstItem->getId()])->toEntity();
        $mstUnitGradeUp = MstUnitGradeUp::factory()->create([
            'unit_label' => $mstUnit->getUnitLabel(),
            'grade_level' => 1,
            'require_amount' => 10,
        ])->toEntity();
        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => 0,
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem->getId(),
            'amount' => $mstUnitGradeUp->getRequireAmount(),
        ]);

        $grade_level_total_count = 3;
        UsrUnitSummary::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'grade_level_total_count' => $grade_level_total_count,
        ]);

        // Exercise
        $this->unitGradeUpService->gradeUp($usrUnit->getId(), $usrUser->getId());
        $this->saveAll();

        // グレードが更新されていること
        $usrUnit = UsrUnit::query()->where('id', $usrUnit->getId())->first();
        $this->assertEquals(1, $usrUnit->getGradeLevel());

        // UnitSummaryのグレードアップがカウントアップされていること
        $usrUnitSummary = UsrUnitSummary::query()
            ->where('usr_user_id', $usrUser->getId())
            ->first();
        $this->assertEquals($grade_level_total_count + 1, $usrUnitSummary->getGradeLevelTotalCount());
        
        // かけらが減っていること
        $usrItem = UsrItem::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_item_id', $mstItem->getId())
            ->first();
        $this->assertEquals(0, $usrItem->getAmount());
    }

    public function testGradeUp_必要コストを所持していない場合エラーになる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstItem = MstItem::factory()->create()->toEntity();
        $mstUnit = MstUnit::factory()->create(['fragment_mst_item_id' => $mstItem->getId()])->toEntity();
        $mstUnitGradeUp = MstUnitGradeUp::factory()->create([
            'unit_label' => $mstUnit->getUnitLabel(),
            'grade_level' => 1,
            'require_amount' => 10,
        ])->toEntity();
        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => 0,
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem->getId(),
            'amount' => $mstUnitGradeUp->getRequireAmount() - 1,
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ITEM_AMOUNT_IS_NOT_ENOUGH);

        // Exercise
        $this->unitGradeUpService->gradeUp($usrUnit->getId(), $usrUser->getId());
    }

    public function testRankUp_上限を超えてグレードアップしようとするとエラーになる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstItem = MstItem::factory()->create()->toEntity();
        $mstUnit = MstUnit::factory()->create(['fragment_mst_item_id' => $mstItem->getId()])->toEntity();
        $mstUnitGradeUp = MstUnitGradeUp::factory()->create([
            'unit_label' => $mstUnit->getUnitLabel(),
            'grade_level' => 1,
            'require_amount' => 1,
        ])->toEntity();
        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'grade_level' => 1,
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem->getId(),
            'amount' => $mstUnitGradeUp->getRequireAmount(),
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);

        // Exercise
        $this->unitGradeUpService->gradeUp($usrUnit->getId(), $usrUser->getId());
    }

    public function testAddGradeLevelTotalCount_グレードレベルをカウントアップする()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $initialCount = 5;
        UsrUnitSummary::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'grade_level_total_count' => $initialCount,
        ]);

        // Exercise
        $this->unitGradeUpService->addGradeLevelTotalCount($usrUser->getId(), 3);
        $this->saveAll();

        // Verify
        $usrUnitSummary = UsrUnitSummary::query()
            ->where('usr_user_id', $usrUser->getId())
            ->first();
        $this->assertEquals($initialCount + 3, $usrUnitSummary->getGradeLevelTotalCount());
    }

    public function testAddGradeLevelTotalCount_UnitSummaryが存在しない場合は新規作成される()
    {
        // Setup
        $usrUser = $this->createUsrUser();

        // Exercise
        $this->unitGradeUpService->addGradeLevelTotalCount($usrUser->getId(), 2);
        $this->saveAll();

        // Verify
        $usrUnitSummary = UsrUnitSummary::query()
            ->where('usr_user_id', $usrUser->getId())
            ->first();
        $this->assertNotNull($usrUnitSummary);
        $this->assertEquals(2, $usrUnitSummary->getGradeLevelTotalCount());
    }
}
