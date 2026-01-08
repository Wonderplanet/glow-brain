<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Repositories\LogStoreRepository;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

class LogStoreRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private LogStoreRepository $logStoreRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->logStoreRepository = $this->app->make(LogStoreRepository::class);
    }

    public function tearDown(): void
    {
        $this->setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function insertStoreLog_ログが登録されていること()
    {
        // Exercise
        $id = $this->logStoreRepository->insertStoreLog(
            '1',
            'device1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            20,
            1,
            'platform_product1',
            'mst_store_product1',
            'product_sub1',
            'product sub name',
            'raw_receipt1',
            '$1.00',
            'USD',
            'receipt_unique_id1',
            'bundle_id1',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type1',
                'trigger_id1',
                'trigger_name',
                'trigger_detail1'
            )
        );

        // Verify
        $logStore = $this->logStoreRepository->findById($id);
        $this->assertEquals('1', $logStore->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logStore->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logStore->billing_platform);
        $this->assertEquals('device1', $logStore->device_id);
        $this->assertEquals(20, $logStore->age);
        $this->assertEquals(1, $logStore->seq_no);
        $this->assertEquals('platform_product1', $logStore->platform_product_id);
        $this->assertEquals('mst_store_product1', $logStore->mst_store_product_id);
        $this->assertEquals('product_sub1', $logStore->product_sub_id);
        $this->assertEquals('product sub name', $logStore->product_sub_name);
        $this->assertEquals('raw_receipt1', $logStore->raw_receipt);
        $this->assertEquals('$1.00', $logStore->raw_price_string);
        $this->assertEquals('USD', $logStore->currency_code);
        $this->assertEquals('receipt_unique_id1', $logStore->receipt_unique_id);
        $this->assertEquals('bundle_id1', $logStore->receipt_bundle_id);
        $this->assertEquals(100, $logStore->paid_amount);
        $this->assertEquals(200, $logStore->free_amount);
        $this->assertEquals('1.000000', $logStore->purchase_price);
        $this->assertEquals('0.01000000', $logStore->price_per_amount);
        $this->assertEquals(110, $logStore->vip_point);
        $this->assertEquals(0, $logStore->is_sandbox);
        $this->assertEquals('trigger_type1', $logStore->trigger_type);
        $this->assertEquals('trigger_id1', $logStore->trigger_id);
        $this->assertEquals('trigger_name', $logStore->trigger_name);
        $this->assertEquals('trigger_detail1', $logStore->trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logStore->request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logStore->request_id);
        $this->assertEquals(CurrencyCommon::getFrontRequestId(), $logStore->nginx_request_id);
    }

    #[Test]
    public function getTargetMonthCount_日本時間基準で件数が1件取得できる():void
    {
        // 日本時間の2023-11-28 00:00:00に作成(UTC 2023-11-27 15:00:00)
        $now = Carbon::parse('2023-11-27 15:00:00', 'UTC');
        $this->setTestNow($now);

        // setup
        $this->logStoreRepository->insertStoreLog(
            '1',
            'device1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            20,
            1,
            'platform_product1',
            'mst_store_product1',
            'product_sub1',
            'product sub name',
            'raw_receipt1',
            '￥1.00',
            'JPY',
            'receipt_unique_id1',
            'bundle_id1',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type1',
                'trigger_id1',
                'trigger_name',
                'trigger_detail1'
            )
        );

        // 日本時間の01-04 00:00:00に作成(UTCだと2024-01-03 15:00:00)
        $now = Carbon::parse('2024-01-03 15:00:00', 'UTC');
        $this->setTestNow($now);
        $this->logStoreRepository->insertStoreLog(
            '1',
            'device1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            20,
            1,
            'platform_product2',
            'mst_store_product2',
            'product_sub2',
            'product sub name',
            'raw_receipt2',
            '￥2.00',
            'JPY',
            'receipt_unique_id2',
            'bundle_id2',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type2',
                'trigger_id2',
                'trigger_name',
                'trigger_detail1'
            )
        );

        // Exercise
        // 対象のログは2023年12月
        $startAt = Carbon::parse('2023-11-28 00:00:00', 'Asia/Tokyo');
        $endAt = Carbon::parse('2024-01-03 23:59:59', 'Asia/Tokyo');
        $count = $this->logStoreRepository
            ->getTargetMonthCount(
                $startAt,
                $endAt,
                false,
            );

        // Verify
        //  1件だけ抽出されること
        $this->assertEquals(1, $count);
    }

    #[Test]
    public function getTargetMonthData_日本時間基準で抽出データが1件だけ取得できる(): void
    {
        // setup
        //  日本時間の2023-11-28 00:00:00に作成(UTC 2023-11-27 15:00:00)
        $now = Carbon::parse('2023-11-27 15:00:00', 'UTC');
        $this->setTestNow($now);
        $id1 = $this->logStoreRepository->insertStoreLog(
            '1',
            'device1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            20,
            1,
            'platform_product1',
            'mst_store_product1',
            'product_sub1',
            'product sub name',
            'raw_receipt1',
            '￥1.00',
            'JPY',
            'receipt_unique_id1',
            'bundle_id1',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type1',
                'trigger_id1',
                'trigger_name',
                'trigger_detail1'
            )
        );

        // 日本時間の01-04 00:00:00に作成(UTCだと2024-01-03 15:00:00)
        $now = Carbon::parse('2024-01-03 15:00:00', 'UTC');
        $this->setTestNow($now);
        $id2 = $this->logStoreRepository->insertStoreLog(
            '1',
            'device1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            20,
            1,
            'platform_product2',
            'mst_store_product2',
            'product_sub2',
            'product sub name',
            'raw_receipt2',
            '￥2.00',
            'JPY',
            'receipt_unique_id2',
            'bundle_id2',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type2',
                'trigger_id2',
                'trigger_name',
                'trigger_detail1'
            )
        );
        $currencyCodeAndTtmList = [
            [
                'currency_code' => 'USD',
                'ttm' => '149.580000'
            ],
            [
                'currency_code' => 'EUR',
                'ttm' => '157.120000'
            ],
            [
                'currency_code' => 'HKD',
                'ttm' => '18.150000'
            ]
        ];

        // Exercise
        // 対象のログは2023年12月
        $startAt = Carbon::parse('2023-11-28 00:00:00', 'Asia/Tokyo');
        $endAt = Carbon::parse('2024-01-03 23:59:59', 'Asia/Tokyo');
        $logStores = $this->logStoreRepository
            ->getTargetMonthData(
                $startAt,
                $endAt,
                false,
                $currencyCodeAndTtmList
            );

        // Verify
        // 生成したデータの作成日チェック
        // 日本時間が一致すること
        $logStore1 = $this->logStoreRepository->findById($id1);
        $this->assertEquals('2023-11-28 00:00:00', $logStore1->created_at->setTimezone('Asia/Tokyo')->format('Y-m-d H:i:s'));
        $logStore2 = $this->logStoreRepository->findById($id2);
        $this->assertEquals('2024-01-04 00:00:00', $logStore2->created_at->setTimezone('Asia/Tokyo')->format('Y-m-d H:i:s'));

        // 取得ログデータチェック
        // 1件だけ抽出されること
        $this->assertCount(1, $logStores);
        // 取得されたデータが想定されたものであること
        $logStoreRow = $logStores[0];
        $this->assertEquals('1', $logStoreRow['player_id']);
        $this->assertEquals('aapl', $logStoreRow['market']);
        $this->assertEquals('receipt_unique_id1', $logStoreRow['order_id']);
        $this->assertEquals('platform_product1', $logStoreRow['product_id']);
        $this->assertEquals('JPY', $logStoreRow['currency']);
        $this->assertEquals('1.000000', $logStoreRow['price']);
        $this->assertEquals('1', $logStoreRow['currency_rate']);
        $this->assertEquals('2023/11/28 00:00:00', $logStoreRow['formatted_created_at']);
    }

    #[Test]
    public function getTargetMonthData_curenncy_codeに合わせた通貨レートが取得できる(): void
    {
        // 日本時間の2023-11-28 00:00:00に作成(UTC 2023-11-27 15:00:00)
        $now = Carbon::parse('2023-11-27 15:00:00', 'UTC');
        $this->setTestNow($now);

        // setup
        $this->logStoreRepository->insertStoreLog(
            '1',
            'device1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            20,
            1,
            'platform_product1',
            'mst_store_product1',
            'product_sub1',
            'product sub name',
            'raw_receipt1',
            '￥1.00',
            'JPY',
            'receipt_unique_id1',
            'bundle_id1',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type1',
                'trigger_id1',
                'trigger_name',
                'trigger_detail1'
            )
        );
        $this->logStoreRepository->insertStoreLog(
            '2',
            'device1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            20,
            1,
            'platform_product1',
            'mst_store_product1',
            'product_sub1',
            'product sub name',
            'raw_receipt1',
            '1.00',
            'USD',
            'receipt_unique_id2',
            'bundle_id1',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type1',
                'trigger_id1',
                'trigger_name',
                'trigger_detail1'
            )
        );
        $this->logStoreRepository->insertStoreLog(
            '3',
            'device1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            20,
            1,
            'platform_product1',
            'mst_store_product1',
            'product_sub1',
            'product sub name',
            'raw_receipt1',
            '1.00',
            'HKD',
            'receipt_unique_id3',
            'bundle_id1',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type1',
                'trigger_id1',
                'trigger_name',
                'trigger_detail1'
            )
        );
        $this->logStoreRepository->insertStoreLog(
            '4',
            'device1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            20,
            1,
            'platform_product1',
            'mst_store_product1',
            'product_sub1',
            'product sub name',
            'raw_receipt1',
            '1.00',
            'EUR',
            'receipt_unique_id4',
            'bundle_id1',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type1',
                'trigger_id1',
                'trigger_name',
                'trigger_detail1'
            )
        );
        $this->logStoreRepository->insertStoreLog(
            '5',
            'device1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            20,
            1,
            'platform_product1',
            'mst_store_product1',
            'product_sub1',
            'product sub name',
            'raw_receipt1',
            '1.00',
            'NZD',
            'receipt_unique_id5',
            'bundle_id1',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type1',
                'trigger_id1',
                'trigger_name',
                'trigger_detail1'
            )
        );
        $currencyCodeAndTtmList = [
            [
                'currency_code' => 'USD',
                'ttm' => '149.580000'
            ],
            [
                'currency_code' => 'EUR',
                'ttm' => '157.120000'
            ],
            [
                'currency_code' => 'HKD',
                'ttm' => '18.150000'
            ]
        ];

        // Exercise
        $startAt = Carbon::parse('2023-11-28 00:00:00', 'Asia/Tokyo');
        $endAt = Carbon::parse('2024-01-03 23:59:59', 'Asia/Tokyo');
        $logStores = $this->logStoreRepository
            ->getTargetMonthData(
                $startAt,
                $endAt,
                false,
                $currencyCodeAndTtmList
            );

        // Verify
        // 取得ログデータチェック
        $this->assertCount(5, $logStores);
        // 取得されたデータが想定されたものであること
        // 通貨コードとcurrency_rateに焦点を絞っているチェック
        $logStoreRow1 = $logStores->first(fn ($row) => $row->toArray()['currency'] === 'JPY');
        $this->assertEquals('JPY', $logStoreRow1['currency']);
        $this->assertEquals('1', $logStoreRow1['currency_rate']);
        $logStoreRow2 = $logStores->first(fn ($row) => $row->toArray()['currency'] === 'USD');
        $this->assertEquals('USD', $logStoreRow2['currency']);
        $this->assertEquals('149.580000', $logStoreRow2['currency_rate']);
        $logStoreRow3 = $logStores->first(fn ($row) => $row->toArray()['currency'] === 'HKD');
        $this->assertEquals('HKD', $logStoreRow3['currency']);
        $this->assertEquals('18.150000', $logStoreRow3['currency_rate']);
        $logStoreRow4 = $logStores->first(fn ($row) => $row->toArray()['currency'] === 'EUR');
        $this->assertEquals('EUR', $logStoreRow4['currency']);
        $this->assertEquals('157.120000', $logStoreRow4['currency_rate']);
        $logStoreRow5 = $logStores->first(fn ($row) => $row->toArray()['currency'] === 'NZD');
        $this->assertEquals('NZD', $logStoreRow5['currency']);
        $this->assertEquals('', $logStoreRow5['currency_rate']);
    }

    #[Test]
    #[DataProvider('getTargetMonthDataWithDateFormatJstSandbox')]
    public function getTargetMonthData_sandbox条件チェック(
        bool $isIncludeSandbox,
        int $expectedRowCount
    ): void {
        // setup
        // 日本時間の2023-11-28 00:00:00に作成(UTC 2023-11-27 15:00:00)
        $now = Carbon::parse('2023-11-27 15:00:00', 'UTC');
        $this->setTestNow($now);
        // sandbox=false
        $this->logStoreRepository->insertStoreLog(
            '1',
            'device1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            20,
            1,
            'platform_product1',
            'mst_store_product1',
            'product_sub1',
            'product sub name',
            'raw_receipt1',
            '￥1.00',
            'JPY',
            'receipt_unique_id1',
            'bundle_id1',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type1',
                'trigger_id1',
                'trigger_name',
                'trigger_detail1'
            )
        );
        // 日本時間の2023-11-29 00:00:00に作成(UTC 2023-11-28 15:00:00)
        $now = Carbon::parse('2023-11-28 15:00:00', 'UTC');
        $this->setTestNow($now);
        // sandbox=true
        $this->logStoreRepository->insertStoreLog(
            '2',
            'device1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            20,
            1,
            'platform_product2',
            'mst_store_product2',
            'product_sub2',
            'product sub name',
            'raw_receipt1',
            '￥1.00',
            'JPY',
            'receipt_unique_id2',
            'bundle_id1',
            100,
            200,
            '2.000000',
            '0.01000000',
            110,
            true,
            new Trigger(
                'trigger_type1',
                'trigger_id1',
                'trigger_name',
                'trigger_detail1'
            )
        );

        // Exercise
        $startAt = Carbon::parse('2023-11-28 00:00:00', 'Asia/Tokyo');
        $endAt = Carbon::parse('2024-01-03 23:59:59', 'Asia/Tokyo');
        $logStores = $this->logStoreRepository
            ->getTargetMonthData(
                $startAt,
                $endAt,
                $isIncludeSandbox,
                []
            );

        // Verify
        // 取得ログデータチェック
        $this->assertCount($expectedRowCount, $logStores);

        $row1 = $logStores[0];
        $this->assertEquals('1', $row1['player_id']);
        $this->assertEquals('aapl', $row1['market']);
        $this->assertEquals('receipt_unique_id1', $row1['order_id']);
        $this->assertEquals('platform_product1', $row1['product_id']);
        $this->assertEquals('JPY', $row1['currency']);
        $this->assertEquals('1.000000', $row1['price']);
        $this->assertEquals('1', $row1['currency_rate']);
        $this->assertEquals('2023/11/28 00:00:00', $row1['formatted_created_at']);

        if ($isIncludeSandbox) {
            // sandboxデータチェック
            $row2 = $logStores[1];
            $this->assertEquals('2', $row2['player_id']);
            $this->assertEquals('goog', $row2['market']);
            $this->assertEquals('receipt_unique_id2', $row2['order_id']);
            $this->assertEquals('platform_product2', $row2['product_id']);
            $this->assertEquals('JPY', $row2['currency']);
            $this->assertEquals('2.000000', $row2['price']);
            $this->assertEquals('1', $row2['currency_rate']);
            $this->assertEquals('2023/11/29 00:00:00', $row2['formatted_created_at']);
        }
    }

    /**
     * @return array
     */
    public static function getTargetMonthDataWithDateFormatJstSandbox(): array
    {
        return [
            'サンドボックスデータを含めない' => [false, 1],
            'サンドボックスデータを含める' => [true, 2],
        ];
    }

    #[Test]
    public function getTargetMonthData_指定したlimitとoffsetが動作しているかチェック(): void
    {
        // setup
        // 日本時間の2023-11-28 00:00:00に作成(UTC 2023-11-27 15:00:00)
        $now = Carbon::parse('2023-11-27 15:00:00', 'UTC');
        $this->setTestNow($now);
        $this->logStoreRepository->insertStoreLog(
            '1',
            'device1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            20,
            1,
            'platform_product1',
            'mst_store_product1',
            'product_sub1',
            'product sub name',
            'raw_receipt1',
            '￥1.00',
            'JPY',
            'receipt_unique_id1',
            'bundle_id1',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type1',
                'trigger_id1',
                'trigger_name',
                'trigger_detail1'
            )
        );
        // 日本時間の2023-11-29 00:00:00に作成(UTC 2023-11-28 15:00:00)
        $now = Carbon::parse('2023-11-28 15:00:00', 'UTC');
        $this->setTestNow($now);
        $this->logStoreRepository->insertStoreLog(
            '2',
            'device2',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            20,
            1,
            'platform_product1',
            'mst_store_product1',
            'product_sub1',
            'product sub name',
            'raw_receipt1',
            '￥1.00',
            'JPY',
            'receipt_unique_id2',
            'bundle_id2',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type1',
                'trigger_id1',
                'trigger_name',
                'trigger_detail1'
            )
        );
        // 日本時間の2023-11-30 00:00:00に作成(UTC 2023-11-29 15:00:00)
        $now = Carbon::parse('2023-11-29 15:00:00', 'UTC');
        $this->setTestNow($now);
        $this->logStoreRepository->insertStoreLog(
            '3',
            'device3',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            20,
            1,
            'platform_product1',
            'mst_store_product1',
            'product_sub1',
            'product sub name',
            'raw_receipt1',
            '￥1.00',
            'JPY',
            'receipt_unique_id3',
            'bundle_id3',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type1',
                'trigger_id1',
                'trigger_name',
                'trigger_detail1'
            )
        );

        // Exercise
        $startAt = Carbon::parse('2023-11-28 00:00:00', 'Asia/Tokyo');
        $endAt = Carbon::parse('2024-01-03 23:59:59', 'Asia/Tokyo');
        // 1件ずつ取得しoffset=1(2回目)の実行結果を取得
        $logStores = $this->logStoreRepository
            ->getTargetMonthData(
                $startAt,
                $endAt,
                false,
                [],
                1,
                1
            );

        // Verify
        // 取得ログデータチェック
        $this->assertCount(1, $logStores);
        //  player_id=2のデータ取得できているかチェック
        $row1 = $logStores[0];
        $this->assertEquals('2', $row1['player_id']);
        $this->assertEquals('goog', $row1['market']);
        $this->assertEquals('receipt_unique_id2', $row1['order_id']);
        $this->assertEquals('platform_product1', $row1['product_id']);
        $this->assertEquals('JPY', $row1['currency']);
        $this->assertEquals('1.000000', $row1['price']);
        $this->assertEquals('1', $row1['currency_rate']);
        $this->assertEquals('2023/11/29 00:00:00', $row1['formatted_created_at']);
    }

    #[Test]
    #[DataProvider('makeCurrencyRateCaseQueryStrData')]
    public function makeCurrencyRateCaseQueryStr_case文生成(array $currencyCodeAndTtmList, string $expected): void
    {
        // Exercise
        $result = $this->callMethod(
            $this->logStoreRepository,
            'makeCurrencyRateCaseQueryStr',
            [$currencyCodeAndTtmList]
        );

        // Verify
        $this->assertEquals($expected, $result);
    }

    public static function makeCurrencyRateCaseQueryStrData(): array
    {
        $expected1 = 'CASE';
        $expected1 .= " WHEN currency_code = 'JPY' THEN '1'";
        $expected1 .= " WHEN currency_code = 'USD' THEN '149.580000'";
        $expected1 .= " WHEN currency_code = 'EUR' THEN '157.120000'";
        $expected1 .= " WHEN currency_code = 'HKD' THEN '18.150000'";
        $expected1 .= " ELSE ''";
        $expected1 .= " END AS currency_rate";

        $expected2 = 'CASE';
        $expected2 .= " WHEN currency_code = 'JPY' THEN '1'";
        $expected2 .= " ELSE ''";
        $expected2 .= " END AS currency_rate";

        return [
            '外貨為替レートデータがある' => [
                [
                    [
                        'currency_code' => 'USD',
                        'ttm' => '149.580000'
                    ],
                    [
                        'currency_code' => 'EUR',
                        'ttm' => '157.120000'
                    ],
                    [
                        'currency_code' => 'HKD',
                        'ttm' => '18.150000'
                    ]
                ],
                $expected1
            ],
            '外貨為替レートがない' => [
                [],
                $expected2
            ]
        ];
    }
}

