<?php

namespace Tests\Feature\Domain\Unit\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Models\MstUnitLevelUp;
use App\Domain\Resource\Mst\Models\MstUnitRankUp;
use App\Domain\Resource\Mst\Repositories\MstUnitLevelUpRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitRankUpRepository;
use Tests\TestCase;

class MstUnitRankUpRepositoryTest extends TestCase
{
    private MstUnitRankUpRepository $mstUnitRankUpRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mstUnitRankUpRepository = $this->app->make(MstUnitRankUpRepository::class);
    }

    public function testGetByUnitLabelAndRank_ユニットラベルとランクからマスターデータを取得()
    {
        // Setup
        $unitLabel = 'DropR';
        $rank = 1;
        MstUnitRankUp::factory()->create(['unit_label' => $unitLabel, 'rank' => $rank]);

        // Exercise
        $actual = $this->mstUnitRankUpRepository->getByUnitLabelAndRank($unitLabel, $rank);

        // Verify
        $this->assertEquals($unitLabel, $actual->getUnitLabel());
        $this->assertEquals($rank, $actual->getRank());
    }

    public static function param_getByUnitLabelAndRank_該当データがない場合NULLが返る()
    {
        return [
            'データが存在しない1' => ['mstUnitLabel' => 'DropR', 'mstRank' => 1, 'unitLabel' => 'DropSR', 'rank' => 1],
            'データが存在しない2' => ['mstUnitLabel' => 'DropR', 'mstRank' => 2, 'unitLabel' => 'DropR', 'rank' => 1],
        ];
    }

    /**
     * @dataProvider param_getByUnitLabelAndRank_該当データがない場合NULLが返る
     */
    public function testGetByUnitLabelAndRank_該当データがない場合NULLが返る(
        string $mstUnitLabel,
        int $mstRank,
        string $unitLabel,
        int $rank
    ) {
        // Setup
        MstUnitRankUp::factory()->create(['unit_label' => $mstUnitLabel, 'rank' => $mstRank]);

        // Exercise
        $actual = $this->mstUnitRankUpRepository->getByUnitLabelAndRank($unitLabel, $rank);

        // Verify
        $this->assertNull($actual);
    }

    public function testGetByUnitLabelAndRank_データがなくエラーフラグがtrueの場合はエラーになる()
    {
        // Setup
        MstUnitRankUp::factory()->create(['unit_label' => 'DropR', 'rank' => 1]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);

        // Exercise
        $this->mstUnitRankUpRepository->getByUnitLabelAndRank('label', 11, true);
    }
}
