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
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyBalanceAggregationByForeignCountry;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyBalanceMultipleSheets;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyPaidDetail;

class CurrencyBalanceMultipleSheetsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[DataProvider('fileNameData')]
    public function getFileName_チェック(
        bool $isIncludeSandbox,
        string $expectedFileName
    ): void {
        // Setup
        $now = Carbon::make('2023-12-01 00:00:00');

        // Exercise
        $currencyBalanceMultipleSheets = new CurrencyBalanceMultipleSheets(
            '2023',
            '12',
            [
                new CurrencyBalanceAggregation($now, collect(), CurrencyConstants::PLATFORM_APPSTORE),
                new CurrencyPaidDetail($now, collect(), CurrencyConstants::PLATFORM_APPSTORE),
                new CurrencyBalanceAggregationByForeignCountry($now, collect()),
            ],
            $isIncludeSandbox
        );

        // Verify
        // ファイル名のチェック
        $this->assertEquals(
            $expectedFileName,
            $currencyBalanceMultipleSheets->getFileName()
        );
    }

    /**
     * @return array[]
     */
    public static function fileNameData(): array
    {
        return [
            'サンドボックスデータを含めない' => [
                false,
                '一次通貨残高集計レポート_2023-12.xlsx'
            ],
            'サンドボックスデータを含める' => [
                true,
                '一次通貨残高集計レポート_2023-12_サンドボックスデータ含む.xlsx'
            ],
        ];
    }
}
