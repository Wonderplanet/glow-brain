<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Utils\Excel;

use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Excel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Utils\Excel\CollaboAggregation;

class CollaboAggregationTest extends TestCase
{
    #[Test]
    #[DataProvider('collectionSandboxData')]
    public function collection_出力データチェック(
        bool $isIncludeSandbox,
        string $expectedFileName
    ): void {
        // Setup
        $startAt = new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo');
        $endAt = new Carbon('2023-01-31 23:59:59', 'Asia/Tokyo');
        $collection = collect([
            self::makeCollectItem(
                'gacha',
                'gacha_id_1',
                'JPY',
                '2023-01',
                '10.00',
                '100',
                '1',
                '1000.00000000'
            ),
            self::makeCollectItem(
                'product',
                'product_id_1',
                'JPY',
                '2023-01',
                '10.00',
                '100',
                '',
                ''
            ),
            self::makeCollectItem(
                'product',
                'product_id_1',
                'USD',
                '2023-01',
                '10.00',
                '100',
                '149.580000',
                '149580.00000000'
            ),
        ]);
        $searchTriggers = [
            [
                'type' => 'gacha',
                'ids' => [
                    'gacha_id_1',
                    // 存在しないID
                    'gacha_id_2',
                ],
            ],
            [
                'type' => 'product',
                'ids' => [
                    'product_id_1',
                    // 存在しないID
                    'product_id_2',
                ],
            ],

        ];

        $collaboAggregation = new CollaboAggregation(
            $collection,
            $startAt,
            $endAt,
            $searchTriggers,
            $isIncludeSandbox
        );

        // Exercise
        $excelDataArray = $collaboAggregation
            ->collection()
            ->toArray();

        // Verify
        $this->assertCount(7, $excelDataArray);

        $messageRow = $excelDataArray[0];
        $this->assertEquals('通貨レートが空白のデータがあります。  対象通貨: JPY', $messageRow[0]);

        $headerRow = $excelDataArray[1];
        $this->assertEquals('gacha_id/product_id', $headerRow[0]);
        $this->assertEquals('currency', $headerRow[1]);
        $this->assertEquals('消費年月', $headerRow[2]);
        $this->assertEquals('有償通貨単価', $headerRow[3]);
        $this->assertEquals('月末TTM', $headerRow[4]);
        $this->assertEquals('消費有償通貨数', $headerRow[5]);
        $this->assertEquals('消費有償通貨額（円）', $headerRow[6]);

        $excelDatacollection = collect($excelDataArray);
        $gacha1DataRow = $excelDatacollection->first(fn($row) => $row[0] === 'gacha_id_1');
        $this->assertEquals('gacha_id_1', $gacha1DataRow[0]);
        $this->assertEquals('JPY', $gacha1DataRow[1]);
        $this->assertEquals('2023-01', $gacha1DataRow[2]);
        $this->assertEquals('10.00', $gacha1DataRow[3]);
        $this->assertEquals('1', $gacha1DataRow[4]);
        $this->assertEquals('100', $gacha1DataRow[5]);
        $this->assertEquals('1000.00000000', $gacha1DataRow[6]);

        $productJpyDataRow = $excelDatacollection->first(fn($row) => $row[0] === 'product_id_1' && $row[1] === 'JPY');
        $this->assertEquals('product_id_1', $productJpyDataRow[0]);
        $this->assertEquals('JPY', $productJpyDataRow[1]);
        $this->assertEquals('2023-01', $productJpyDataRow[2]);
        $this->assertEquals('10.00', $productJpyDataRow[3]);
        $this->assertEquals('', $productJpyDataRow[4]);
        $this->assertEquals('100', $productJpyDataRow[5]);
        $this->assertEquals('=C4 * D4 * E4', $productJpyDataRow[6]);

        $productUsdDataRow = $excelDatacollection->first(fn($row) => $row[0] === 'product_id_1' && $row[1] === 'USD');
        $this->assertEquals('product_id_1', $productUsdDataRow[0]);
        $this->assertEquals('USD', $productUsdDataRow[1]);
        $this->assertEquals('2023-01', $productUsdDataRow[2]);
        $this->assertEquals('10.00', $productUsdDataRow[3]);
        $this->assertEquals('149.580000', $productUsdDataRow[4]);
        $this->assertEquals('100', $productUsdDataRow[5]);
        $this->assertEquals('149580.00000000', $productUsdDataRow[6]);

        // 存在しないIDは-で行が埋まっている
        $gacha2DataRow = $excelDatacollection->first(fn($row) => $row[0] === 'gacha_id_2');
        $this->assertEquals('gacha_id_2', $gacha2DataRow[0]);
        $this->assertEquals('-', $gacha2DataRow[1]);
        $this->assertEquals('-', $gacha2DataRow[2]);
        $this->assertEquals('-', $gacha2DataRow[3]);
        $this->assertEquals('-', $gacha2DataRow[4]);
        $this->assertEquals('-', $gacha2DataRow[5]);
        $this->assertEquals('-', $gacha2DataRow[6]);

        $product2DataRow = $excelDatacollection->first(fn($row) => $row[0] === 'product_id_2');
        $this->assertEquals('product_id_2', $product2DataRow[0]);
        $this->assertEquals('-', $product2DataRow[1]);
        $this->assertEquals('-', $product2DataRow[2]);
        $this->assertEquals('-', $product2DataRow[3]);
        $this->assertEquals('-', $product2DataRow[4]);
        $this->assertEquals('-', $product2DataRow[5]);
        $this->assertEquals('-', $product2DataRow[6]);

        // ファイル名が指定した期間から付けられている
        $this->assertEquals(
            $expectedFileName,
            $collaboAggregation->getFileName(),
        );
    }

    /**
     * @return array[]
     */
    public static function collectionSandboxData(): array
    {
        return [
            'サンドボックスデータを含めない' => [
                false,
                'コラボ消費通貨集計レポート_20230101000000-20230131235959.xlsx'
            ],
            'サンドボックスデータを含める' => [
                true,
                'コラボ消費通貨集計レポート_20230101000000-20230131235959_サンドボックスデータ含む.xlsx'
            ],
        ];
    }

    public static function verifyData(): array
    {
        return [
            '通貨レートが全て記載' => [
                [
                    self::makeCollectItem(
                        'gacha',
                        'gacha_id_1',
                        'JPY',
                        '2023-01',
                        '10.00',
                        '100',
                        '1',
                        '1000.00000000'
                    ),
                    self::makeCollectItem(
                        'product',
                        'product_id_1',
                        'USD',
                        '2023-01',
                        '10.00',
                        '100',
                        '149.580000',
                        '149580.00000000'
                    ),
                ],
                '', // 警告文なし
                [], // オプションもなし
            ],
            '通貨レートが抜けている' => [
                [
                    self::makeCollectItem(
                        'gacha',
                        'gacha_id_1',
                        'JPY',
                        '2023-01',
                        '10.00',
                        '100',
                        '1',
                        '1000.00000000'
                    ),
                    self::makeCollectItem(
                        'gacha',
                        'gacha_id_1',
                        'USD',
                        '2023-01',
                        '10.00',
                        '100',
                        '',
                        ''
                    ),
                    self::makeCollectItem(
                        'gacha',
                        'gacha_id_1',
                        'EUR',
                        '2023-01',
                        '10.00',
                        '100',
                        '',
                        ''
                    ),
                    self::makeCollectItem(
                        'gacha',
                        'gacha_id_1',
                        'CAD',
                        '2023-01',
                        '10.00',
                        '100',
                        '110.060000',
                        '110060.00000000'
                    ),
                ],
                '通貨レートが空白のデータがあります。  対象通貨: EUR, USD', // 警告文
                [
                    'A4:H4' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_YELLOW]]],
                    'A5:H5' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_YELLOW]]],
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('verifyData')]
    public function verify_通貨レートが抜けている場合の警告文(array $data, string $expectedMessages, array $expectedOptions): void
    {
        // Setup
        $startAt = new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo');
        $endAt = new Carbon('2023-01-31 23:59:59', 'Asia/Tokyo');
        $collection = collect($data);
        $searchTriggers = [
            [
                'type' => 'gacha',
                'ids' => [
                    'gacha_id_1',
                    // 存在しないID
                    'gacha_id_2',
                ],
            ],
            [
                'type' => 'product',
                'ids' => [
                    'product_id_1',
                    // 存在しないID
                    'product_id_2',
                ],
            ],

        ];

        $collaboAggregation = new CollaboAggregation(
            $collection,
            $startAt,
            $endAt,
            $searchTriggers,
            true,
        );

        // Exercise
        $messages = $this->callMethod($collaboAggregation, 'verify');
        $collaboAggregation->collection();

        // Verify
        $this->assertEquals($expectedMessages, $messages);
    }

    private static function makeCollectItem(
        string $triggerType,
        string $triggerId,
        string $currencyCode,
        string $yearMonthCreatedAt,
        string $pricePerAmount,
        string $sumAmount,
        string $ttm,
        string $rateCalculatedMoney
    ): array {
        return [
            'trigger_type' => $triggerType,
            'trigger_id' => $triggerId,
            'currency_code' => $currencyCode,
            'year_month_created_at' => $yearMonthCreatedAt,
            'price_per_amount' => $pricePerAmount,
            'sum_amount' => $sumAmount,
            'ttm' => $ttm,
            'rate_calculated_money' => $rateCalculatedMoney
        ];
    }
}
