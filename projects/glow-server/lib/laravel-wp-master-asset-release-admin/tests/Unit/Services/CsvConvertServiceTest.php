<?php

declare(strict_types=1);

namespace MasterAssetReleaseAdmin\Unit\Services;

use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Constants\SpreadSheetLabel;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\CsvConvertService;
use WonderPlanet\Tests\TestCase;

class CsvConvertServiceTest extends TestCase
{
    use ReflectionTrait;

    private CsvConvertService $csvConvertService;

    public function setUp(): void
    {
        parent::setUp();

        $this->csvConvertService = app(CsvConvertService::class);
    }

    /**
     * @test
     * @dataProvider convertToDateTimeData
     */
    public function convertToDateTime_変換チェック(string $valueJst): void
    {
        // Exercise
        $result = $this->csvConvertService->convertToDateTime($valueJst);

        // Verify
        //  JSTで渡した値がUTCに変換され、デフォルトフォーマットの形式になっているか
        $this->assertEquals('2019-12-31 15:00:00', $result);
    }

    /**
     * @return array
     */
    private function convertToDateTimeData(): array
    {
        return [
            'パターン1' => ['2020-01-01 00:00:00'],
            'パターン2' => ['2020-01-01 00:00:00+09:00'],
            'パターン3' => ['2020/01/01 0:00:00'],
        ];
    }

    /**
     * @test
     */
    public function toArrayInputPlaceholder_nullへの変換チェック(): void
    {
        // Setup
        //  変換対象となるcsvデータ設定
        $csv = [
            ["test_column"], // カラム行
            // 以降、レコード行(7件)
            [100],
            [''],
            [null],
            ['__NULL__'],
            ['__null__'],
            ['NULL'],
            ['null'],
        ];
        $placeholders = [
            null,
            SpreadSheetLabel::UNDER_BAR_NULL_CELL_PLACEHOLDER,
            strtolower(SpreadSheetLabel::UNDER_BAR_NULL_CELL_PLACEHOLDER),
            SpreadSheetLabel::NULL_CELL_PLACEHOLDER,
            strtolower(SpreadSheetLabel::NULL_CELL_PLACEHOLDER),
        ];

        // Exercise
        $results = $this->callMethod(
            $this->app->make(CsvConvertService::class),
            'toArrayInputPlaceholder',
            $csv,
            $placeholders
        );

        // Verify
        //  7件分の変換ができているか
        $this->assertEquals(7, count($results));
        //   想定した変換ができているか
        $expectedResults = [
            100, '', null, null, null, null, null,
        ];
        foreach ($results as $key => $result) {
            $this->assertEquals($expectedResults[$key], $result['test_column']);
        }
    }

    /**
     * @test
     * @dataProvider isSkipConvertToDateTimeData
     */
    public function isSkipConvertToDateTime_想定した判定になるかチェック(mixed $columnValue, $expected): void
    {
        // Exercise
        $result = $this->callMethod(
            $this->app->make(CsvConvertService::class),
            'isSkipConvertToDateTime',
            $columnValue
        );

        // Verify
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array[]
     */
    private function isSkipConvertToDateTimeData(): array
    {
        return [
            'null' => [null, true],
            '空文字' => ['', true],
            '__NULL__' => [SpreadSheetLabel::UNDER_BAR_NULL_CELL_PLACEHOLDER, true],
            '__null__' => [strtolower(SpreadSheetLabel::UNDER_BAR_NULL_CELL_PLACEHOLDER), true],
            'NULL(文字列)' => [SpreadSheetLabel::NULL_CELL_PLACEHOLDER, true],
            'null(文字列)' => [strtolower(SpreadSheetLabel::NULL_CELL_PLACEHOLDER), true],
            'それ以外の文字列' => ['test', false],
            'それ以外の数字' => [99, false],
        ];
    }
}
