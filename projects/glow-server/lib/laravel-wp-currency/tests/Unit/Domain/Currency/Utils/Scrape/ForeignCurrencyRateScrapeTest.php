<?php

namespace WonderPlanet\Tests\Unit\Domain\Currency\Utils\Scrape;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Utils\Scrape\ForeignCurrencyRateScrape;

class ForeignCurrencyRateScrapeTest extends TestCase
{
    private const TEST_URL = __DIR__ . '/ForeignCurrencyRateScrapeTestTarget.html';
    private const TEST_TODAY_URL = __DIR__ . '/ForeignCurrencyRateScrapeTestTodayTarget.html';

    private ForeignCurrencyRateScrape $foreignCurrencyRateScrape;

    public function setUp(): void
    {
        parent::setUp();

        $this->foreignCurrencyRateScrape = $this->app->make(ForeignCurrencyRateScrape::class);
    }

    #[Test]
    public function getContent_実行(): void
    {
        // setup
        // テスト用のhtmlコードを読み込む為、makeQueryParameterの返り値を空文字に設定するモックを生成
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['makeQueryParameter']);
        $mock->method('makeQueryParameter')->with(2023, 12)->willReturn('');

        // Exercise
        $result = $this->callMethod(
            $mock,
            'getContent',
            [
                2023,
                12,
                self::TEST_URL,
                true
            ]
        );

        // Verify
        // 結果がfalseにならないこと
        $this->assertNotEquals(false, $result);
    }

    #[Test]
    public function getContent_makeQueryParameterを通らない場合(): void
    {
        // Exercise
        $result = $this->callMethod(
            $this->foreignCurrencyRateScrape,
            'getContent',
            [
                2023,
                7,
                self::TEST_URL,
                false
            ]
        );

        // Verify
        // 結果がfalseにならないこと
        $this->assertNotEquals(false, $result);
    }

    #[Test]
    public function getContent_ユニットテストでの外部通信を禁止(): void
    {
        // Setup
        // 例外で失敗する
        $this->expectException(\Exception::class);

        // Exercise
        $this->callMethod(
            $this->foreignCurrencyRateScrape,
            'getContent',
            [
                2023,
                7,
                "https://localhost",
                true
            ]
        );

        // Verify
        // 結果がここにこないこと
        $this->assertTrue(false);
    }

    #[Test]
    #[DataProvider('makeQueryParameterData')]
    public function makeQueryParameter_yearとmonthからパラメータを生成(int $year, int $month, string $expected): void
    {
        // Exercise
        $result = $this->callMethod(
            $this->foreignCurrencyRateScrape,
            'makeQueryParameter',
            [$year, $month]
        );

        // Verify
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public static function makeQueryParameterData(): array
    {
        return [
            '2023年12月' => [2023, 12, '2312'],
            '2024年1月' => [2024, 1, '2401']
        ];
    }

    #[Test]
    public function parse_正常実行(): void
    {
        // setup
        $htmlCode = $this->getContent();
        $year = 2023;
        $month = 12;
        $yearMonth = '2312';
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['makeQueryParameter', 'getContent']);
        $mock->method('makeQueryParameter')->with($year, $month)->willReturn($yearMonth);
        $mock->method('getContent')->with($year, $month)->willReturn($htmlCode);

        // Exercise
        $resultCollection = $mock->parse($year, $month, false);

        // Verify
        $this->assertEquals(30, $resultCollection->count());
        $resultUsd = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'USD');
        $resultEur = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'EUR');
        $resultCad = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'CAD');
        $resultGbp = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'GBP');
        $resultChf = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'CHF');
        $resultDkk = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'DKK');
        $resultNok = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'NOK');
        $resultSek = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'SEK');
        $resultAud = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'AUD');
        $resultNzd = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'NZD');
        $resultHkd = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'HKD');
        $resultMyr = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'MYR');
        $resultSgd = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'SGD');
        $resultSar = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'SAR');
        $resultAed = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'AED');
        $resultCny = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'CNY');
        $resultThb = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'THB');
        $resultInr = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'INR');
        $resultPkr = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'PKR');
        $resultKwd = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'KWD');
        $resultQar = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'QAR');
        $resultIdr = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'IDR');
        $resultMxn = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'MXN');
        $resultKrw = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'KRW');
        $resultPhp = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'PHP');
        $resultZar = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'ZAR');
        $resultCzk = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'CZK');
        $resultRub = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'RUB');
        $resultHuf = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'HUF');
        $resultPln = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'PLN');
        $resultTry = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'TRY');

        $this->assertEquals('US Dollar', $resultUsd['currency']);
        $this->assertEquals('米ドル', $resultUsd['currencyName']);
        $this->assertEquals('USD', $resultUsd['currencyCode']);
        $this->assertEquals('142.830000', $resultUsd['tts']);
        $this->assertEquals('140.830000', $resultUsd['ttb']);

        $this->assertEquals('Euro', $resultEur['currency']);
        $this->assertEquals('ユーロ', $resultEur['currencyName']);
        $this->assertEquals('EUR', $resultEur['currencyCode']);
        $this->assertEquals('158.620000', $resultEur['tts']);
        $this->assertEquals('155.620000', $resultEur['ttb']);

        $this->assertEquals('Canadian Dollar', $resultCad['currency']);
        $this->assertEquals('カナダ・ドル', $resultCad['currencyName']);
        $this->assertEquals('CAD', $resultCad['currencyCode']);
        $this->assertEquals('108.840000', $resultCad['tts']);
        $this->assertEquals('105.640000', $resultCad['ttb']);

        $this->assertEquals('Pound Sterling', $resultGbp['currency']);
        $this->assertEquals('英ポンド', $resultGbp['currencyName']);
        $this->assertEquals('GBP', $resultGbp['currencyCode']);
        $this->assertEquals('184.680000', $resultGbp['tts']);
        $this->assertEquals('176.680000', $resultGbp['ttb']);

        $this->assertEquals('Swiss Franc', $resultChf['currency']);
        $this->assertEquals('スイス･フラン', $resultChf['currencyName']);
        $this->assertEquals('CHF', $resultChf['currencyCode']);
        $this->assertEquals('169.140000', $resultChf['tts']);
        $this->assertEquals('167.340000', $resultChf['ttb']);

        $this->assertEquals('Danish Krone', $resultDkk['currency']);
        $this->assertEquals('デンマーク・クローネ', $resultDkk['currencyName']);
        $this->assertEquals('DKK', $resultDkk['currencyCode']);
        $this->assertEquals('21.380000', $resultDkk['tts']);
        $this->assertEquals('20.780000', $resultDkk['ttb']);

        $this->assertEquals('Norwegian Krone', $resultNok['currency']);
        $this->assertEquals('ノルウェー・クローネ', $resultNok['currencyName']);
        $this->assertEquals('NOK', $resultNok['currencyCode']);
        $this->assertEquals('14.220000', $resultNok['tts']);
        $this->assertEquals('13.620000', $resultNok['ttb']);

        $this->assertEquals('Swedish Krona', $resultSek['currency']);
        $this->assertEquals('スウェーデン・クローネ', $resultSek['currencyName']);
        $this->assertEquals('SEK', $resultSek['currencyCode']);
        $this->assertEquals('14.630000', $resultSek['tts']);
        $this->assertEquals('13.830000', $resultSek['ttb']);

        $this->assertEquals('Australian Dollar', $resultAud['currency']);
        $this->assertEquals('オーストラリア・ドル', $resultAud['currencyName']);
        $this->assertEquals('AUD', $resultAud['currencyCode']);
        $this->assertEquals('98.940000', $resultAud['tts']);
        $this->assertEquals('94.940000', $resultAud['ttb']);

        $this->assertEquals('New Zealand Dollar', $resultNzd['currency']);
        $this->assertEquals('ニュージーランド・ドル', $resultNzd['currencyName']);
        $this->assertEquals('NZD', $resultNzd['currencyCode']);
        $this->assertEquals('91.910000', $resultNzd['tts']);
        $this->assertEquals('87.910000', $resultNzd['ttb']);

        $this->assertEquals('Hong Kong Dollar', $resultHkd['currency']);
        $this->assertEquals('香港ドル', $resultHkd['currencyName']);
        $this->assertEquals('HKD', $resultHkd['currencyCode']);
        $this->assertEquals('18.580000', $resultHkd['tts']);
        $this->assertEquals('17.720000', $resultHkd['ttb']);

        $this->assertNull($resultMyr); // 'unquoted'のためデータなし

        $this->assertEquals('Singapore Dollar', $resultSgd['currency']);
        $this->assertEquals('シンガポール・ドル', $resultSgd['currencyName']);
        $this->assertEquals('SGD', $resultSgd['currencyCode']);
        $this->assertEquals('108.310000', $resultSgd['tts']);
        $this->assertEquals('106.650000', $resultSgd['ttb']);

        $this->assertEquals('Saudi Riyal', $resultSar['currency']);
        $this->assertEquals('サウジ・リアル', $resultSar['currencyName']);
        $this->assertEquals('SAR', $resultSar['currencyCode']);
        $this->assertEquals('38.680000', $resultSar['tts']);
        $this->assertEquals('37.080000', $resultSar['ttb']);

        $this->assertEquals('UAE Dirham', $resultAed['currency']);
        $this->assertEquals('UAEディルハム', $resultAed['currencyName']);
        $this->assertEquals('AED', $resultAed['currencyCode']);
        $this->assertEquals('39.230000', $resultAed['tts']);
        $this->assertEquals('37.870000', $resultAed['ttb']);

        $this->assertEquals('Yuan Renminbi', $resultCny['currency']);
        $this->assertEquals('中国・人民元', $resultCny['currencyName']);
        $this->assertEquals('CNY', $resultCny['currencyCode']);
        $this->assertEquals('20.230000', $resultCny['tts']);
        $this->assertEquals('19.630000', $resultCny['ttb']);

        $this->assertEquals('Baht', $resultThb['currency']);
        $this->assertEquals('タイ・バーツ', $resultThb['currencyName']);
        $this->assertEquals('THB', $resultThb['currencyCode']);
        $this->assertEquals('4.210000', $resultThb['tts']);
        $this->assertEquals('4.050000', $resultThb['ttb']);

        $this->assertEquals('Indian Rupee', $resultInr['currency']);
        $this->assertEquals('インド・ルピー', $resultInr['currencyName']);
        $this->assertEquals('INR', $resultInr['currencyCode']);
        $this->assertEquals('1.870000', $resultInr['tts']);
        $this->assertEquals('1.570000', $resultInr['ttb']);

        $this->assertEquals('Pakistan Rupee', $resultPkr['currency']);
        $this->assertEquals('パキスタン・ルピー', $resultPkr['currencyName']);
        $this->assertEquals('PKR', $resultPkr['currencyCode']);
        $this->assertEquals('0.660000', $resultPkr['tts']);
        $this->assertEquals('0.360000', $resultPkr['ttb']);

        $this->assertEquals('Kuwaiti Dinar', $resultKwd['currency']);
        $this->assertEquals('クウェート・ディナール', $resultKwd['currencyName']);
        $this->assertEquals('KWD', $resultKwd['currencyCode']);
        $this->assertEquals('468.640000', $resultKwd['tts']);
        $this->assertEquals('452.640000', $resultKwd['ttb']);

        $this->assertEquals('Qatari Rial', $resultQar['currency']);
        $this->assertEquals('カタール・リアル', $resultQar['currencyName']);
        $this->assertEquals('QAR', $resultQar['currencyCode']);
        $this->assertEquals('39.670000', $resultQar['tts']);
        $this->assertEquals('38.310000', $resultQar['ttb']);

        $this->assertEquals('Indonesia Rupiah', $resultIdr['currency']);
        $this->assertEquals('インドネシア・ルピア', $resultIdr['currencyName']);
        $this->assertEquals('IDR', $resultIdr['currencyCode']);
        // per 100 unitのため表示されている数値から100を割った数値に変わっていることを確認する
        $this->assertEquals('0.010400', $resultIdr['tts']);
        $this->assertEquals('0.008000', $resultIdr['ttb']);

        $this->assertEquals('Mexican Peso', $resultMxn['currency']);
        $this->assertEquals('メキシコ・ペソ', $resultMxn['currencyName']);
        $this->assertEquals('MXN', $resultMxn['currencyCode']);
        $this->assertEquals('9.350000', $resultMxn['tts']);
        $this->assertEquals('7.350000', $resultMxn['ttb']);

        $this->assertEquals('Won', $resultKrw['currency']);
        $this->assertEquals('韓国ウォン', $resultKrw['currencyName']);
        $this->assertEquals('KRW', $resultKrw['currencyCode']);
        // per 100 unitのため表示されている数値から100を割った数値に変わっていることを確認する
        $this->assertEquals('0.112500', $resultKrw['tts']);
        $this->assertEquals('0.108500', $resultKrw['ttb']);

        $this->assertEquals('Philippine Peso', $resultPhp['currency']);
        $this->assertEquals('フィリピン・ペソ', $resultPhp['currencyName']);
        $this->assertEquals('PHP', $resultPhp['currencyCode']);
        $this->assertEquals('2.720000', $resultPhp['tts']);
        $this->assertEquals('2.440000', $resultPhp['ttb']);

        $this->assertEquals('Rand', $resultZar['currency']);
        $this->assertEquals('南アフリカ･ランド', $resultZar['currencyName']);
        $this->assertEquals('ZAR', $resultZar['currencyCode']);
        $this->assertEquals('9.120000', $resultZar['tts']);
        $this->assertEquals('6.120000', $resultZar['ttb']);

        $this->assertEquals('Czech Koruna', $resultCzk['currency']);
        $this->assertEquals('チェコ・コルナ', $resultCzk['currencyName']);
        $this->assertEquals('CZK', $resultCzk['currencyCode']);
        $this->assertEquals('6.490000', $resultCzk['tts']);
        $this->assertEquals('6.250000', $resultCzk['ttb']);

        $this->assertEquals('Russian Ruble', $resultRub['currency']);
        $this->assertEquals('ロシア・ルーブル', $resultRub['currencyName']);
        $this->assertEquals('RUB', $resultRub['currencyCode']);
        $this->assertEquals('1.860000', $resultRub['tts']);
        $this->assertEquals('1.360000', $resultRub['ttb']);

        $this->assertEquals('Hungarian Forint', $resultHuf['currency']);
        $this->assertEquals('ハンガリー・フォリント', $resultHuf['currencyName']);
        $this->assertEquals('HUF', $resultHuf['currencyCode']);
        $this->assertEquals('0.430000', $resultHuf['tts']);
        $this->assertEquals('0.390000', $resultHuf['ttb']);

        $this->assertEquals('Polish Zloty', $resultPln['currency']);
        $this->assertEquals('ポーランド・ズロチ', $resultPln['currencyName']);
        $this->assertEquals('PLN', $resultPln['currencyCode']);
        $this->assertEquals('37.420000', $resultPln['tts']);
        $this->assertEquals('35.020000', $resultPln['ttb']);

        $this->assertEquals('Turkish Lira', $resultTry['currency']);
        $this->assertEquals('トルコ・リラ', $resultTry['currencyName']);
        $this->assertEquals('TRY', $resultTry['currencyCode']);
        $this->assertEquals('7.320000', $resultTry['tts']);
        $this->assertEquals('2.320000', $resultTry['ttb']);
    }

    #[Test]
    public function parse_正常実行_今月対象(): void
    {
        // setup
        $htmlCode = $this->getTodayContent();
        $year = 2025;
        $month = 7;
        $yearMonth = '2025';
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['makeQueryParameter', 'getContent']);
        $mock->method('makeQueryParameter')->with($year, $month)->willReturn($yearMonth);
        $mock->method('getContent')->with($year, $month)->willReturn($htmlCode);

        // Exercise
        $resultCollection = $mock->parse($year, $month, true);

        // Verify
        $this->assertEquals(30, $resultCollection->count());
        $resultUsd = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'USD');
        $resultEur = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'EUR');
        $resultCad = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'CAD');
        $resultGbp = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'GBP');
        $resultChf = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'CHF');
        $resultDkk = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'DKK');
        $resultNok = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'NOK');
        $resultSek = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'SEK');
        $resultAud = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'AUD');
        $resultNzd = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'NZD');
        $resultHkd = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'HKD');
        $resultMyr = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'MYR');
        $resultSgd = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'SGD');
        $resultSar = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'SAR');
        $resultAed = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'AED');
        $resultCny = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'CNY');
        $resultThb = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'THB');
        $resultInr = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'INR');
        $resultPkr = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'PKR');
        $resultKwd = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'KWD');
        $resultQar = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'QAR');
        $resultIdr = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'IDR');
        $resultMxn = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'MXN');
        $resultKrw = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'KRW');
        $resultPhp = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'PHP');
        $resultZar = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'ZAR');
        $resultCzk = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'CZK');
        $resultRub = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'RUB');
        $resultHuf = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'HUF');
        $resultPln = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'PLN');
        $resultTry = $resultCollection->first(fn ($row) => $row['currencyCode'] === 'TRY');

        $this->assertEquals('US Dollar', $resultUsd['currency']);
        $this->assertEquals('米ドル', $resultUsd['currencyName']);
        $this->assertEquals('USD', $resultUsd['currencyCode']);
        $this->assertEquals('150.390000', $resultUsd['tts']);
        $this->assertEquals('148.390000', $resultUsd['ttb']);

        $this->assertEquals('Euro', $resultEur['currency']);
        $this->assertEquals('ユーロ', $resultEur['currencyName']);
        $this->assertEquals('EUR', $resultEur['currencyCode']);
        $this->assertEquals('172.250000', $resultEur['tts']);
        $this->assertEquals('169.250000', $resultEur['ttb']);

        $this->assertEquals('Canadian Dollar', $resultCad['currency']);
        $this->assertEquals('カナダ・ドル', $resultCad['currencyName']);
        $this->assertEquals('CAD', $resultCad['currencyCode']);
        $this->assertEquals('109.590000', $resultCad['tts']);
        $this->assertEquals('106.390000', $resultCad['ttb']);

        $this->assertEquals('Pound Sterling', $resultGbp['currency']);
        $this->assertEquals('英ポンド', $resultGbp['currencyName']);
        $this->assertEquals('GBP', $resultGbp['currencyCode']);
        $this->assertEquals('201.930000', $resultGbp['tts']);
        $this->assertEquals('193.930000', $resultGbp['ttb']);

        $this->assertEquals('Swiss Franc', $resultChf['currency']);
        $this->assertEquals('スイス･フラン', $resultChf['currencyName']);
        $this->assertEquals('CHF', $resultChf['currencyCode']);
        $this->assertEquals('184.520000', $resultChf['tts']);
        $this->assertEquals('182.720000', $resultChf['ttb']);

        $this->assertEquals('Danish Krone', $resultDkk['currency']);
        $this->assertEquals('デンマーク・クローネ', $resultDkk['currencyName']);
        $this->assertEquals('DKK', $resultDkk['currencyCode']);
        $this->assertEquals('23.180000', $resultDkk['tts']);
        $this->assertEquals('22.580000', $resultDkk['ttb']);

        $this->assertEquals('Norwegian Krone', $resultNok['currency']);
        $this->assertEquals('ノルウェー・クローネ', $resultNok['currencyName']);
        $this->assertEquals('NOK', $resultNok['currencyCode']);
        $this->assertEquals('14.810000', $resultNok['tts']);
        $this->assertEquals('14.210000', $resultNok['ttb']);

        $this->assertEquals('Swedish Krona', $resultSek['currency']);
        $this->assertEquals('スウェーデン・クローネ', $resultSek['currencyName']);
        $this->assertEquals('SEK', $resultSek['currencyCode']);
        $this->assertEquals('15.680000', $resultSek['tts']);
        $this->assertEquals('14.880000', $resultSek['ttb']);

        $this->assertEquals('Australian Dollar', $resultAud['currency']);
        $this->assertEquals('オーストラリア・ドル', $resultAud['currencyName']);
        $this->assertEquals('AUD', $resultAud['currencyCode']);
        $this->assertEquals('98.220000', $resultAud['tts']);
        $this->assertEquals('94.220000', $resultAud['ttb']);

        $this->assertEquals('New Zealand Dollar', $resultNzd['currency']);
        $this->assertEquals('ニュージーランド・ドル', $resultNzd['currencyName']);
        $this->assertEquals('NZD', $resultNzd['currencyCode']);
        $this->assertEquals('90.180000', $resultNzd['tts']);
        $this->assertEquals('86.180000', $resultNzd['ttb']);

        $this->assertEquals('Hong Kong Dollar', $resultHkd['currency']);
        $this->assertEquals('香港ドル', $resultHkd['currencyName']);
        $this->assertEquals('HKD', $resultHkd['currencyCode']);
        $this->assertEquals('19.450000', $resultHkd['tts']);
        $this->assertEquals('18.590000', $resultHkd['ttb']);

        $this->assertNull($resultMyr); // 'unquoted'のためデータなし

        $this->assertEquals('Singapore Dollar', $resultSgd['currency']);
        $this->assertEquals('シンガポール・ドル', $resultSgd['currencyName']);
        $this->assertEquals('SGD', $resultSgd['currencyCode']);
        $this->assertEquals('116.150000', $resultSgd['tts']);
        $this->assertEquals('114.490000', $resultSgd['ttb']);

        $this->assertEquals('Saudi Riyal', $resultSar['currency']);
        $this->assertEquals('サウジ・リヤル', $resultSar['currencyName']);
        $this->assertEquals('SAR', $resultSar['currencyCode']);
        $this->assertEquals('40.710000', $resultSar['tts']);
        $this->assertEquals('39.110000', $resultSar['ttb']);

        $this->assertEquals('UAE Dirham', $resultAed['currency']);
        $this->assertEquals('UAEディルハム', $resultAed['currencyName']);
        $this->assertEquals('AED', $resultAed['currencyCode']);
        $this->assertEquals('41.430000', $resultAed['tts']);
        $this->assertEquals('40.070000', $resultAed['ttb']);

        $this->assertEquals('Yuan Renminbi', $resultCny['currency']);
        $this->assertEquals('中国・人民元', $resultCny['currencyName']);
        $this->assertEquals('CNY', $resultCny['currencyCode']);
        $this->assertEquals('21.030000', $resultCny['tts']);
        $this->assertEquals('20.430000', $resultCny['ttb']);

        $this->assertEquals('Baht', $resultThb['currency']);
        $this->assertEquals('タイ・バーツ', $resultThb['currencyName']);
        $this->assertEquals('THB', $resultThb['currencyCode']);
        $this->assertEquals('4.630000', $resultThb['tts']);
        $this->assertEquals('4.470000', $resultThb['ttb']);

        $this->assertEquals('Indian Rupee', $resultInr['currency']);
        $this->assertEquals('インド・ルピー', $resultInr['currencyName']);
        $this->assertEquals('INR', $resultInr['currencyCode']);
        $this->assertEquals('1.860000', $resultInr['tts']);
        $this->assertEquals('1.560000', $resultInr['ttb']);

        $this->assertEquals('Pakistan Rupee', $resultPkr['currency']);
        $this->assertEquals('パキスタン・ルピー', $resultPkr['currencyName']);
        $this->assertEquals('PKR', $resultPkr['currencyCode']);
        $this->assertEquals('0.680000', $resultPkr['tts']);
        $this->assertEquals('0.380000', $resultPkr['ttb']);

        $this->assertEquals('Kuwaiti Dinar', $resultKwd['currency']);
        $this->assertEquals('クウェート・ディナール', $resultKwd['currencyName']);
        $this->assertEquals('KWD', $resultKwd['currencyCode']);
        $this->assertEquals('498.930000', $resultKwd['tts']);
        $this->assertEquals('482.930000', $resultKwd['ttb']);

        $this->assertEquals('Qatari Rial', $resultQar['currency']);
        $this->assertEquals('カタール・リヤル', $resultQar['currencyName']);
        $this->assertEquals('QAR', $resultQar['currencyCode']);
        $this->assertEquals('41.790000', $resultQar['tts']);
        $this->assertEquals('40.430000', $resultQar['ttb']);

        $this->assertEquals('Indonesia Rupiah', $resultIdr['currency']);
        $this->assertEquals('インドネシア・ルピア', $resultIdr['currencyName']);
        $this->assertEquals('IDR', $resultIdr['currencyCode']);
        // per 100 unitのため表示されている数値から100を割った数値に変わっていることを確認する
        $this->assertEquals('0.010400', $resultIdr['tts']);
        $this->assertEquals('0.008000', $resultIdr['ttb']);

        $this->assertEquals('Mexican Peso', $resultMxn['currency']);
        $this->assertEquals('メキシコ・ペソ', $resultMxn['currencyName']);
        $this->assertEquals('MXN', $resultMxn['currencyCode']);
        $this->assertEquals('8.930000', $resultMxn['tts']);
        $this->assertEquals('6.930000', $resultMxn['ttb']);

        $this->assertEquals('Won', $resultKrw['currency']);
        $this->assertEquals('韓国ウォン', $resultKrw['currencyName']);
        $this->assertEquals('KRW', $resultKrw['currencyCode']);
        // per 100 unitのため表示されている数値から100を割った数値に変わっていることを確認する
        $this->assertEquals('0.109700', $resultKrw['tts']);
        $this->assertEquals('0.105700', $resultKrw['ttb']);

        $this->assertEquals('Philippine Peso', $resultPhp['currency']);
        $this->assertEquals('フィリピン・ペソ', $resultPhp['currencyName']);
        $this->assertEquals('PHP', $resultPhp['currencyCode']);
        $this->assertEquals('2.760000', $resultPhp['tts']);
        $this->assertEquals('2.480000', $resultPhp['ttb']);

        $this->assertEquals('Rand', $resultZar['currency']);
        $this->assertEquals('南アフリカ･ランド', $resultZar['currencyName']);
        $this->assertEquals('ZAR', $resultZar['currencyCode']);
        $this->assertEquals('9.810000', $resultZar['tts']);
        $this->assertEquals('6.810000', $resultZar['ttb']);

        $this->assertEquals('Czech Koruna', $resultCzk['currency']);
        $this->assertEquals('チェコ・コルナ', $resultCzk['currencyName']);
        $this->assertEquals('CZK', $resultCzk['currencyCode']);
        $this->assertEquals('7.060000', $resultCzk['tts']);
        $this->assertEquals('6.820000', $resultCzk['ttb']);

        $this->assertEquals('Russian Ruble', $resultRub['currency']);
        $this->assertEquals('ロシア・ルーブル', $resultRub['currencyName']);
        $this->assertEquals('RUB', $resultRub['currencyCode']);
        $this->assertEquals('2.100000', $resultRub['tts']);
        $this->assertEquals('1.600000', $resultRub['ttb']);

        $this->assertEquals('Hungarian Forint', $resultHuf['currency']);
        $this->assertEquals('ハンガリー・フォリント', $resultHuf['currencyName']);
        $this->assertEquals('HUF', $resultHuf['currencyCode']);
        $this->assertEquals('0.450000', $resultHuf['tts']);
        $this->assertEquals('0.410000', $resultHuf['ttb']);

        $this->assertEquals('Polish Zloty', $resultPln['currency']);
        $this->assertEquals('ポーランド・ズロチ', $resultPln['currencyName']);
        $this->assertEquals('PLN', $resultPln['currencyCode']);
        $this->assertEquals('41.160000', $resultPln['tts']);
        $this->assertEquals('38.760000', $resultPln['ttb']);

        $this->assertEquals('Turkish Lira', $resultTry['currency']);
        $this->assertEquals('トルコ・リラ', $resultTry['currencyName']);
        $this->assertEquals('TRY', $resultTry['currencyCode']);
        $this->assertEquals('6.180000', $resultTry['tts']);
        $this->assertEquals('1.180000', $resultTry['ttb']);
    }

    #[Test]
    #[DataProvider('parseTestData')]
    public function parse_指定した年月の情報がなかった(int $year, int $month, string $yearMonth): void
    {
        // setup
        $htmlCode = $this->getContent();
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['makeQueryParameter', 'getContent']);
        $mock->method('makeQueryParameter')->with($year, $month)->willReturn($yearMonth);
        $mock->method('getContent')->with($year, $month)->willReturn($htmlCode);

        // Exercise
        $resultCollection = $mock->parse($year, $month, false);

        // Verify
        $this->assertEquals(0, $resultCollection->count());
    }

    /**
     * @return array[]
     */
    public static function parseTestData(): array
    {
        return [
            '年が異なる' => [2022, 12, '2312'],
            '月が異なる' => [2023, 1, '2301'],
            '年も月も異なる' => [2024, 1, '2401'],
        ];
    }

    #[Test]
    public function parse_htmlCodeがfalse()
    {
        // setup
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['getContent']);
        $mock->method('getContent')->with(2023, 12)->willReturn(false);

        // Exercise
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('外貨為替収集情報取得:htmlCodeがfalse');
        $mock->parse(2023, 12, false);
    }

    #[Test]
    public function parse_h2テキストの解析でエラー(): void
    {
        // setup
        $htmlCode = <<<HTML
<!DOCTYPE html>
<html>
	<p>2023年12月末日および月中平均相場</p>
</html>
HTML;
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['getContent']);
        $mock->method('getContent')->with(2023, 12)->willReturn($htmlCode);

        // Exercise
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('外貨為替収集情報取得:h2テキストの解析でエラー');
        $mock->parse(2023, 12, false);
    }

    #[Test]
    public function parse_tableデータの解析でエラー(): void
    {
        // setup
        $htmlCode = <<<HTML
<!DOCTYPE html>
<html>
	<h2>2023年12月末日および月中平均相場</h2>
	<table>
	</table>
</html>
HTML;
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['getContent']);
        $mock->method('getContent')->with(2023, 12)->willReturn($htmlCode);

        // Exercise
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('外貨為替収集情報取得:tableデータの解析でエラー');
        $mock->parse(2023, 12, false);
    }

    #[Test]
    public function parse_tableデータの解析結果がnull(): void
    {
        // setup
        $htmlCode = $this->getContent();
        $xpath = $this->callMethod(
            $this->foreignCurrencyRateScrape,
            'getDOMXPath',
            [$htmlCode]
        );

        // ForeignCurrencyRateScrapeクラスのモック作成
        $foreignCurrencyRateScrapeMock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['getContent', 'getDOMXPath']);
        // DOMXPathとDOMNodeListのモック作成
        // ここで $tableData->item(0) null である定義をしている
        $xpathMock = $this->createPartialMock(\DOMXPath::class, ['query']);
        $tableDataMock = $this->createPartialMock(\DOMNodeList::class, ['count','item']);
        $tableDataMock->method('count')->willReturn(1);
        $tableDataMock->method('item')->with(0)->willReturn(null);

        // メソッドの複数呼び出し用にwillReturnCallbackを使用
        $xpathMock->method('query')
            ->willReturnCallback(function ($xpathExpression) use ($xpath, $tableDataMock) {
                $res = null;
                switch ($xpathExpression) {
                    case '//h2':
                        // h2テキスト解析用に正しいパラメータを返す
                        $res = $xpath->query('//h2');
                        break;
                    case '//h2/following-sibling::table[@class="data-table7"][1]':
                        // <h2>直下のclass="data-table7"のtable検索用にモックパラメータを返す
                        $res = $tableDataMock;
                        break;
                }
                return $res;
            });

        $foreignCurrencyRateScrapeMock->method('getContent')->with(2023, 12)->willReturn($htmlCode);
        $foreignCurrencyRateScrapeMock->method('getDOMXPath')->with($htmlCode)->willReturn($xpathMock);

        // Exercise
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('外貨為替収集情報取得:tableデータの解析結果がnull');
        $foreignCurrencyRateScrapeMock->parse(2023, 12, false);
    }

    #[Test]
    public function parse_tableデータの解析結果が想定と異なる(): void
    {
        // setup
        $htmlCode = <<<HTML
<!DOCTYPE html>
<html>
	<h2>2023年12月末日および月中平均相場</h2>
	<table class="data-table7">
		<tbody>
			
			<tr>
				<th>Currency</th>
				<th>通貨名</th>
				<th>略称Code</th>
				<th>通貨単位</th>
				<th>月末TTS</th>
				<th>月末TTB</th>
				<th>月末平均TTS</th>
				<th>月末平均TTB</th>
				<th>＊</th>
			</tr>
			
			<tr>
				<td>US Dollar</td>
				<td>米ドル</td>
				<td>USD</td>
				<td>1unit</td>
				<td>142.83</td>
				<td>140.83</td>
				<td>142.83</td>
				<td>145.13</td>
				<td>143.13</td>
				<td>ttm</td>
			</tr>
			
		</tbody>
	</table>
</html>
HTML;
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['getContent']);
        $mock->method('getContent')->with(2023, 12)->willReturn($htmlCode);

        // Exercise
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('外貨為替収集情報取得:tableデータの解析結果が想定と異なる');
        $mock->parse(2023, 12, false);
    }

    #[Test]
    public function parse_ttsが数字に変換できなかった(): void
    {
        // setup
        $htmlCode = <<<HTML
<!DOCTYPE html>
<html>
	<h2>2023年12月末日および月中平均相場</h2>
	<table class="data-table7">
		<tbody>
			
			<tr>
				<th>Currency</th>
				<th>通貨名</th>
				<th>略称Code</th>
				<th>通貨単位</th>
				<th>月末TTS</th>
				<th>月末TTB</th>
				<th>月末平均TTS</th>
				<th>月末平均TTB</th>
				<th>＊</th>
			</tr>
			
			<tr>
				<td>US Dollar</td>
				<td>米ドル</td>
				<td>USD</td>
				<td>1unit</td>
				<td>aaaaa</td>
				<td>140.83</td>
				<td>145.13</td>
				<td>143.13</td>
				<td></td>
			</tr>
			
		</tbody>
	</table>
</html>
HTML;
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['getContent']);
        $mock->method('getContent')->with(2023, 12)->willReturn($htmlCode);

        // Exercise
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('外貨為替収集情報取得:ttsが数字に変換できなかった');
        $mock->parse(2023, 12, false);
    }

    #[Test]
    public function parse_ttbが数字に変換できなかった(): void
    {
        // setup
        $htmlCode = <<<HTML
<!DOCTYPE html>
<html>
	<h2>2023年12月末日および月中平均相場</h2>
	<table class="data-table7">
		<tbody>
			
			<tr>
				<th>Currency</th>
				<th>通貨名</th>
				<th>略称Code</th>
				<th>通貨単位</th>
				<th>月末TTS</th>
				<th>月末TTB</th>
				<th>月末平均TTS</th>
				<th>月末平均TTB</th>
				<th>＊</th>
			</tr>
			
			<tr>
				<td>US Dollar</td>
				<td>米ドル</td>
				<td>USD</td>
				<td>1unit</td>
				<td>142.83</td>
				<td>aaaaa</td>
				<td>145.13</td>
				<td>143.13</td>
				<td></td>
			</tr>
			
		</tbody>
	</table>
</html>
HTML;
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['getContent']);
        $mock->method('getContent')->with(2023, 12)->willReturn($htmlCode);

        // Exercise
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('外貨為替収集情報取得:ttbが数字に変換できなかった');
        $mock->parse(2023, 12, false);
    }

    // NOTE: このケースは対象HPからダウンロードしてテストする関数です
    // Github Actionなどで生データがダウンロードされないよう原則スキップしています
    // 自身のローカルで動作確認などをする場合に使ってください
    #[Test]
    public function parseLocalReferenceExchangeRateByExcel_正常実行_サイトからダウンロード(): void
    {
        $this->markTestSkipped('生データをダウンロードするテストのため、GithubActionで実行されないようスキップします');
        // setup
        $year = 2024;
        $month = 8;

        // Exercise
        $result = $this->foreignCurrencyRateScrape->parseLocalReferenceExchangeRateByExcel($year, $month);

        // Verify
        $expectedTwdTts = '4.502476';
        $expectedTwdTtb = '4.585053';
        $expectedMydTts = '32.959789';
        $expectedMydTtb = '34.317090';

        $this->assertCount(2, $result);
        foreach ($result as $actual) {
            if ($actual['currencyCode'] === "TWD") {
                $this->assertEquals($expectedTwdTts, $actual['tts']);
                $this->assertEquals($expectedTwdTtb, $actual['ttb']);
            } elseif ($actual['currencyCode'] === "MYR") {
                $this->assertEquals($expectedMydTts, $actual['tts']);
                $this->assertEquals($expectedMydTtb, $actual['ttb']);
            } else {
                $this->fail();
            }
        }
    }

    #[Test]
    public function parseLocalReferenceExchangeRateByExcel_正常実行_月末が平日(): void
    {
        // setup
        $year = 2024;
        $month = 1;
        // テスト用ファイルの権限を変えておく
        chmod("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls", 0755);
        $htmlCode = <<<HTML
<!DOCTYPE html>
<html>
    <meta charset="utf-8">
	<h2>現地参考為替相場</h2>
	<ul>
			<li><a href="../../TestFile/sample_2024.xls">現地参考為替相場（マレーシア、中国、台湾）2024年</a></li>
			<li><a href="../../TestFile/sample_2023.xls">現地参考為替相場（マレーシア、中国、台湾）2023年</a></li>
	</ul>
</html>
HTML;
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['getContent', 'convertToUri']);
        $mock->method('getContent')->with($year, $month, "https://www.murc-kawasesouba.jp/fx/ref_rate.html", false)->willReturn($htmlCode);
        $mock->method('convertToUri')->with("../../TestFile/sample_2024.xls", "https://www.murc-kawasesouba.jp/fx/ref_rate.html")->willReturn("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls");

        // Exercise
        $result = $mock->parseLocalReferenceExchangeRateByExcel($year, $month);

        // Verify
        $expectedTwdTts = '4.690432';
        $expectedTwdTtb = '4.780115';
        $expectedMydTts = '30.656039';
        $expectedMydTtb = '31.826862';

        $this->assertCount(2, $result);
        foreach ($result as $actual) {
            if ($actual['currencyCode'] === "TWD") {
                $this->assertEquals($expectedTwdTts, $actual['tts']);
                $this->assertEquals($expectedTwdTtb, $actual['ttb']);
            } elseif ($actual['currencyCode'] === "MYR") {
                $this->assertEquals($expectedMydTts, $actual['tts']);
                $this->assertEquals($expectedMydTtb, $actual['ttb']);
            } else {
                $this->fail();
            }
        }
    }

    #[Test]
    public function parseLocalReferenceExchangeRateByExcel_正常実行_月末が早い28日(): void
    {
        // setup
        $year = 2024;
        $month = 2;
        // テスト用ファイルの権限を変えておく
        chmod("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls", 0755);
        $htmlCode = <<<HTML
<!DOCTYPE html>
<html>
    <meta charset="utf-8">
	<h2>現地参考為替相場</h2>
	<ul>
			<li><a href="../../TestFile/sample_2024.xls">現地参考為替相場（マレーシア、中国、台湾）2024年</a></li>
			<li><a href="../../TestFile/sample_2023.xls">現地参考為替相場（マレーシア、中国、台湾）2023年</a></li>
	</ul>
</html>
HTML;
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['getContent', 'convertToUri']);
        $mock->method('getContent')->with($year, $month, "https://www.murc-kawasesouba.jp/fx/ref_rate.html", false)->willReturn($htmlCode);
        $mock->method('convertToUri')->with("../../TestFile/sample_2024.xls", "https://www.murc-kawasesouba.jp/fx/ref_rate.html")->willReturn("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls");

        // Exercise
        $result = $mock->parseLocalReferenceExchangeRateByExcel($year, $month);

        // Verify
        $expectedTwdTts = '4.697041';
        $expectedTwdTtb = '4.786979';
        $expectedMydTts = '30.703101';
        $expectedMydTtb = '31.877590';

        $this->assertCount(2, $result);
        foreach ($result as $actual) {
            if ($actual['currencyCode'] === "TWD") {
                $this->assertEquals($expectedTwdTts, $actual['tts']);
                $this->assertEquals($expectedTwdTtb, $actual['ttb']);
            } elseif ($actual['currencyCode'] === "MYR") {
                $this->assertEquals($expectedMydTts, $actual['tts']);
                $this->assertEquals($expectedMydTtb, $actual['ttb']);
            } else {
                $this->fail();
            }
        }
    }

    #[Test]
    public function parseLocalReferenceExchangeRateByExcel_正常実行_月末が土日(): void
    {
        // setup
        $year = 2024;
        $month = 3;
        // テスト用ファイルの権限を変えておく
        chmod("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls", 0755);
        $htmlCode = <<<HTML
<!DOCTYPE html>
<html>
    <meta charset="utf-8">
	<h2>現地参考為替相場</h2>
	<ul>
			<li><a href="../../TestFile/sample_2024.xls">現地参考為替相場（マレーシア、中国、台湾）2024年</a></li>
			<li><a href="../../TestFile/sample_2023.xls">現地参考為替相場（マレーシア、中国、台湾）2023年</a></li>
	</ul>
</html>
HTML;
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['getContent', 'convertToUri']);
        $mock->method('getContent')->with($year, $month, "https://www.murc-kawasesouba.jp/fx/ref_rate.html", false)->willReturn($htmlCode);
        $mock->method('convertToUri')->with("../../TestFile/sample_2024.xls", "https://www.murc-kawasesouba.jp/fx/ref_rate.html")->willReturn("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls");

        // Exercise
        $result = $mock->parseLocalReferenceExchangeRateByExcel($year, $month);

        // Verify
        $expectedTwdTts = '4.692633';
        $expectedTwdTtb = '4.782401';
        $expectedMydTts = '31.377471';
        $expectedMydTtb = '32.605152';

        $this->assertCount(2, $result);
        foreach ($result as $actual) {
            if ($actual['currencyCode'] === "TWD") {
                $this->assertEquals($expectedTwdTts, $actual['tts']);
                $this->assertEquals($expectedTwdTtb, $actual['ttb']);
            } elseif ($actual['currencyCode'] === "MYR") {
                $this->assertEquals($expectedMydTts, $actual['tts']);
                $this->assertEquals($expectedMydTtb, $actual['ttb']);
            } else {
                $this->fail();
            }
        }
    }

    #[Test]
    public function parseLocalReferenceExchangeRateByExcel_正常実行_月末が祝日_TWDが月末祝日_MYRは30日祝日(): void
    {
        // setup
        $year = 2024;
        $month = 4;
        // テスト用ファイルの権限を変えておく
        chmod("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls", 0755);
        $htmlCode = <<<HTML
<!DOCTYPE html>
<html>
    <meta charset="utf-8">
	<h2>現地参考為替相場</h2>
	<ul>
			<li><a href="../../TestFile/sample_2024.xls">現地参考為替相場（マレーシア、中国、台湾）2024年</a></li>
			<li><a href="../../TestFile/sample_2023.xls">現地参考為替相場（マレーシア、中国、台湾）2023年</a></li>
	</ul>
</html>
HTML;
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['getContent', 'convertToUri']);
        $mock->method('getContent')->with($year, $month, "https://www.murc-kawasesouba.jp/fx/ref_rate.html", false)->willReturn($htmlCode);
        $mock->method('convertToUri')->with("../../TestFile/sample_2024.xls", "https://www.murc-kawasesouba.jp/fx/ref_rate.html")->willReturn("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls");

        // Exercise
        $result = $mock->parseLocalReferenceExchangeRateByExcel($year, $month);

        // Verify
        $expectedTwdTts = '4.688233';
        $expectedTwdTtb = '4.777831';
        $expectedMydTts = '30.656039';
        $expectedMydTtb = '31.826862';

        $this->assertCount(2, $result);
        foreach ($result as $actual) {
            if ($actual['currencyCode'] === "TWD") {
                $this->assertEquals($expectedTwdTts, $actual['tts']);
                $this->assertEquals($expectedTwdTtb, $actual['ttb']);
            } elseif ($actual['currencyCode'] === "MYR") {
                $this->assertEquals($expectedMydTts, $actual['tts']);
                $this->assertEquals($expectedMydTtb, $actual['ttb']);
            } else {
                $this->fail();
            }
        }
    }

    #[Test]
    public function parseLocalReferenceExchangeRateByExcel_正常実行_月末が祝日_TWDは30日祝日_MYRが月末祝日(): void
    {
        // setup
        $year = 2024;
        $month = 5;
        // テスト用ファイルの権限を変えておく
        chmod("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls", 0755);
        $htmlCode = <<<HTML
<!DOCTYPE html>
<html>
    <meta charset="utf-8">
	<h2>現地参考為替相場</h2>
	<ul>
			<li><a href="../../TestFile/sample_2024.xls">現地参考為替相場（マレーシア、中国、台湾）2024年</a></li>
			<li><a href="../../TestFile/sample_2023.xls">現地参考為替相場（マレーシア、中国、台湾）2023年</a></li>
	</ul>
</html>
HTML;
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['getContent', 'convertToUri']);
        $mock->method('getContent')->with($year, $month, "https://www.murc-kawasesouba.jp/fx/ref_rate.html", false)->willReturn($htmlCode);
        $mock->method('convertToUri')->with("../../TestFile/sample_2024.xls", "https://www.murc-kawasesouba.jp/fx/ref_rate.html")->willReturn("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls");

        // Exercise
        $result = $mock->parseLocalReferenceExchangeRateByExcel($year, $month);

        // Verify
        $expectedTwdTts = '4.688233';
        $expectedTwdTtb = '4.777831';
        $expectedMydTts = '30.656039';
        $expectedMydTtb = '31.826862';

        $this->assertCount(2, $result);
        foreach ($result as $actual) {
            if ($actual['currencyCode'] === "TWD") {
                $this->assertEquals($expectedTwdTts, $actual['tts']);
                $this->assertEquals($expectedTwdTtb, $actual['ttb']);
            } elseif ($actual['currencyCode'] === "MYR") {
                $this->assertEquals($expectedMydTts, $actual['tts']);
                $this->assertEquals($expectedMydTtb, $actual['ttb']);
            } else {
                $this->fail();
            }
        }
    }

    #[Test]
    public function parseLocalReferenceExchangeRateByExcel_正常実行_月末が祝日_TWDだけ月末祝日(): void
    {
        // setup
        $year = 2024;
        $month = 6;
        // テスト用ファイルの権限を変えておく
        chmod("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls", 0755);
        $htmlCode = <<<HTML
<!DOCTYPE html>
<html>
    <meta charset="utf-8">
	<h2>現地参考為替相場</h2>
	<ul>
			<li><a href="../../TestFile/sample_2024.xls">現地参考為替相場（マレーシア、中国、台湾）2024年</a></li>
			<li><a href="../../TestFile/sample_2023.xls">現地参考為替相場（マレーシア、中国、台湾）2023年</a></li>
	</ul>
</html>
HTML;
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['getContent', 'convertToUri']);
        $mock->method('getContent')->with($year, $month, "https://www.murc-kawasesouba.jp/fx/ref_rate.html", false)->willReturn($htmlCode);
        $mock->method('convertToUri')->with("../../TestFile/sample_2024.xls", "https://www.murc-kawasesouba.jp/fx/ref_rate.html")->willReturn("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls");

        // Exercise
        $result = $mock->parseLocalReferenceExchangeRateByExcel($year, $month);

        // Verify
        $expectedTwdTts = '4.688233';
        $expectedTwdTtb = '4.777831';
        $expectedMydTts = '30.778701';
        $expectedMydTtb = '31.959092';

        $this->assertCount(2, $result);
        foreach ($result as $actual) {
            if ($actual['currencyCode'] === "TWD") {
                $this->assertEquals($expectedTwdTts, $actual['tts']);
                $this->assertEquals($expectedTwdTtb, $actual['ttb']);
            } elseif ($actual['currencyCode'] === "MYR") {
                $this->assertEquals($expectedMydTts, $actual['tts']);
                $this->assertEquals($expectedMydTtb, $actual['ttb']);
            } else {
                $this->fail();
            }
        }
    }

    #[Test]
    public function parseLocalReferenceExchangeRateByExcel_正常実行_月末が祝日_MYRだけ月末祝日(): void
    {
        // setup
        $year = 2024;
        $month = 7;
        // テスト用ファイルの権限を変えておく
        chmod("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls", 0755);
        $htmlCode = <<<HTML
<!DOCTYPE html>
<html>
    <meta charset="utf-8">
	<h2>現地参考為替相場</h2>
	<ul>
			<li><a href="../../TestFile/sample_2024.xls">現地参考為替相場（マレーシア、中国、台湾）2024年</a></li>
			<li><a href="../../TestFile/sample_2023.xls">現地参考為替相場（マレーシア、中国、台湾）2023年</a></li>
	</ul>
</html>
HTML;
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['getContent', 'convertToUri']);
        $mock->method('getContent')->with($year, $month, "https://www.murc-kawasesouba.jp/fx/ref_rate.html", false)->willReturn($htmlCode);
        $mock->method('convertToUri')->with("../../TestFile/sample_2024.xls", "https://www.murc-kawasesouba.jp/fx/ref_rate.html")->willReturn("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls");

        // Exercise
        $result = $mock->parseLocalReferenceExchangeRateByExcel($year, $month);

        // Verify
        $expectedTwdTts = '4.681648';
        $expectedTwdTtb = '4.770992';
        $expectedMydTts = '30.656039';
        $expectedMydTtb = '31.826862';

        $this->assertCount(2, $result);
        foreach ($result as $actual) {
            if ($actual['currencyCode'] === "TWD") {
                $this->assertEquals($expectedTwdTts, $actual['tts']);
                $this->assertEquals($expectedTwdTtb, $actual['ttb']);
            } elseif ($actual['currencyCode'] === "MYR") {
                $this->assertEquals($expectedMydTts, $actual['tts']);
                $this->assertEquals($expectedMydTtb, $actual['ttb']);
            } else {
                $this->fail();
            }
        }
    }

    #[Test]
    public function parseLocalReferenceExchangeRateByExcel_存在しない年(): void
    {
        // setup
        $year = 1990;
        $month = 7;
        // テスト用ファイルの権限を変えておく
        chmod("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls", 0755);
        $htmlCode = <<<HTML
<!DOCTYPE html>
<html>
    <meta charset="utf-8">
	<h2>現地参考為替相場</h2>
	<ul>
			<li><a href="../../TestFile/sample_2024.xls">現地参考為替相場（マレーシア、中国、台湾）2024年</a></li>
			<li><a href="../../TestFile/sample_2023.xls">現地参考為替相場（マレーシア、中国、台湾）2023年</a></li>
	</ul>
</html>
HTML;
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['getContent', 'convertToUri']);
        $mock->method('getContent')->with($year, $month, "https://www.murc-kawasesouba.jp/fx/ref_rate.html", false)->willReturn($htmlCode);
        $mock->method('convertToUri')->with("../../TestFile/sample_2024.xls", "https://www.murc-kawasesouba.jp/fx/ref_rate.html")->willReturn("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls");


        // Exercise
        // Verify
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('外貨為替収集情報取得:fileUrlがない');
        $mock->parseLocalReferenceExchangeRateByExcel($year, $month);
    }

    #[Test]
    public function getExcelDataFromLocalReferenceExchangeRate_存在しない月(): void
    {
        // setup
        $year = 2024;
        $month = 10;
        $mock = $this->createPartialMock(ForeignCurrencyRateScrape::class, ['convertToUri']);
        $mock->method('convertToUri')->with("../../TestFile/sample_2024.xls", "https://www.murc-kawasesouba.jp/fx/ref_rate.html")->willReturn("../local/lib/laravel-wp-currency/tests/Unit/Domain/Currency/TestFile/sample_2024.xls");

        // Exercise
        // Verify
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('外貨為替収集情報取得:対象月のシート取得失敗');
        $result = $this->callMethod(
            $mock,
            'getExcelDataFromLocalReferenceExchangeRate',
            [
                "../../TestFile/sample_2024.xls",
                Storage::disk('local'),
                $month,
            ]
        );
    }

    #[Test]
    public function getTtbAndTtsDataFromExcelData_データ変換テスト(): void
    {
        // setup
        $data = [
            [
                0 => 31,
                6 => '1.1000',
                7 => '1.2000',
                10 => '2.1000',
                11 => '2.2000',
            ],
        ];
        // Exercise
        $result = $this->callMethod(
            $this->foreignCurrencyRateScrape,
            'getTtbAndTtsDataFromExcelData',
            [$data]
        );
        // Verify
        $this->assertCount(2, $result);
        foreach ($result as $actual) {
            if ($actual['currencyCode'] === "TWD") {
                $this->assertEquals('0.909091', $actual['tts']);
                $this->assertEquals('0.833333', $actual['ttb']);
            } elseif ($actual['currencyCode'] === "MYR") {
                $this->assertEquals('47.619048', $actual['tts']);
                $this->assertEquals('45.454545', $actual['ttb']);
            } else {
                $this->fail();
            }
        }
    }

    public static function param_convertToUri_URL変換()
    {
        return [
            '絶対パス' => ['https://example.jp/sample.xls', false],
            '相対パス_先頭がダブルスラッシュ' => ['//example.jp/sample.xls', false],
            '相対パス_ディレクトリ指定' => ['/rate/sample.xls', true],
            '相対パス_カレントディレクトリ指定' => ['./sample.xls', false],
            '相対パス_ファイル名のみ' => ['sample.xls', false],
            '相対パス_ディレクトリ移動' => ['../../sample.xls', false],
            '相対パス_ディレクトリ移動_ディレクトリ指定' => ['../../rate/sample.xls', true],
        ];
    }

    #[Test]
    #[DataProvider('param_convertToUri_URL変換')]
    public function convertToUri_URL変換(string $url, bool $hasDirectory): void
    {
        // Setup
        $base = 'https://example.jp/rate.html';
        // Exercise
        $result = $this->callMethod(
            $this->foreignCurrencyRateScrape,
            'convertToUri',
            [$url, $base]
        );

        // Verify
        if ($hasDirectory) {
            $this->assertEquals("https://example.jp/rate/sample.xls", $result);
        } else {
            $this->assertEquals("https://example.jp/sample.xls", $result);
        }
    }

    /**
     * スクレイピング用のhtmlコード
     * 下記スクレイピング対象のページからコードをhtmlコードをDLして配置
     * https://www.murc-kawasesouba.jp/fx/monthend/index.php?id=2312
     *
     * @return string
     */
    private function getContent(): string
    {
        return file_get_contents(self::TEST_URL);
    }

    private function getTodayContent(): string
    {
        return file_get_contents(self::TEST_TODAY_URL);
    }
}
