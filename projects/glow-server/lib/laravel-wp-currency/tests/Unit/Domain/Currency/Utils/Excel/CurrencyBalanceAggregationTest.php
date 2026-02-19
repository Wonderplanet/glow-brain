<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Utils\Excel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyBalanceAggregation;

class CurrencyBalanceAggregationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[DataProvider('platformData')]
    public function collection_出力データチェック(
        string $billingPlatform,
        string $expectedTitle
    ): void {
        // Setup
        $endAt = Carbon::create(2023, 12, 31, 23, 59, 59);
        $summaryData = [
            'soldAmountByPaid' => '400',
            'consumeAmountByPaid' => '200',
            'invalidPaidAmount' => '150',
            'remainingAmountByPaid' => '50',
            'soldAmountMoney' => '4,000.00',
            'consumeAmountMoney' => '2,000.00',
            'remainingAmountMoney' => '2,000.00',
        ];
        $currencyBalanceAggregation = new CurrencyBalanceAggregation(
            $endAt,
            collect($summaryData),
            $billingPlatform
        );

        // Verify
        $excelDataArray = $currencyBalanceAggregation
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
        $this->assertEquals('有償通貨販売金額', $headerRow[5]);
        $this->assertEquals('有償通貨消費金額', $headerRow[6]);
        $this->assertEquals('有償一次通貨残高', $headerRow[7]);

        $unitRow = $excelDataArray[2];
        $this->assertEquals('単位', $unitRow[0]);
        $this->assertEquals('個', $unitRow[1]);
        $this->assertEquals('個', $unitRow[2]);
        $this->assertEquals('個', $unitRow[3]);
        $this->assertEquals('個', $unitRow[4]);
        $this->assertEquals('￥', $unitRow[5]);
        $this->assertEquals('￥', $unitRow[6]);
        $this->assertEquals('￥', $unitRow[7]);

        $dataRow = $excelDataArray[3];
        $this->assertEquals('リリース〜2023-12', $dataRow[0]);
        $this->assertEquals('400', $dataRow[1]);
        $this->assertEquals('200', $dataRow[2]);
        $this->assertEquals('150', $dataRow[3]);
        $this->assertEquals('50', $dataRow[4]);
        $this->assertEquals('4,000.00', $dataRow[5]);
        $this->assertEquals('2,000.00', $dataRow[6]);
        $this->assertEquals('2,000.00', $dataRow[7]);

        $this->assertEquals($expectedTitle, $currencyBalanceAggregation->title());
    }

    /**
     * @return array
     */
    public static function platformData(): array
    {
        return [
            '全プラットフォーム' => ['', '日本累計(サマリー)'],
            'AppStore' => [CurrencyConstants::PLATFORM_APPSTORE, '日本Apple(サマリー)'],
            'GooglePlay' => [CurrencyConstants::PLATFORM_GOOGLEPLAY, '日本Google(サマリー)'],
        ];
    }
}
