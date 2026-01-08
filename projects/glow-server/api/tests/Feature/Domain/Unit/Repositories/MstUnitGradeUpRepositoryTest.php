<?php

namespace Tests\Feature\Domain\Unit\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Models\MstUnitGradeUp;
use App\Domain\Resource\Mst\Repositories\MstUnitGradeUpRepository;
use Tests\TestCase;

class MstUnitGradeUpRepositoryTest extends TestCase
{
    private MstUnitGradeUpRepository $mstUnitGradeUpRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mstUnitGradeUpRepository = $this->app->make(MstUnitGradeUpRepository::class);
    }

    public function testGetByUnitLabelAndGradeLevel_ユニットラベルとグレードからマスターデータを取得()
    {
        // Setup
        $unitLabel = 'DropR';
        $gradeLevel = 1;
        MstUnitGradeUp::factory()->createMany([
            ['unit_label' => $unitLabel, 'grade_level' => $gradeLevel],
            ['unit_label' => $unitLabel, 'grade_level' => $gradeLevel + 1],
            ['unit_label' => 'DropSR', 'grade_level' => $gradeLevel],
        ]);

        // Exercise
        $actual = $this->mstUnitGradeUpRepository->getByUnitLabelAndGradeLevel($unitLabel, $gradeLevel);

        // Verify
        $this->assertEquals($unitLabel, $actual->getUnitLabel());
        $this->assertEquals($gradeLevel, $actual->getGradeLevel());
    }

    public static function param_getByUnitLabelAndGradeLevel_該当データがない場合NULLが返る()
    {
        return [
            'データが存在しない1' => ['mstUnitLabel' => 'DropR', 'mstGrade' => 1, 'unitLabel' => 'DropSR', 'grade' => 1],
            'データが存在しない2' => ['mstUnitLabel' => 'DropR', 'mstGrade' => 2, 'unitLabel' => 'DropR', 'grade' => 1],
        ];
    }

    /**
     * @dataProvider param_getByUnitLabelAndGradeLevel_該当データがない場合NULLが返る
     */
    public function testGetByUnitLabelAndGradeLevel_該当データがない場合NULLが返る(
        string $mstUnitLabel,
        int $mstGrade,
        string $unitLabel,
        int $grade
    ) {
        // Setup
        MstUnitGradeUp::factory()->create(['unit_label' => $mstUnitLabel, 'grade_level' => $mstGrade]);

        // Exercise
        $actual = $this->mstUnitGradeUpRepository->getByUnitLabelAndGradeLevel($unitLabel, $grade);

        // Verify
        $this->assertNull($actual);
    }

    public function testGetByUnitLabelAndGradeLevel_データがなくエラーフラグがtrueの場合はエラーになる()
    {
        // Setup
        MstUnitGradeUp::factory()->create(['unit_label' => 'DropR', 'grade_level' => 1]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);

        // Exercise
        $this->mstUnitGradeUpRepository->getByUnitLabelAndGradeLevel('label', 11, true);
    }
}
