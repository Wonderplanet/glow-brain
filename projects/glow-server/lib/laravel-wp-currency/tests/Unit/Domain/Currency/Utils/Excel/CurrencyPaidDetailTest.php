<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Utils\Excel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyPaidDetail;

class CurrencyPaidDetailTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[DataProvider('platformData')]
    public function collection_出力データチェック(
        string $billingPlatform,
        string $expectedTitle
    ): void {
        // setup
        $endAt = Carbon::create(2023, 12, 31, 23, 59, 59);
        $summaryData = [
            [
                'soldAmountByPaid' => '400',
                'consumeAmountByPaid' => '200',
                'invalidPaidAmount' => '150',
                'remainingAmountByPaid' => '50',
                'pricePerAmount' => '10.00',
                'soldAmountMoney' => '4,000.00',
                'consumeAmountMoney' => '2,000.00',
                'remainingAmountMoney' => '2,000.00',
            ],
            [
                'soldAmountByPaid' => '100',
                'consumeAmountByPaid' => '100',
                'invalidPaidAmount' => '0',
                'remainingAmountByPaid' => '0',
                'pricePerAmount' => '1.00',
                'soldAmountMoney' => '100.00',
                'consumeAmountMoney' => '100.00',
                'remainingAmountMoney' => '0.00',
            ],
        ];
        $currencyPaidDetail = new CurrencyPaidDetail(
            $endAt,
            collect($summaryData),
            $billingPlatform
        );

        // Verify
        $excelDataArray = $currencyPaidDetail
            ->collection()
            ->toArray();

        $messageRow = $excelDataArray[0];
        $this->assertEquals('', $messageRow[0]);

        $headerRow = $excelDataArray[1];
        $this->assertEquals('集計期間', $headerRow[0]);
        $this->assertEquals('有償通貨販売個数', $headerRow[1]);
        $this->assertEquals('有償通貨消費個数', $headerRow[2]);
        $this->assertEquals('無効有償通貨', $headerRow[3]);
        $this->assertEquals('有償通貨残個数(有効)', $headerRow[4]);
        $this->assertEquals('有償通貨単価', $headerRow[5]);
        $this->assertEquals('有償通貨販売金額', $headerRow[6]);
        $this->assertEquals('有償通貨消費金額', $headerRow[7]);
        $this->assertEquals('有償一次通貨残高', $headerRow[8]);

        $unitRow = $excelDataArray[2];
        $this->assertEquals('単位', $unitRow[0]);
        $this->assertEquals('個', $unitRow[1]);
        $this->assertEquals('個', $unitRow[2]);
        $this->assertEquals('個', $unitRow[3]);
        $this->assertEquals('個', $unitRow[4]);
        $this->assertEquals('￥', $unitRow[5]);
        $this->assertEquals('￥', $unitRow[6]);
        $this->assertEquals('￥', $unitRow[7]);
        $this->assertEquals('￥', $unitRow[8]);

        $dataRow1 = $excelDataArray[3];
        $this->assertEquals('リリース〜2023-12', $dataRow1[0]);
        $this->assertEquals('100', $dataRow1[1]);
        $this->assertEquals('100', $dataRow1[2]);
        $this->assertEquals('0', $dataRow1[3]);
        $this->assertEquals('0', $dataRow1[4]);
        $this->assertEquals('1.00', $dataRow1[5]);
        $this->assertEquals('100.00', $dataRow1[6]);
        $this->assertEquals('100.00', $dataRow1[7]);
        $this->assertEquals('0.00', $dataRow1[8]);

        $dataRow2 = $excelDataArray[4];
        $this->assertEquals('リリース〜2023-12', $dataRow2[0]);
        $this->assertEquals('400', $dataRow2[1]);
        $this->assertEquals('200', $dataRow2[2]);
        $this->assertEquals('150', $dataRow2[3]);
        $this->assertEquals('50', $dataRow2[4]);
        $this->assertEquals('10.00', $dataRow2[5]);
        $this->assertEquals('4,000.00', $dataRow2[6]);
        $this->assertEquals('2,000.00', $dataRow2[7]);
        $this->assertEquals('2,000.00', $dataRow2[8]);

        $this->assertEquals($expectedTitle, $currencyPaidDetail->title());
    }

    /**
     * @return array
     */
    public static function platformData(): array
    {
        return [
            '全プラットフォーム' => ['', '日本累計(内訳)'],
            'AppStore' => [CurrencyConstants::PLATFORM_APPSTORE, '日本Apple(内訳)'],
            'GooglePlay' => [CurrencyConstants::PLATFORM_GOOGLEPLAY, '日本Google(内訳)'],
        ];
    }
}
