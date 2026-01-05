<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\AdmForeignCurrencyRate;
use WonderPlanet\Domain\Currency\Repositories\AdmForeignCurrencyRateRepository;

class AdmForeignCurrencyRateRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AdmForeignCurrencyRateRepository $admForeignCurrencyRateRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->admForeignCurrencyRateRepository = $this->app->make(AdmForeignCurrencyRateRepository::class);
    }

    #[Test]
    public function insert_外貨為替相場データが追加されていること()
    {
        // setup
        $year = 2023;
        $month = 12;
        $currency = 'US Dollar';
        $currencyName = '米ドル';
        $currencyCode = 'USD';
        $tts = '150.58';
        $ttb = '148.58';

        // Exercise
        $this->admForeignCurrencyRateRepository->insert(
            $year,
            $month,
            $currency,
            $currencyName,
            $currencyCode,
            $tts,
            $ttb
        );

        // Verify
        $results = $this->admForeignCurrencyRateRepository
            ->getCollectionByYearAndMonth(2023, 12);
        /** @var AdmForeignCurrencyRate $result */
        $result = $results->first();

        $this->assertEquals('2023', $result->year);
        $this->assertEquals('12', $result->month);
        $this->assertEquals('USD', $result->currency_code);
        $this->assertEquals('US Dollar', $result->currency);
        $this->assertEquals('米ドル', $result->currency_name);
        $this->assertEquals('150.580000', $result->tts);
        $this->assertEquals('148.580000', $result->ttb);
        $this->assertEquals('149.580000', $result->ttm);
    }

    #[Test]
    public function getCollectionByYearAndMonth_指定年月のデータが取得できているか(): void
    {
        // setup
        $inputs = [
            [
                'id' => '1',
                'year' => '2023',
                'month' => '11',
                'currency' => 'US Dollar',
                'currency_name' => '米ドル',
                'currency_code' => 'USD',
                'tts' => '100.00',
                'ttb' => '200.00',
                'ttm' => '150.00',
            ],
            [
                'id' => '2',
                'year' => '2023',
                'month' => '12',
                'currency' => 'US Dollar',
                'currency_name' => '米ドル',
                'currency_code' => 'USD',
                'tts' => '150.58',
                'ttb' => '148.58',
                'ttm' => '149.58',
            ],
        ];
        AdmForeignCurrencyRate::query()->insert($inputs);

        // Exercise
        $result = $this->admForeignCurrencyRateRepository->getCollectionByYearAndMonth(2023, 12);

        // Verify
        $this->assertEquals('2', $result[0]['id']);
        $this->assertEquals('2023', $result[0]['year']);
        $this->assertEquals('12', $result[0]['month']);
        $this->assertEquals('US Dollar', $result[0]['currency']);
        $this->assertEquals('米ドル', $result[0]['currency_name']);
        $this->assertEquals('USD', $result[0]['currency_code']);
        $this->assertEquals('150.580000', $result[0]['tts']);
        $this->assertEquals('148.580000', $result[0]['ttb']);
        $this->assertEquals('149.580000', $result[0]['ttm']);
    }

     #[Test]
     #[DataProvider('makeCurrencyRateCaseQueryStrData')]
    public function makeCurrencyRateCaseQueryStr_case文生成(int $year, int $month, string $expected): void
    {
        // setup
        $inputs = [
            [
                'id' => '1',
                'year' => '2023',
                'month' => '11',
                'currency' => 'US Dollar',
                'currency_name' => '米ドル',
                'currency_code' => 'USD',
                'tts' => '100.00',
                'ttb' => '200.00',
                'ttm' => '150.00',
            ],
            [
                'id' => '2',
                'year' => '2023',
                'month' => '12',
                'currency' => 'Euro',
                'currency_name' => 'ユーロ',
                'currency_code' => 'EUR',
                'tts' => '158.62',
                'ttb' => '155.62',
                'ttm' => '157.12',
            ],
            [
                'id' => '3',
                'year' => '2023',
                'month' => '12',
                'currency' => 'Hong Kong Dollar',
                'currency_name' => '香港ドル',
                'currency_code' => 'HKD',
                'tts' => '18.58',
                'ttb' => '17.72',
                'ttm' => '18.15',
            ],
            [
                'id' => '4',
                'year' => '2023',
                'month' => '12',
                'currency' => 'US Dollar',
                'currency_name' => '米ドル',
                'currency_code' => 'USD',
                'tts' => '150.58',
                'ttb' => '148.58',
                'ttm' => '149.58',
            ],
        ];
        AdmForeignCurrencyRate::query()->insert($inputs);

        // Exercise
        $result = $this->admForeignCurrencyRateRepository->makeCurrencyRateCaseQueryStr($year, $month);

        // Verify
        $this->assertSame(
            $expected,
            $result
        );
    }

    /**
     * @return array[]
     */
    public static function makeCurrencyRateCaseQueryStrData(): array
    {
        $expected1 = 'CASE';
        $expected1 .= " WHEN currency_code = 'JPY' THEN '1'";
        $expected1 .= " WHEN currency_code = 'EUR' THEN '157.120000'";
        $expected1 .= " WHEN currency_code = 'HKD' THEN '18.150000'";
        $expected1 .= " WHEN currency_code = 'USD' THEN '149.580000'";
        $expected1 .= " ELSE ''";
        $expected1 .= " END AS currency_rate";

        $expected2 = 'CASE';
        $expected2 .= " WHEN currency_code = 'JPY' THEN '1'";
        $expected2 .= " ELSE ''";
        $expected2 .= " END AS currency_rate";

        return [
            'currency_codeごとの為替レートcase文' => [
                2023,
                12,
                $expected1,
            ],
            'currency_rateが空文字' => [
                2024,
                1,
                $expected2,
            ],
        ];
    }
}
