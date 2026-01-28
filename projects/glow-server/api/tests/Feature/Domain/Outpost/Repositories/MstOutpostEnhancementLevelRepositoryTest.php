<?php

namespace Feature\Domain\Outpost\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Repositories\MstOutpostEnhancementLevelRepository;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancementLevel;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MstOutpostEnhancementLevelRepositoryTest extends TestCase
{
    private MstOutpostEnhancementLevelRepository $mstOutpostEnhancementLevelRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mstOutpostEnhancementLevelRepository = app(MstOutpostEnhancementLevelRepository::class);
    }

    public static function params_testGetLevelIsInRange_指定範囲レベルのゲートのレベル情報を取得できる()
    {
        return [
            '1レベル取得' => [
                'fromLevel' => 2,
                'toLevel' => 2,
                'expectedCount' => 1,
            ],
            '複数レベル取得' => [
                'fromLevel' => 1,
                'toLevel' => 3,
                'expectedCount' => 3,
            ],
        ];
    }

    #[DataProvider('params_testGetLevelIsInRange_指定範囲レベルのゲートのレベル情報を取得できる')]
    public function testGetLevelIsInRange_指定範囲レベルのゲートのレベル情報を取得できる(
        int $fromLevel,
        int $toLevel,
        $expectedCount
    ) {
        // Setup
        $mstOutPostEnhancementId = fake()->uuid();
        MstOutpostEnhancementLevel::factory()->createMany([
            ['mst_outpost_enhancement_id' => $mstOutPostEnhancementId, 'level' => 1],
            ['mst_outpost_enhancement_id' => $mstOutPostEnhancementId, 'level' => 2],
            ['mst_outpost_enhancement_id' => $mstOutPostEnhancementId, 'level' => 3],
        ]);

        // Exercise
        $actual = $this->mstOutpostEnhancementLevelRepository->getLevelIsInRange(
            $mstOutPostEnhancementId,
            $fromLevel,
            $toLevel,
            true
        );

        // Verify
        $this->assertCount($expectedCount, $actual);

        $actual = $actual->keyBy(fn($entity) => $entity->getLevel());
        foreach (range($fromLevel, $toLevel) as $level) {
            $this->assertArrayHasKey($level, $actual);
        }
    }

    public static function params_testGetLevelIsInRange_マスターデータがない場合はエラーとなる()
    {
        return [
            'レコード0件' => [
                'levels' => collect(),
            ],
            '対象範囲のレコードがない' => [
                'levels' => collect([1, 3]),
            ],
        ];
    }

    #[DataProvider('params_testGetLevelIsInRange_マスターデータがない場合はエラーとなる')]
    public function testGetLevelIsInRange_マスターデータがない場合はエラーとなる(Collection $levels)
    {
        // Setup
        $mstOutPostEnhancementId = fake()->uuid();

        $levels->each(function ($level) use ($mstOutPostEnhancementId) {
            MstOutpostEnhancementLevel::factory()->create([
                'mst_outpost_enhancement_id' => $mstOutPostEnhancementId,
                'level' => $level,
            ]);
        });

        // 現在のレベルよりも低いレベルを指定してエラーとなる
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);

        // Exercise
        $this->mstOutpostEnhancementLevelRepository->getLevelIsInRange(
            $mstOutPostEnhancementId,
            1,
            3,
            true
        );
    }
}
