<?php

namespace Tests\Feature\Domain\Unit\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\Repositories\UsrUnitRepository;
use App\Domain\Unit\Enums\UnitLabel;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Unit\Services\UnitService;
use App\Domain\Unit\Models\UsrUnitSummary;
use Tests\TestCase;

class UsrUnitServiceTest extends TestCase
{
    private UsrUnitRepository $usrUnitRepository;
    private UnitService $unitService;

    public function setUp(): void
    {
        parent::setUp();
        $this->usrUnitRepository = $this->app->make(UsrUnitRepository::class);
        $this->unitService = $this->app->make(UnitService::class);
    }

    public function testGetById_ユニットを取得できる()
    {
        $usrUser = $this->createUsrUser();
        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId()
        ]);

        $actual = $this->usrUnitRepository->getById($usrUnit->getId(), $usrUser->getId());

        $this->assertEquals($usrUser->getUsrUserId(), $actual->getUsrUserId());
        $this->assertEquals($usrUnit->getId(), $actual->getId());
    }

    public function testGetById_ユニットIDが不正な場合はエラーになる()
    {
        $usrUser = $this->createUsrUser();
        UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId()
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::UNIT_NOT_FOUND);

        $this->usrUnitRepository->getById('invalid_id', $usrUser->getId());
    }

    public function testBulkCreate_ユニットを登録できる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstUnitId = 'unit1';
        $mstUnitId2 = 'unit2';

        MstUnit::factory()->createMany([
            ['id' => $mstUnitId, 'unit_label' => UnitLabel::DROP_R, 'fragment_mst_item_id' => 'charaFragment1',],
            ['id' => $mstUnitId2, 'unit_label' => UnitLabel::DROP_R, 'fragment_mst_item_id' => 'charaFragment2',],
        ]);

        // Exercise
        $this->unitService->bulkCreate($usrUser->getId(), collect([$mstUnitId, $mstUnitId2]));
        $this->saveAll();
        
        // Verify
        $usrUnit = UsrUnit::query()->where('usr_user_id', $usrUser->getId())->get();
        $this->assertEquals($usrUnit->count(), 2);

        $usrUnitSummary = UsrUnitSummary::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals($usrUnitSummary->grade_level_total_count, 2);
    }
}
