<?php

namespace Tests\Feature\Domain\Unit\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Models\MstUnitLevelUp;
use App\Domain\Resource\Mst\Repositories\MstUnitLevelUpRepository;
use Tests\TestCase;

class MstUnitLevelUpRepositoryTest extends TestCase
{
    private MstUnitLevelUpRepository $mstUnitLevelUpRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mstUnitLevelUpRepository = $this->app->make(MstUnitLevelUpRepository::class);
    }

    public function testGetLevelIsInRange_レベル範囲内のマスターデータを取得()
    {
        // Setup
        $unitLabel = 'DropR';
        MstUnitLevelUp::factory()
            ->count(5)
            ->sequence(fn ($sequence) => ['unit_label' => $unitLabel, 'level' => $sequence->index + 1])
            ->create();

        $fromLevel = 2;
        $toLevel = 4;

        // Exercise
        $result = $this->mstUnitLevelUpRepository->getLevelIsInRange($unitLabel, $fromLevel, $toLevel);

        // Verify
        $this->assertCount(3, $result);
        $this->assertEquals($fromLevel, $result->first()->getLevel());
        $this->assertEquals($toLevel, $result->last()->getLevel());
    }

    public function testGetMaxMstUnitLevelUp_最大レベルのマスターデータを取得()
    {
        // Setup
        $unitLabel = 'DropR';
        MstUnitLevelUp::factory()
            ->count(3)
            ->sequence(fn ($sequence) => ['unit_label' => $unitLabel, 'level' => $sequence->index + 1])
            ->create();

        // Exercise
        $result = $this->mstUnitLevelUpRepository->getMaxMstUnitLevelUp($unitLabel);

        // Verify
        $this->assertEquals(3, $result->getLevel());
    }

    public function testGetMaxMstUnitLevelUp_データがない場合はエラーになる()
    {
        // Setup
        $unitLabel = 'DropR';
        MstUnitLevelUp::factory()
            ->count(3)
            ->sequence(fn ($sequence) => ['unit_label' => $unitLabel, 'level' => $sequence->index + 1])
            ->create();

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);

        // Exercise
        $this->mstUnitLevelUpRepository->getMaxMstUnitLevelUp('invalid_label', true);
    }
}
