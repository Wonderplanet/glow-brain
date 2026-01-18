<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Utils\Excel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Excel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyBalanceAggregationByForeignCountry;

class CurrencyBalanceAggregationByForeignCountryTest extends TestCase
{
    use RefreshDatabase;

    private CurrencyBalanceAggregationByForeignCountry $currencyBalanceAggregationByForeignCountry;

    public function setUp(): void
    {
        parent::setUp();

        $endAt = Carbon::create(2023, 12, 31, 23, 59, 59);
        $summaryData = [
            [
                'soldAmountByPaid' => '200',
                'consumeAmountByPaid' => '100',
                'invalidPaidAmount' => '0',
                'remainingAmountByPaid' => '100',
                'currencyCode' => 'USD',
                'rate' => '149.580000',
                'remainingAmountMoney' => '1,000.00',
                'rateCalculatedRemainingAmountMoney' => '149580.00000000',
            ],
            [
                'soldAmountByPaid' => '300',
                'consumeAmountByPaid' => '0',
                'invalidPaidAmount' => '0',
                'remainingAmountByPaid' => '300',
                'currencyCode' => 'EUR',
                'rate' => '',
                'remainingAmountMoney' => '199.99',
                'rateCalculatedRemainingAmountMoney' => '',
            ],
        ];
        $this->currencyBalanceAggregationByForeignCountry =
            new CurrencyBalanceAggregationByForeignCountry(
                $endAt,
                collect($summaryData)
            );
    }

    #[Test]
    public function collection_出力データチェック(): void
    {
        // Verify
        $excelDataArray = $this->currencyBalanceAggregationByForeignCountry
            ->collection()
            ->toArray();

        $messageRow = $excelDataArray[0];
        $this->assertEquals('通貨レートが空白のデータがあります。  対象通貨: EUR', $messageRow[0]);

        $headerRow = $excelDataArray[1];
        $this->assertEquals('集計期間', $headerRow[0]);
        $this->assertEquals('有償通貨販売個数', $headerRow[1]);
        $this->assertEquals('有償通貨消費個数', $headerRow[2]);
        $this->assertEquals('無効有償通貨', $headerRow[3]);
        $this->assertEquals('有償通貨残個数(有効)', $headerRow[4]);
        $this->assertEquals('為替コード', $headerRow[5]);
        $this->assertEquals('為替レート(月末TTM)', $headerRow[6]);
        $this->assertEquals('有償一次通貨残高(現地通貨換算金額)', $headerRow[7]);
        $this->assertEquals('有償一次通貨残高', $headerRow[8]);

        $unitRow = $excelDataArray[2];
        $this->assertEquals('単位', $unitRow[0]);
        $this->assertEquals('個', $unitRow[1]);
        $this->assertEquals('個', $unitRow[2]);
        $this->assertEquals('個', $unitRow[3]);
        $this->assertEquals('個', $unitRow[4]);
        $this->assertEquals('', $unitRow[5]);
        $this->assertEquals('￥', $unitRow[6]);
        $this->assertEquals('通貨コードに伴う', $unitRow[7]);
        $this->assertEquals('￥', $unitRow[8]);

        $dataRow1 = $excelDataArray[3];
        $this->assertEquals('リリース〜2023-12', $dataRow1[0]);
        $this->assertEquals('300', $dataRow1[1]);
        $this->assertEquals('0', $dataRow1[2]);
        $this->assertEquals('0', $dataRow1[3]);
        $this->assertEquals('300', $dataRow1[4]);
        $this->assertEquals('EUR', $dataRow1[5]);
        $this->assertEquals('', $dataRow1[6]);
        $this->assertEquals('199.99', $dataRow1[7]);
        $this->assertEquals('=G4 * H4', $dataRow1[8]);

        $dataRow2 = $excelDataArray[4];
        $this->assertEquals('リリース〜2023-12', $dataRow2[0]);
        $this->assertEquals('200', $dataRow2[1]);
        $this->assertEquals('100', $dataRow2[2]);
        $this->assertEquals('0', $dataRow2[3]);
        $this->assertEquals('100', $dataRow2[4]);
        $this->assertEquals('USD', $dataRow2[5]);
        $this->assertEquals('149.580000', $dataRow2[6]);
        $this->assertEquals('1,000.00', $dataRow2[7]);
        $this->assertEquals('149580.00000000', $dataRow2[8]);
    }

    public static function verifyData(): array
    {
        return [
            '通貨レートが全て記載' => [
                [
                    [
                        'soldAmountByPaid' => '200',
                        'consumeAmountByPaid' => '100',
                        'invalidPaidAmount' => '0',
                        'remainingAmountByPaid' => '100',
                        'currencyCode' => 'USD',
                        'rate' => '149.580000',
                        'remainingAmountMoney' => '1,000.00',
                        'rateCalculatedRemainingAmountMoney' => '149580.00000000',
                    ],
                    [
                        'soldAmountByPaid' => '300',
                        'consumeAmountByPaid' => '0',
                        'invalidPaidAmount' => '0',
                        'remainingAmountByPaid' => '300',
                        'currencyCode' => 'EUR',
                        'rate' => '159.970000',
                        'remainingAmountMoney' => '199.99',
                        'rateCalculatedRemainingAmountMoney' => '',
                    ],
                ],
                '', // 警告文なし
                [], // オプションもなし
            ],
            '通貨レートが抜けている' => [
                [
                    [
                        'soldAmountByPaid' => '200',
                        'consumeAmountByPaid' => '100',
                        'invalidPaidAmount' => '0',
                        'remainingAmountByPaid' => '100',
                        'currencyCode' => 'USD',
                        'rate' => '149.580000',
                        'remainingAmountMoney' => '1,000.00',
                        'rateCalculatedRemainingAmountMoney' => '149580.00000000',
                    ],
                    [
                        'soldAmountByPaid' => '300',
                        'consumeAmountByPaid' => '0',
                        'invalidPaidAmount' => '0',
                        'remainingAmountByPaid' => '300',
                        'currencyCode' => 'EUR',
                        'rate' => '',
                        'remainingAmountMoney' => '199.99',
                        'rateCalculatedRemainingAmountMoney' => '',
                    ],
                    [
                        'soldAmountByPaid' => '300',
                        'consumeAmountByPaid' => '0',
                        'invalidPaidAmount' => '0',
                        'remainingAmountByPaid' => '300',
                        'currencyCode' => 'CAD',
                        'rate' => '',
                        'remainingAmountMoney' => '199.99',
                        'rateCalculatedRemainingAmountMoney' => '',
                    ],
                    [
                        'soldAmountByPaid' => '300',
                        'consumeAmountByPaid' => '0',
                        'invalidPaidAmount' => '0',
                        'remainingAmountByPaid' => '300',
                        'currencyCode' => 'JPY',
                        'rate' => '1',
                        'remainingAmountMoney' => '199.99',
                        'rateCalculatedRemainingAmountMoney' => '',
                    ],
                ],
                '通貨レートが空白のデータがあります。  対象通貨: CAD, EUR',
                [
                    'A4:I4' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_YELLOW]]],
                    'A5:I5' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_YELLOW]]],
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('verifyData')]
    public function verify_通貨レートが抜けている場合の警告文(array $summaryData, string $expectedMessages, array $expectedOptions): void
    {
        // Setup
        $endAt = Carbon::create(2023, 12, 31, 23, 59, 59);

        $currencyBalanceAggregationByForeignCountry =
            new CurrencyBalanceAggregationByForeignCountry(
                $endAt,
                collect($summaryData)
            );

        // Exercise
        $messages = $this->callMethod($currencyBalanceAggregationByForeignCountry, 'verify');
        // ここでExcelのStyleを設定している
        $currencyBalanceAggregationByForeignCountry->collection();

        // Verify
        $summaryStyle = $this->getProperty($currencyBalanceAggregationByForeignCountry, 'summaryStyle');
        $this->assertEquals($expectedMessages, $messages);
        $this->assertEquals($expectedOptions, $summaryStyle);
    }
}
