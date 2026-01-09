<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Entities\StoreReceipt;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Billing\Models\LogStore;
use WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory;
use WonderPlanet\Domain\Billing\Repositories\LogStoreRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreAllowanceRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreInfoRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Billing\Services\BillingAdminService;
use WonderPlanet\Domain\Billing\Services\BillingService;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;
use WonderPlanet\Domain\Currency\Entities\CollectPaidCurrencyAdminTrigger;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Models\AdmForeignCurrencyRate;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;
use WonderPlanet\Domain\Currency\Repositories\AdmForeignCurrencyRateRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencySummaryRepository;
use WonderPlanet\Domain\Currency\Services\CurrencyService;
use WonderPlanet\Tests\Traits\Domain\Currency\DataFixtureTrait;

class BillingAdminServiceTest extends TestCase
{
    use RefreshDatabase;
    use DataFixtureTrait;
    use FakeStoreReceiptTrait;

    private BillingAdminService $billingAdminService;
    private LogStoreRepository $logStoreRepository;
    private AdmForeignCurrencyRateRepository $admForeignCurrencyRateRepository;
    private BillingService $billingService;
    private UsrStoreAllowanceRepository $usrStoreAllowanceRepository;
    private CurrencyDelegator $currencyDelegator;
    private UsrCurrencyPaidRepository $usrCurrencyPaidRepository;
    private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository;
    private CurrencyService $currencyService;
    private UsrCurrencySummaryRepository $usrCurrencySummaryRepository;
    private LogCurrencyPaidRepository $logCurrencyPaidRepository;
    private UsrStoreInfoRepository $usrStoreInfoRepository;
    private UsrCurrencyFreeRepository $usrCurrencyFreeRepository;
    private LogCurrencyFreeRepository $logCurrencyFreeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->billingAdminService = $this->app->make(BillingAdminService::class);
        $this->logStoreRepository = $this->app->make(LogStoreRepository::class);
        $this->admForeignCurrencyRateRepository = $this->app->make(AdmForeignCurrencyRateRepository::class);
        $this->billingService = $this->app->make(BillingService::class);
        $this->usrStoreAllowanceRepository = $this->app->make(UsrStoreAllowanceRepository::class);
        $this->currencyDelegator = $this->app->make(CurrencyDelegator::class);
        $this->usrCurrencyPaidRepository = $this->app->make(UsrCurrencyPaidRepository::class);
        $this->usrStoreProductHistoryRepository = $this->app->make(UsrStoreProductHistoryRepository::class);
        $this->currencyService = $this->app->make(CurrencyService::class);
        $this->usrCurrencySummaryRepository = $this->app->make(UsrCurrencySummaryRepository::class);
        $this->logCurrencyPaidRepository = $this->app->make(LogCurrencyPaidRepository::class);
        $this->usrStoreInfoRepository = $this->app->make(UsrStoreInfoRepository::class);
        $this->usrCurrencyFreeRepository = $this->app->make(UsrCurrencyFreeRepository::class);
        $this->logCurrencyFreeRepository = $this->app->make(LogCurrencyFreeRepository::class);
    }

    public function tearDown(): void
    {
        $this->setTestNow();

        parent::tearDown();
    }

    #[Test]
    #[DataProvider('getYearMonthOptionsData')]
    public function getYearMonthOptions_対象期間までの年配列を取得(Carbon $now, array $expectedYears, array $expectedMonthsByYear): void
    {
        // setUp
        // 指定日時を現在日時として固定
        $this->setTestNow($now);
        LogStore::query()->insert([
            'id' => '1',
            'seq_no' => '1',
            'usr_user_id' => '1',
            'platform_product_id' => 'product-1',
            'mst_store_product_id' => "mst-product-1",
            'product_sub_id' => 'product-sub-id-1',
            'product_sub_name' => 'product-sub-name-',
            'raw_receipt' => 'test',
            'raw_price_string' => 'test',
            'currency_code' => 'JPY',
            'receipt_unique_id' => 'receipt_unique_id_',
            'receipt_bundle_id' => 'receipt_bundle_id_',
            'os_platform' => CurrencyConstants::OS_PLATFORM_IOS,
            'billing_platform' => CurrencyConstants::PLATFORM_APPSTORE,
            'device_id' => 'test',
            'age' => 20,
            'paid_amount' => '1',
            'free_amount' => 0,
            'purchase_price' => '100',
            'price_per_amount' => '100',
            'vip_point' => 110,
            'is_sandbox' => false,
            'trigger_type' => '',
            'trigger_id' => '',
            'trigger_name' => '',
            'trigger_detail' => '',
            'request_id' => 'request-id-1',
            'nginx_request_id' => 'nginx-request-id-1',
            'created_at' => '2023-04-01 12:00:00',
            'updated_at' => '2023-04-01 12:00:00',
        ]);

        // Exercise
        [$resultYears, $resultMonthsByYear] = $this->billingAdminService
            ->getYearMonthOptions();

        // Verify
        $this->assertSame($expectedYears, $resultYears);
        $this->assertSame($expectedMonthsByYear, $resultMonthsByYear);
    }
    public static function getYearMonthOptionsData(): array
    {
        return [
            '2023年〜2025年' => [
                Carbon::parse('2025-02-01 00:00:00'),
                [
                    '2023' => '2023',
                    '2024' => '2024',
                    '2025' => '2025',
                ],
                [
                    '2023' => array_combine(range(4, 12), range(4, 12)),
                    '2024' => array_combine(range(1, 12), range(1, 12)),
                    '2025' => [1 => 1],
                ]
            ],
            '2023年のみ' => [
                Carbon::parse('2024-01-01 00:00:00'),
                [
                    '2023' => '2023',
                ],
                [
                    '2023' => array_combine(range(4, 12), range(4, 12)),
                ]
            ],
            'ログレコードの一番古いデータと現在年が一緒(１ヶ月経過していない)' => [
                Carbon::parse('2023-01-31 23:59:59'),
                [],
                []
            ]
        ];
    }

    #[Test]
    public function getYearMonthOptions_ログレコードが存在しない(): void
    {
        // Exercise
        [$resultYears, $resultMonthsByYear] = $this->billingAdminService
            ->getYearMonthOptions();

        // Verify
        $this->assertSame([], $resultYears);
        $this->assertSame([], $resultMonthsByYear);
    }

    #[Test]
    #[DataProvider('getBillingLogReportData')]
    public function getBillingLogReport_指定データを取得(
        bool $isIncludeSandbox,
        int $expectedRowCount,
        string $expectedFileName
    ): void {
        // Setup
        $year = '2023';
        $month = '12';
        $limit = 100000;
        $this->makeBillingLogReportRecordWithinTerm();
        $this->makeBillingLogReportRecordOutOfTerm();
        $this->makeAdmForeignCurrencyRate();

        // Exercise
        $billingLogReport = $this->billingAdminService->getBillingLogReport($year, $month, $isIncludeSandbox, $limit);
        $dataCollection = $this->getProperty($billingLogReport, 'data');

        // Verify
        //  ファイルに書き込むレコード数が想定通り
        $this->assertEquals($expectedRowCount, $dataCollection->count());

        //  取得データが正しいこと
        $logStore1 = $dataCollection->first(fn ($row) => $row['player_id'] === '2');
        $this->assertEquals('2', $logStore1['player_id']);
        $this->assertEquals('aapl', $logStore1['market']);
        $this->assertEquals('receipt_unique_id2', $logStore1['order_id']);
        $this->assertEquals('platform_product2', $logStore1['product_id']);
        $this->assertEquals('JPY', $logStore1['currency']);
        $this->assertEquals('1.000000', $logStore1['price']);
        $this->assertEquals('1', $logStore1['currency_rate']);
        $this->assertEquals('2023/11/28 00:00:00', $logStore1['formatted_created_at']);

        $logStore2 = $dataCollection->first(fn ($row) => $row['player_id'] === '3');
        $this->assertEquals('3', $logStore2['player_id']);
        $this->assertEquals('goog', $logStore2['market']);
        $this->assertEquals('receipt_unique_id3', $logStore2['order_id']);
        $this->assertEquals('platform_product3', $logStore2['product_id']);
        $this->assertEquals('USD', $logStore2['currency']);
        $this->assertEquals('1.000000', $logStore2['price']);
        $this->assertEquals('149.580000', $logStore2['currency_rate']);
        $this->assertEquals('2024/01/03 23:59:59', $logStore2['formatted_created_at']);

        if ($isIncludeSandbox) {
            $logStore3 = $dataCollection->first(fn ($row) => $row['player_id'] === '4');
            $this->assertEquals('4', $logStore3['player_id']);
            $this->assertEquals('goog', $logStore3['market']);
            $this->assertEquals('receipt_unique_id4', $logStore3['order_id']);
            $this->assertEquals('platform_product4', $logStore3['product_id']);
            $this->assertEquals('USD', $logStore3['currency']);
            $this->assertEquals('1.000000', $logStore3['price']);
            $this->assertEquals('149.580000', $logStore3['currency_rate']);
            $this->assertEquals('2024/01/03 23:59:59', $logStore3['formatted_created_at']);
        }

        //  ファイル名が正しいこと
        $this->assertEquals($expectedFileName, $billingLogReport->getFileName());
    }

    /**
     * @return array[]
     */
    public static function getBillingLogReportData(): array
    {
        return [
            'サンドボックスデータを含めない' => [false, 2, '課金ログレポート_2023-12.xlsx'],
            'サンドボックスデータを含める' => [true, 3, '課金ログレポート_2023-12_サンドボックスデータ含む.xlsx'],
        ];
    }

    #[Test]
    public function getBillingLogExportData_データがない(): void
    {
        // setup
        $this->makeBillingLogReportRecordOutOfTerm();

        // Exercise
        $billingLogReport = $this->billingAdminService
            ->getBillingLogReport('2023', '12', false, 100000);
        $dataCollection = $this->getProperty($billingLogReport, 'data');

        // Verify
        $this->assertTrue($dataCollection->isEmpty());
    }

    #[Test]
    public function purchasedByTool_購入処理(): void
    {
        // setup
        //  各パラメータ
        $userId = '100';
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $deviceId = 'grant by tool';
        $storeProductId = 'ap-1';
        $mstStoreProductId = '1-1';
        $productSubId = '1';
        $purchasePrice = '100';
        $rawPriceString = '¥100';
        $vipPoint = 21;
        $currencyCode = 'JPY';
        $receiptUniqueId = 'receipt_unique_admin';
        $trigger = new Trigger(
            'adminPurchased',
            $productSubId,
            $productSubId,
            "product_id: {$storeProductId}, billing_platform: {$billingPlatform}, "
            . "mst_product_id: {$mstStoreProductId}"
        );
        $loggingProductSubName = $productSubId;
        //  コールバック実行の確認用フラグ
        $actualCallbackFlg = false;
        $callback = function () use (&$actualCallbackFlg) {
            $actualCallbackFlg = true;
        };
        $isSandbox = false;
        //  購入商品のマスタデータを作成
        $paidAmount = 10;
        $this->insertMstStoreProduct($mstStoreProductId, 0, $storeProductId, 'gg-1');
        $this->insertOptProduct($productSubId, 0, $mstStoreProductId, $paidAmount);
        //  ユーザーデータ作成
        $this->currencyDelegator->createUser(
            $userId,
            $osPlatform,
            $billingPlatform,
            0,
        );
        $this->billingService->setStoreInfo($userId, 30, '2024-01-01 00:00:00');

        // Exercise
        $this->billingAdminService->purchasedByTool(
            $userId,
            $osPlatform,
            $billingPlatform,
            $deviceId,
            $storeProductId,
            $mstStoreProductId,
            $productSubId,
            $purchasePrice,
            $rawPriceString,
            $vipPoint,
            $currencyCode,
            $receiptUniqueId,
            $trigger,
            $loggingProductSubName,
            $callback,
            $isSandbox
        );

        // Verify
        //  購入した数だけ所持数が増えていること
        $currencySummary = $this->currencyDelegator->getCurrencySummary($userId);
        $this->assertEquals($paidAmount, $currencySummary->paid_amount_apple);

        //  paidの管理レコードが追加されていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findByUserId($userId)[0];
        $this->assertEquals($userId, $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $usrCurrencyPaid->seq_no);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrCurrencyPaid->billing_platform);
        $this->assertEquals($paidAmount, $usrCurrencyPaid->left_amount);
        $this->assertEquals('100.000000', $usrCurrencyPaid->purchase_price);
        $this->assertEquals($paidAmount, $usrCurrencyPaid->purchase_amount);
        $this->assertEquals('10.00000000', $usrCurrencyPaid->price_per_amount);
        $this->assertEquals($vipPoint, $usrCurrencyPaid->vip_point);
        $this->assertEquals($currencyCode, $usrCurrencyPaid->currency_code);
        $this->assertEquals($receiptUniqueId, $usrCurrencyPaid->receipt_unique_id);
        $this->assertEquals($isSandbox, $usrCurrencyPaid->is_sandbox);

        //  コールバックが動作していること
        $this->assertTrue($actualCallbackFlg);

        //  ストア購入履歴が登録されていること
        $usrStoreProductHistory = $this->usrStoreProductHistoryRepository
            ->findByReceiptUniqueIdAndBillingPlatform($receiptUniqueId, $billingPlatform);
        $this->assertEquals($userId, $usrStoreProductHistory->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrStoreProductHistory->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrStoreProductHistory->billing_platform);
        $this->assertEquals($productSubId, $usrStoreProductHistory->product_sub_id);
        $this->assertEquals($storeProductId, $usrStoreProductHistory->platform_product_id);
        $this->assertEquals($mstStoreProductId, $usrStoreProductHistory->mst_store_product_id);
        $this->assertEquals($currencyCode, $usrStoreProductHistory->currency_code);
        $this->assertEquals('100.000000', $usrStoreProductHistory->purchase_price);
        $this->assertEquals('10.00000000', $usrStoreProductHistory->price_per_amount);
        $this->assertEquals($vipPoint, $usrStoreProductHistory->vip_point);
        $this->assertEquals($deviceId, $usrStoreProductHistory->device_id);
        $this->assertEquals(30, $usrStoreProductHistory->age);

        //  ストア情報の累計購入額に加算されていること
        $usrStoreInfo = $this->billingService->getStoreInfo($userId);
        $this->assertEquals(100, $usrStoreInfo->paid_price);
        $this->assertEquals($vipPoint, $usrStoreInfo->total_vip_point);

        //  ログが追加されていること
        //   log_store
        $logStore = $this->logStoreRepository->findByUserId($userId)[0];
        $this->assertEquals($userId, $logStore->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logStore->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logStore->billing_platform);
        $this->assertEquals($deviceId, $logStore->device_id);
        $this->assertEquals(30, $logStore->age);
        $this->assertEquals($storeProductId, $logStore->platform_product_id);
        $this->assertEquals($mstStoreProductId, $logStore->mst_store_product_id);
        $this->assertEquals($productSubId, $logStore->product_sub_id);
        $this->assertEquals($loggingProductSubName, $logStore->product_sub_name);
        $this->assertEquals('100.000000', $logStore->purchase_price);
        $this->assertEquals('10.00000000', $logStore->price_per_amount);
        $this->assertEquals($vipPoint, $logStore->vip_point);
        $this->assertEquals($currencyCode, $logStore->currency_code);
        $this->assertEquals($receiptUniqueId, $logStore->receipt_unique_id);
        $this->assertEquals($usrStoreProductHistory->receipt_bundle_id, $logStore->receipt_bundle_id);
        //    レシート情報はjson_decodeしてpayloadとstoreの文字列をチェックする
        $receipt = json_decode($logStore->raw_receipt, true);
        $this->assertEquals('GrantByTool', $receipt['Payload']);
        $this->assertEquals('admin', $receipt['Store']);
        $this->assertEquals($rawPriceString, $logStore->raw_price_string);
        $this->assertEquals('10', $logStore->paid_amount);
        $this->assertEquals(0, $logStore->free_amount);
        $this->assertEquals('adminPurchased', $logStore->trigger_type);
        $this->assertEquals($productSubId, $logStore->trigger_id);
        $this->assertEquals($productSubId, $logStore->trigger_name);
        $this->assertEquals('product_id: ap-1, billing_platform: AppStore, mst_product_id: 1-1', $logStore->trigger_detail);

        //  log_currency_paid
        $logCurrencyPaid = LogCurrencyPaid::query()->where('usr_user_id', $userId)->first();
        $this->assertEquals($userId, $logCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logCurrencyPaid->billing_platform);
        $this->assertEquals($usrCurrencyPaid->id, $logCurrencyPaid->currency_paid_id);
        $this->assertEquals($usrCurrencyPaid->receipt_unique_id, $logCurrencyPaid->receipt_unique_id);
        $this->assertEquals($isSandbox, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_INSERT, $logCurrencyPaid->query);
        $this->assertEquals('100.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(10, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('10.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals($vipPoint, $logCurrencyPaid->vip_point);
        $this->assertEquals($currencyCode, $logCurrencyPaid->currency_code);
        $this->assertEquals(0, $logCurrencyPaid->before_amount);
        $this->assertEquals(10, $logCurrencyPaid->change_amount);
        $this->assertEquals(10, $logCurrencyPaid->current_amount);
        $this->assertEquals('adminPurchased', $logCurrencyPaid->trigger_type);
        $this->assertEquals('1', $logCurrencyPaid->trigger_id);
        $this->assertEquals('1', $logCurrencyPaid->trigger_name);
        $this->assertEquals('product_id: ap-1, billing_platform: AppStore, mst_product_id: 1-1', $logCurrencyPaid->trigger_detail);
    }

    #[Test]
    #[DataProvider('purchasedByToolIncrementPaidPriceData')]
    public function purchasedByTool_incrementPaidPriceのチェック(
        string $currencyCode,
        ?string $renotifyAt,
        int $expected
    ): void {
        // 下記パターンだとusr_store_infoが更新されないのでそのチェック
        //  通貨コードがBillingPurchaseTrait::ADD_PAID_CURRENCY_CODES(JPY)以外
        //  usr_sotre_info.renotify_atがnull

        // setup
        //  各パラメータ
        $userId = '100';
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $deviceId = 'grant by tool';
        $storeProductId = 'ap-1';
        $mstStoreProductId = '1-1';
        $productSubId = '1';
        $purchasePrice = '100';
        $rawPriceString = '¥100';
        $vipPoint = 21;
        $receiptUniqueId = 'receipt_unique_admin';
        $trigger = new Trigger(
            'adminPurchased',
            $productSubId,
            $productSubId,
            "product_id: {$storeProductId}, billing_platform: {$billingPlatform}, "
            . "mst_product_id: {$mstStoreProductId}"
        );
        $loggingProductSubName = $productSubId;
        $callback = function () {};
        $isSandbox = false;
        //  購入商品のマスタデータを作成
        $paidAmount = 10;
        $this->insertMstStoreProduct($mstStoreProductId, 0, $storeProductId, 'gg-1');
        $this->insertOptProduct($productSubId, 0, $mstStoreProductId, $paidAmount);
        //  ユーザーデータ作成
        $this->currencyDelegator->createUser(
            $userId,
            $osPlatform,
            $billingPlatform,
            0,
        );
        $this->billingService->setStoreInfo($userId, 30, $renotifyAt);

        // Exercise
        $this->billingAdminService->purchasedByTool(
            $userId,
            $osPlatform,
            $billingPlatform,
            $deviceId,
            $storeProductId,
            $mstStoreProductId,
            $productSubId,
            $purchasePrice,
            $rawPriceString,
            $vipPoint,
            $currencyCode,
            $receiptUniqueId,
            $trigger,
            $loggingProductSubName,
            $callback,
            $isSandbox
        );

        // Verify
        //  usr_sotre_info.paid_priceに絞ってチェックする
        $usrStoreInfo = $this->billingService->getStoreInfo($userId);
        $this->assertEquals($expected, $usrStoreInfo->paid_price);
    }

    /**
     * @return array
     */
    public static function purchasedByToolIncrementPaidPriceData(): array
    {
        return [
            // $currencyCode, $renotifyAt, $expected
            '更新する' => ['JPY', '2024-01-01 00:00:00', 100],
            '更新しない 年齢確認日がnull' => ['JPY', null, 0],
            '更新しない 通貨コードが国外' => ['USD', '2024-01-01 00:00:00', 0],
        ];
    }

    #[Test]
    #[DataProvider('purchasedByToolExceptionData')]
    public function purchasedByTool_verifyPurchaseStoreProductチェック(
        string $productSubId,
        string $mstStoreProductId,
        string $oprMstProductId,
        string $storeProductId,
        string $errorMsg
    ): void {
        // setup
        //  各パラメータ
        $userId = '100';
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $deviceId = 'grant by tool';
        $purchasePrice = '100';
        $rawPriceString = '¥100';
        $vipPoint = 21;
        $currencyCode = 'JPY';
        $receiptUniqueId = 'receipt_unique_admin';
        $trigger = new Trigger(
            'adminPurchased',
            $productSubId,
            $productSubId,
            "product_id: {$storeProductId}, billing_platform: {$billingPlatform}, "
            . "mst_product_id: {$mstStoreProductId}"
        );
        $loggingProductSubName = $productSubId;
        $callback = function () {};
        $isSandbox = false;
        //  購入商品のマスタデータを作成
        $this->insertMstStoreProduct('1-1', 0, 'ap-1', 'gg-1');
        $this->insertOptProduct('1', 0, $oprMstProductId, 10);
        //  ユーザーデータ作成
        $this->currencyDelegator->createUser(
            $userId,
            $osPlatform,
            $billingPlatform,
            0,
        );
        $this->billingService->setStoreInfo($userId, 30, '2024-01-01 00:00:00');

        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage($errorMsg);
        $this->billingAdminService->purchasedByTool(
            $userId,
            $osPlatform,
            $billingPlatform,
            $deviceId,
            $storeProductId,
            $mstStoreProductId,
            $productSubId,
            $purchasePrice,
            $rawPriceString,
            $vipPoint,
            $currencyCode,
            $receiptUniqueId,
            $trigger,
            $loggingProductSubName,
            $callback,
            $isSandbox
        );
    }

    /**
     * @return array[]
     */
    public static function purchasedByToolExceptionData(): array
    {
        return [
            'opr_productがない' => [
                '2', // $productSubId
                '1-1', // $mstStoreProductId
                '1-1', // $oprMstProductId
                'ap-1', // $storeProductId
                'Billing-102: opr_product not found', // エラーメッセージ
            ],
            'mst_store_productがない' => [
                '1',
                '2-1',
                '1-1',
                'ap-1',
                'Billing-103: mst_store_product not found',
            ],
            'mst_store_product_idが一致しない' => [
                '1',
                '1-1',
                '2-1',
                'ap-1',
                'Billing-100: mst_store_product_id not match, mst:1-1 opr:2-1',
            ],
            'product_idが一致しない' => [
                '1',
                '1-1',
                '1-1',
                'ap-2',
                'Billing-101: mst_store_product_id not match, mst:ap-1 billing:ap-2',
            ],
        ];
    }

    #[Test]
    public function purchasedByTool_usrStoreInfoがnull(): void
    {
        // setup
        //  各パラメータ
        $userId = '100';
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $deviceId = 'grant by tool';
        $storeProductId = 'ap-1';
        $mstStoreProductId = '1-1';
        $productSubId = '1';
        $purchasePrice = '100';
        $rawPriceString = '¥100';
        $vipPoint = 21;
        $currencyCode = 'JPY';
        $receiptUniqueId = 'receipt_unique_admin';
        $trigger = new Trigger(
            'adminPurchased',
            $productSubId,
            $productSubId,
            "product_id: {$storeProductId}, billing_platform: {$billingPlatform}, "
            . "mst_product_id: {$mstStoreProductId}"
        );
        $loggingProductSubName = $productSubId;
        //  コールバック実行の確認用フラグ
        $callback = function () {};
        $isSandbox = false;
        //  購入商品のマスタデータを作成
        $paidAmount = 10;
        $this->insertMstStoreProduct($mstStoreProductId, 0, $storeProductId, 'gg-1');
        $this->insertOptProduct($productSubId, 0, $mstStoreProductId, $paidAmount);
        //  ユーザーデータ作成
        $this->currencyDelegator->createUser(
            $userId,
            $osPlatform,
            $billingPlatform,
            0,
        );

        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('Billing-1: usr_store_info not found');
        $this->billingAdminService->purchasedByTool(
            $userId,
            $osPlatform,
            $billingPlatform,
            $deviceId,
            $storeProductId,
            $mstStoreProductId,
            $productSubId,
            $purchasePrice,
            $rawPriceString,
            $vipPoint,
            $currencyCode,
            $receiptUniqueId,
            $trigger,
            $loggingProductSubName,
            $callback,
            $isSandbox
        );
    }

    #[Test]
    #[DataProvider('makeFakeStoreReceiptAdminData')]
    public function makeFakeStoreReceiptAdmin_生成されたレシートのチェック(string $payload): void
    {
        // Exercise
        $receiptStr = $this->callMethod(
            $this->billingAdminService,
            'makeReceiptAdmin',
            [$payload]
        );

        // Verify
        //  TransactionIDはユニークなので、PayloadとStoreをチェックしている
        $result = json_decode($receiptStr, true);
        $this->assertEquals($payload, $result['Payload']);
        $this->assertEquals('admin', $result['Store']);
    }

    /**
     * @return array[]
     */
    public static function makeFakeStoreReceiptAdminData(): array
    {
        return [
            '有償一次通貨付与ツールからの実行' => ['GrantByTool'],
            '有償一次通貨回収ツールからの実行' => ['CollectByTool']
        ];
    }

    #[Test]
    public function returnedPurchase_回収処理正常実行(): void
    {
        // Setup
        $userId = '100';
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        //  有償一次通貨購入情報を作成
        //   レシート情報生成
        $receipt = $this->makeFakeStoreReceiptNoSandbox('store_product1');
        $this->createPurchaseData($userId, $receipt, $osPlatform, $billingPlatform);
        //  購入情報のusrStoreProductHistoryを取得
        $purchaseHistory = $this->usrStoreProductHistoryRepository
            ->findByReceiptUniqueIdAndBillingPlatform(
                $receipt->getUnitqueId(),
                $billingPlatform
            );

        // 回収処理実行用パラメータ
        $deviceId = 'collect by tool';
        $receiptBundleId = 'COLLECT_BY_TOOL';
        $receiptPurchaseToken = 'COLLECT_BY_TOOL';
        $receiptUniqueId = 'COLLECT_BY_TOOL_TEST';
        $trigger = new CollectPaidCurrencyAdminTrigger($purchaseHistory->id, 'trigger_detail_collect');

        // Exercise
        $this->billingAdminService->returnedPurchase(
            $userId,
            $purchaseHistory->id,
            $deviceId,
            $receiptBundleId,
            $receiptPurchaseToken,
            $receiptUniqueId,
            $trigger
        );

        // Verify
        //  Billing側のチェック
        //   usrStoreProductHistory
        $usrStoreProductHistories = UsrStoreProductHistory::query()
            ->where('usr_user_id', $userId)
            ->get();
        //    レコードが3件ある(購入2件、回収1件)
        $this->assertCount(3, $usrStoreProductHistories);
        //     回収レコードのチェック
        $collectHistory = $usrStoreProductHistories->first(function ($row) use ($deviceId) {
            return $row->device_id === $deviceId;
        });
        $this->assertEquals($receiptUniqueId, $collectHistory->receipt_unique_id);
        $this->assertEquals($osPlatform, $collectHistory->os_platform);
        $this->assertEquals($userId, $collectHistory->usr_user_id);
        $this->assertEquals($deviceId, $collectHistory->device_id);
        $this->assertEquals($purchaseHistory->age, $collectHistory->age);
        $this->assertEquals($purchaseHistory->product_sub_id, $collectHistory->product_sub_id);
        $this->assertEquals($purchaseHistory->platform_product_id, $collectHistory->platform_product_id);
        $this->assertEquals($purchaseHistory->mst_store_product_id, $collectHistory->mst_store_product_id);
        $this->assertEquals($purchaseHistory->currency_code, $collectHistory->currency_code);
        $this->assertEquals($receiptBundleId, $collectHistory->receipt_bundle_id);
        $this->assertEquals(-1 * $purchaseHistory->paid_amount, $collectHistory->paid_amount);
        $this->assertEquals(0, $collectHistory->free_amount);
        $this->assertEquals($purchaseHistory->purchase_price, $collectHistory->purchase_price);
        $this->assertEquals($purchaseHistory->price_per_amount, $collectHistory->price_per_amount);
        $this->assertEquals(-1 * $purchaseHistory->vip_point, $collectHistory->vip_point);
        $this->assertEquals($purchaseHistory->is_sandbox, $collectHistory->is_sandbox);
        $this->assertEquals($billingPlatform, $collectHistory->billing_platform);
        //   LogStore
        $logStores = $this->logStoreRepository->findByUserId($userId);
        //    ログ件数が3件ある(購入2件、回収1件)
        $this->assertCount(3, $logStores);
        //    回収ログのチェック
        $collectLogStore = collect($logStores)->first(fn ($row) => $row->trigger_type === Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN);
        $this->assertEquals(2, $collectLogStore->seq_no);
        $this->assertEquals($userId, $collectLogStore->usr_user_id);
        $this->assertEquals('store_product1', $collectLogStore->platform_product_id);
        $this->assertEquals('mst_product1', $collectLogStore->mst_store_product_id);
        $this->assertEquals('product1', $collectLogStore->product_sub_id);
        $this->assertEquals('product1', $collectLogStore->product_sub_name);
        $result = json_decode($collectLogStore->raw_receipt, true);
        $this->assertEquals('CollectByTool', $result['Payload']);
        $this->assertEquals('100.000000', $collectLogStore->raw_price_string);
        $this->assertEquals('JPY', $collectLogStore->currency_code);
        $this->assertEquals($receiptUniqueId, $collectLogStore->receipt_unique_id);
        $this->assertEquals($receiptBundleId, $collectLogStore->receipt_bundle_id);
        $this->assertEquals($osPlatform, $collectLogStore->os_platform);
        $this->assertEquals($billingPlatform, $collectLogStore->billing_platform);
        $this->assertEquals($deviceId, $collectLogStore->device_id);
        $this->assertEquals(20, $collectLogStore->age);
        $this->assertEquals(-10, $collectLogStore->paid_amount);
        $this->assertEquals(0, $collectLogStore->free_amount);
        $this->assertEquals('-100.000000', $collectLogStore->purchase_price);
        $this->assertEquals('10.00000000', $collectLogStore->price_per_amount);
        $this->assertEquals(-10, $collectLogStore->vip_point);
        $this->assertFalse((bool) $collectLogStore->is_sandbox);
        $this->assertEquals(Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN, $collectLogStore->trigger_type);
        $this->assertEquals($purchaseHistory->id, $collectLogStore->trigger_id);
        $this->assertEquals('', $collectLogStore->trigger_name);
        $this->assertEquals('trigger_detail_collect', $collectLogStore->trigger_detail);
        //  Currency側のチェック
        //   usrCurrencyPaid
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findByUserId($userId);
        //    レコードが2件ある(購入2件(回収対象1件))
        $this->assertCount(2, $usrCurrencyPaids);
        $collectCurrencyPaid = collect($usrCurrencyPaids)->first(fn ($row) => $row->left_amount === 0);
        //    回収レコードの減算チェック
        $this->assertEquals($userId, $collectCurrencyPaid->usr_user_id);
        $this->assertEquals($billingPlatform, $collectCurrencyPaid->billing_platform);
        $this->assertEquals(0, $collectCurrencyPaid->left_amount);
        $this->assertEquals($purchaseHistory->receipt_unique_id, $collectCurrencyPaid->receipt_unique_id);
        //  usrCurrencySummary
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId($userId);
        //   各プラットフォームの通貨チェック
        $this->assertEquals(10, $usrCurrencySummary->getPaidAmountApple());
        $this->assertEquals(0, $usrCurrencySummary->getPaidAmountGoogle());
        $this->assertEquals(10, $usrCurrencySummary->getTotalPaidAmount());
        //  logCurrencyPaidのチェック
        $logCurrencyPaids = $this->logCurrencyPaidRepository->findByUserId($userId);
        //   レコードが3件ある(購入2件、回収1件)
        $this->assertCount(3, $logCurrencyPaids);
        //   回収ログのチェック
        $logCurrencyPaid = collect($logCurrencyPaids)->first(
            function ($row) {
                return $row->trigger_type === 'collect_currency_paid';
            });
        $this->assertEquals(2, $logCurrencyPaid->seq_no);
        $this->assertEquals($userId, $logCurrencyPaid->usr_user_id);
        $this->assertEquals($receiptUniqueId, $logCurrencyPaid->receipt_unique_id);
        $this->assertFalse((bool) $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_UPDATE, $logCurrencyPaid->query);
        $this->assertEquals('100.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(-10, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('10.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(10, $logCurrencyPaid->vip_point);
        $this->assertEquals('JPY', $logCurrencyPaid->currency_code);
        $this->assertEquals(20, $logCurrencyPaid->before_amount);
        $this->assertEquals(-10, $logCurrencyPaid->change_amount);
        $this->assertEquals(10, $logCurrencyPaid->current_amount);
        $this->assertEquals($osPlatform, $logCurrencyPaid->os_platform);
        $this->assertEquals($billingPlatform, $logCurrencyPaid->billing_platform);
        $this->assertEquals('collect_currency_paid', $logCurrencyPaid->trigger_type);
        $this->assertEquals($purchaseHistory->id, $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals('trigger_detail_collect', $logCurrencyPaid->trigger_detail);
    }

    #[Test]
    public function returnedPurchase_無償一次通貨を付与していた場合の回収チェック(): void
    {
        // Setup
        $freeAmount = 100;
        $userId = '100';
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        //  有償一次通貨購入情報を作成
        //   レシート情報生成
        $receipt = $this->makeFakeStoreReceiptNoSandbox('store_product1');
        $this->createPurchaseData($userId, $receipt, $osPlatform, $billingPlatform, $freeAmount);
        //  購入情報のusrStoreProductHistoryを取得
        $purchaseHistory = $this->usrStoreProductHistoryRepository
            ->findByReceiptUniqueIdAndBillingPlatform(
                $receipt->getUnitqueId(),
                $billingPlatform
            );

        // 回収処理実行用パラメータ
        $deviceId = 'collect by tool';
        $receiptBundleId = 'COLLECT_BY_TOOL';
        $receiptPurchaseToken = 'COLLECT_BY_TOOL';
        $receiptUniqueId = 'COLLECT_BY_TOOL_TEST';
        $trigger = new CollectPaidCurrencyAdminTrigger($purchaseHistory->id, 'trigger_detail_collect');

        // Exercise
        $this->billingAdminService->returnedPurchase(
            $userId,
            $purchaseHistory->id,
            $deviceId,
            $receiptBundleId,
            $receiptPurchaseToken,
            $receiptUniqueId,
            $trigger
        );

        // Verify
        //  無償一次通貨に関するデータだけチェックする
        //   usrStoreProductHistory
        $usrStoreProductHistories = UsrStoreProductHistory::query()
            ->where('usr_user_id', $userId)
            ->get();
        //     回収レコードのチェック
        $collectHistory = $usrStoreProductHistories->first(function ($row) use ($deviceId) {
            return $row->device_id === $deviceId;
        });
        $this->assertEquals($receiptUniqueId, $collectHistory->receipt_unique_id);
        $this->assertEquals(-100, $collectHistory->free_amount);
        //   LogStore
        $logStores = $this->logStoreRepository->findByUserId($userId);
        //    回収ログのチェック
        $collectLogStore = collect($logStores)->first(fn ($row) => $row->trigger_type === Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN);
        $this->assertEquals($userId, $collectLogStore->usr_user_id);
        $this->assertEquals(-100, $collectLogStore->free_amount);
        //   usrCurrencyFree
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId($userId);
        $this->assertEquals($userId, $usrCurrencyFree->usr_user_id);
        $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(100, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);
        //  usrCurrencySummary
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId($userId);
        //   各プラットフォームの通貨チェック
        $this->assertEquals(10, $usrCurrencySummary->getPaidAmountApple());
        $this->assertEquals(0, $usrCurrencySummary->getPaidAmountGoogle());
        $this->assertEquals(10, $usrCurrencySummary->getTotalPaidAmount());
        $this->assertEquals(110, $usrCurrencySummary->getTotalCurrencyAmount());
        //  logCurrencyFreeのチェック
        $logCurrencyFrees = $this->logCurrencyFreeRepository->findByUserId($userId);
        $logCurrencyFree = collect($logCurrencyFrees)->first(fn ($row) => $row->trigger_type === Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN);
        $this->assertEquals($userId, $logCurrencyFree->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFree->os_platform);
        $this->assertEquals(0, $logCurrencyFree->before_ingame_amount);
        $this->assertEquals(200, $logCurrencyFree->before_bonus_amount);
        $this->assertEquals(0, $logCurrencyFree->before_reward_amount);
        $this->assertEquals(0, $logCurrencyFree->change_ingame_amount);
        $this->assertEquals(-100, $logCurrencyFree->change_bonus_amount);
        $this->assertEquals(0, $logCurrencyFree->change_reward_amount);
        $this->assertEquals(0, $logCurrencyFree->current_ingame_amount);
        $this->assertEquals(100, $logCurrencyFree->current_bonus_amount);
        $this->assertEquals(0, $logCurrencyFree->current_reward_amount);
        $this->assertEquals('collect_currency_paid', $logCurrencyFree->trigger_type);
        $this->assertEquals($purchaseHistory->id, $logCurrencyFree->trigger_id);
        $this->assertEquals('', $logCurrencyFree->trigger_name);
        $this->assertEquals('trigger_detail_collect', $logCurrencyFree->trigger_detail);
    }

    #[Test]
    public function returnedPurchase_回収対象の残高が0(): void
    {
        // Setup
        $userId = '100';
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        //  有償一次通貨購入情報を作成
        //   レシート情報生成
        $receipt = $this->makeFakeStoreReceiptNoSandbox('store_product1');
        $this->createPurchaseData($userId, $receipt, $osPlatform, $billingPlatform);
        //  購入情報のusrStoreProductHistoryを取得
        $purchaseHistory = $this->usrStoreProductHistoryRepository
            ->findByReceiptUniqueIdAndBillingPlatform(
                $receipt->getUnitqueId(),
                $billingPlatform
            );
        // apple通貨20消費(完全消費)
        $this->currencyService->usePaid(
            $userId,
            CurrencyConstants::OS_PLATFORM_IOS,
            $billingPlatform,
            20,
            new Trigger(
                'consume',
                'test_trigger_id',
                'test_trigger_name',
                'test_trigger_detail'
            ),
        );
        // 回収処理実行用パラメータ
        $deviceId = 'collect by tool';
        $receiptBundleId = 'COLLECT_BY_TOOL';
        $receiptPurchaseToken = 'COLLECT_BY_TOOL';
        $receiptUniqueId = 'COLLECT_BY_TOOL_TEST';
        $trigger = new CollectPaidCurrencyAdminTrigger($purchaseHistory->id, 'trigger_detail_collect');

        // Exercise
        $this->billingAdminService->returnedPurchase(
            $userId,
            $purchaseHistory->id,
            $deviceId,
            $receiptBundleId,
            $receiptPurchaseToken,
            $receiptUniqueId,
            $trigger
        );

        // Verify
        //  回収後、paid_amount_appleと所持総数がマイナスになることをチェック
        //  Currency側のレコードに絞ってチェックする
        //   usrCurrencyPaid
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findByUserId($userId);
        //    レコードが2件ある(購入2件(うち回収対象1件))
        $this->assertCount(2, $usrCurrencyPaids);
        $collectCurrencyPaid = collect($usrCurrencyPaids)
            ->first(function ($row) use ($receipt) {
                return $row->receipt_unique_id === $receipt->getUnitqueId();
            });
        //    回収レコードの減算チェック
        $this->assertEquals($userId, $collectCurrencyPaid->usr_user_id);
        $this->assertEquals($billingPlatform, $collectCurrencyPaid->billing_platform);
        $this->assertEquals(-10, $collectCurrencyPaid->left_amount);
        $this->assertEquals($purchaseHistory->receipt_unique_id, $collectCurrencyPaid->receipt_unique_id);
        //  usrCurrencySummary
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId($userId);
        //   各プラットフォームの通貨チェック
        $this->assertEquals(-10, $usrCurrencySummary->getPaidAmountApple());
        $this->assertEquals(0, $usrCurrencySummary->getPaidAmountGoogle());
        $this->assertEquals(-10, $usrCurrencySummary->getTotalPaidAmount());
        //  logCurrencyPaidのチェック
        $logCurrencyPaids = $this->logCurrencyPaidRepository->findByUserId($userId);
        //   レコードが3件ある(購入2件、消費2件、回収1件)
        $this->assertCount(5, $logCurrencyPaids);
        //   回収ログのチェック
        $logCurrencyPaid = collect($logCurrencyPaids)->first(
            function ($row) {
                return $row->trigger_type === Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN;
            });
        $this->assertEquals(2, $logCurrencyPaid->seq_no);
        $this->assertEquals($userId, $logCurrencyPaid->usr_user_id);
        $this->assertEquals($receiptUniqueId, $logCurrencyPaid->receipt_unique_id);
        $this->assertFalse((bool) $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_UPDATE, $logCurrencyPaid->query);
        $this->assertEquals('100.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(-10, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('10.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(10, $logCurrencyPaid->vip_point);
        $this->assertEquals('JPY', $logCurrencyPaid->currency_code);
        $this->assertEquals(0, $logCurrencyPaid->before_amount);
        $this->assertEquals(-10, $logCurrencyPaid->change_amount);
        $this->assertEquals(-10, $logCurrencyPaid->current_amount);
    }

    #[Test]
    public function returnedPurchase_回収した結果vipポイントがマイナスになる(): void
    {
        // Setup
        $userId = '100';
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        //  有償一次通貨購入情報を作成
        //   レシート情報生成
        $receipt = $this->makeFakeStoreReceiptNoSandbox('store_product1');
        // vipポイントは20ポイント所持する
        $this->createPurchaseData($userId, $receipt, $osPlatform, $billingPlatform);
        //  購入情報のusrStoreProductHistoryを取得
        $purchaseHistory = $this->usrStoreProductHistoryRepository
            ->findByReceiptUniqueIdAndBillingPlatform(
                $receipt->getUnitqueId(),
                $billingPlatform
            );
        // vipポイント消費
        //  既存の実装でvipポイント消費経路はないので、テスト用に下記手順で消費させている
        //  vipポイントの減算実装が仕様として存在するなら、そちらと置き換える
        //  UsrStoreProductHistoryにvip_pointがマイナス値のレコードを差し込む
        //  UsrStoreInfoのtotal_vip_pointを更新
        //  ※UsrCurrencyPaidのvip_pointは購入時の記録なので更新しない
        $this->usrStoreProductHistoryRepository
            ->insertStoreProductHistory(
                $userId,
                'deviceId',
                20,
                'receipt_unique_id_sub_vip_point',
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                'product_sub_id_sub_vip_point',
                'platform_product_id_sub_vip_point',
                'mst_store_product_id_sub_vip_point',
                'JPY',
                'receipt_bundle_id_sub_vip_point',
                'receipt_purchase_token',
                0,
                0,
                '0.00',
                '0.00',
                -15,
                false
            );
        $sumVipPoint = $this->usrStoreProductHistoryRepository->sumVipPoint($userId);
        $this->usrStoreInfoRepository->updateTotalVipPoint($userId, $sumVipPoint);

        // 回収処理実行用パラメータ
        $deviceId = 'collect by tool';
        $receiptBundleId = 'COLLECT_BY_TOOL';
        $receiptPurchaseToken = 'COLLECT_BY_TOOL';
        //  回収するレコードのreceipt_unique_id
        $receiptUniqueId = 'COLLECT_BY_TOOL_TEST';
        $trigger = new CollectPaidCurrencyAdminTrigger($purchaseHistory->id, 'trigger_detail_collect');

        // Exercise
        $this->billingAdminService->returnedPurchase(
            $userId,
            $purchaseHistory->id,
            $deviceId,
            $receiptBundleId,
            $receiptPurchaseToken,
            $receiptUniqueId,
            $trigger
        );

        // Verify
        // vipポイントに絞ってチェックする
        //  usrStoreInfo totalVipPointチェック 想定のマイナス値になっているか
        //  回収結果(-5) = 購入(20) - 消費(15) - 回収(10)
        $usrStoreInfo = $this->usrStoreInfoRepository->findByUserId($userId);
        $this->assertEquals(-5, $usrStoreInfo->total_vip_point);

        //  usrStoreProductHistory 回収レコードのチェック 購入分マイナス値になっているか
        $usrStoreProductHistories = UsrStoreProductHistory::query()
            ->where('usr_user_id', $userId)
            ->get();
        $collectHistory = $usrStoreProductHistories->first(function ($row) use ($deviceId) {
            return $row->device_id === $deviceId;
        });
        $this->assertEquals(-10, $collectHistory->vip_point);
        //  LogStore 回収ログのチェック 購入分マイナス値になっているか
        $logStores = $this->logStoreRepository->findByUserId($userId);
        $collectLogStore = collect($logStores)->first(fn ($row) => $row->trigger_type === Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN);
        $this->assertEquals(-10, $collectLogStore->vip_point);
        //  usrCurrencyPaid 回収レコードのチェック
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findByUserId($userId);
        $collectCurrencyPaid = collect($usrCurrencyPaids)->first(fn ($row) => $row->left_amount === 0);
        $this->assertEquals(10, $collectCurrencyPaid->vip_point);
        //  logCurrencyPaid 回収ログのチェック
        $logCurrencyPaids = $this->logCurrencyPaidRepository->findByUserId($userId);
        $logCurrencyPaid = collect($logCurrencyPaids)->first(
            function ($row) {
                return $row->trigger_type === 'collect_currency_paid';
            });
        $this->assertEquals(10, $logCurrencyPaid->vip_point);
    }

    #[Test]
    public function returnedPurchase_usrStoreProductHistoryがなくて例外エラー(): void
    {
        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('Billing-16: usr_store_product_history not found historyId=999');
        $this->billingAdminService->returnedPurchase(
            '100',
            '999',
            'collect by tool',
            'COLLECT_BY_TOOL',
            'COLLECT_BY_TOOL',
            'COLLECT_BY_TOOL_TEST',
            new CollectPaidCurrencyAdminTrigger('usr_store_product_history_id', 'trigger_detail_collect')
        );
    }

    #[Test]
    public function returnedPurchase_ユーザーIDが一致しないエラー(): void
    {
        // Setup
        $userId = '100';
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        //  有償一次通貨購入情報を作成
        //   レシート情報生成
        $receipt = $this->makeFakeStoreReceiptNoSandbox('store_product1');
        // usrStoreProductHistoryを作成
        $this->usrStoreProductHistoryRepository
            ->insertStoreProductHistory(
                $userId,
                'deviceId',
                20,
                $receipt->getUnitqueId(),
                $osPlatform,
                $billingPlatform,
                'productSubId',
                'platformProductId',
                'mstStoreProductId',
                'JPY',
                $receipt->getBundleId(),
                $receipt->getPurchaseToken(),
                10,
                0,
                '100.00',
                '10.00',
                0,
                false
            );
        $purchaseHistory = $this->usrStoreProductHistoryRepository
            ->findByReceiptUniqueIdAndBillingPlatform(
                $receipt->getUnitqueId(),
                $billingPlatform
            );

        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('Billing-17: unmatched userId history_user_id=100, userId=999');
        $this->billingAdminService->returnedPurchase(
            '999',
            $purchaseHistory->id,
            'collect by tool',
            'COLLECT_BY_TOOL',
            'COLLECT_BY_TOOL',
            'COLLECT_BY_TOOL_TEST',
            new CollectPaidCurrencyAdminTrigger($purchaseHistory->id, 'trigger_detail_collect')
        );
    }

    #[Test]
    public function returnedPurchase_usrStoreInfoがなくて例外エラー(): void
    {
        // Setup
        $userId = '100';
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        //  有償一次通貨購入情報を作成
        //   レシート情報生成
        $receipt = $this->makeFakeStoreReceiptNoSandbox('store_product1');
        // usrStoreProductHistoryを作成
        $this->usrStoreProductHistoryRepository
            ->insertStoreProductHistory(
                $userId,
                'deviceId',
                20,
                $receipt->getUnitqueId(),
                $osPlatform,
                $billingPlatform,
                'productSubId',
                'platformProductId',
                'mstStoreProductId',
                'JPY',
                $receipt->getBundleId(),
                $receipt->getPurchaseToken(),
                10,
                0,
                '100.00',
                '10.00',
                0,
                false
            );
        $purchaseHistory = $this->usrStoreProductHistoryRepository
            ->findByReceiptUniqueIdAndBillingPlatform(
                $receipt->getUnitqueId(),
                $billingPlatform
            );

        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('Billing-1: usr_store_info not found userId=100');
        $this->billingAdminService->returnedPurchase(
            $userId,
            $purchaseHistory->id,
            'collect by tool',
            'COLLECT_BY_TOOL',
            'COLLECT_BY_TOOL',
            'COLLECT_BY_TOOL_TEST',
            new CollectPaidCurrencyAdminTrigger($purchaseHistory->id, 'trigger_detail_collect')
        );
    }

    /**
     * 課金ログレポート用テストデータ作成(期間内のみ)
     */
    private function makeBillingLogReportRecordWithinTerm(): void
    {
        // 日本時間の2023-11-28 00:00:00(UTC 2023-11-27 15:00:00)に作成
        $now = Carbon::parse('2023-11-28 00:00:00', 'Asia/Tokyo');
        $now->setTimezone('UTC');
        $this->setTestNow($now);
        $this->logStoreRepository->insertStoreLog(
            '2',
            'device2',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            20,
            1,
            'platform_product2',
            'mst_store_product2',
            'product_sub2',
            'product sub name',
            'raw_receipt2',
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
                'trigger_type2',
                'trigger_id2',
                'trigger_name',
                'trigger_detail2'
            )
        );
        // 日本時間の2024-01-03 23:59:59(UTC 2024-01-03 14:59:59)に作成
        $now = Carbon::parse('2024-01-03 23:59:59', 'Asia/Tokyo');
        $now->setTimezone('UTC');
        $this->setTestNow($now);
        $this->logStoreRepository->insertStoreLog(
            '3',
            'device3',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            20,
            1,
            'platform_product3',
            'mst_store_product3',
            'product_sub3',
            'product sub name',
            'raw_receipt3',
            '$1.00',
            'USD',
            'receipt_unique_id3',
            'bundle_id3',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type3',
                'trigger_id3',
                'trigger_name',
                'trigger_detail3'
            )
        );
        // sandboxデータ
        $this->logStoreRepository->insertStoreLog(
            '4',
            'device4',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            20,
            1,
            'platform_product4',
            'mst_store_product4',
            'product_sub4',
            'product sub name',
            'raw_receipt4',
            '$1.00',
            'USD',
            'receipt_unique_id4',
            'bundle_id4',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            true,
            new Trigger(
                'trigger_type4',
                'trigger_id4',
                'trigger_name',
                'trigger_detail4'
            )
        );
    }

    /**
     * 課金ログレポート用テストデータ作成(期間外のみ)
     */
    private function makeBillingLogReportRecordOutOfTerm(): void
    {
        // 日本時間の2023-11-27 23:59:59(UTC 2023-11-27 14:59:59)に作成
        $now = Carbon::parse('2023-11-27 23:59:59', 'Asia/Tokyo');
        $now->setTimezone('UTC');
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
        // 日本時間の2024-01-04 00:00:00(UTC 2024-01-03 15:00:00)に作成
        $now = Carbon::parse('2024-01-04 00:00:00', 'Asia/Tokyo');
        $now->setTimezone('UTC');
        $this->setTestNow($now);
        $this->logStoreRepository->insertStoreLog(
            '4',
            'device4',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            20,
            1,
            'platform_product4',
            'mst_store_product4',
            'product_sub4',
            'product sub name',
            'raw_receipt4',
            '￥1.00',
            'JPY',
            'receipt_unique_id4',
            'bundle_id4',
            100,
            200,
            '1.000000',
            '0.01000000',
            110,
            false,
            new Trigger(
                'trigger_type4',
                'trigger_id4',
                'trigger_name',
                'trigger_detail4'
            )
        );
    }

    /**
     * 外貨為替レートデータ作成
     */
    private function makeAdmForeignCurrencyRate(): void
    {
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
    }

    /**
     * 有償一次通貨購入情報を作成する
     *
     * @return void
     */
    private function createPurchaseData(
        string $userId,
        StoreReceipt $receipt,
        string $osPlatform,
        string $billingPlatform,
        int $freeAmount = 0
    ): void {
        // 配布するマスタデータを作成
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);
        // ユーザーの所持情報を登録
        $this->currencyService->registerCurrencySummary($userId, $osPlatform, 0);
        // ストア情報を登録
        $this->billingService->setStoreInfo($userId, 20, '2020-01-01 00:00:00');

        // 購入情報生成
        //  購入処理にはないが、無償一次通貨(bonus)も加算されたと仮定する
        //   購入1回目
        $this->billingService->allowedToPurchase(
            $userId,
            $osPlatform,
            $billingPlatform,
            'store_product1',
            'product1',
            'device1'
        );
        $allowance = $this->billingService->getStoreAllowance($userId, $billingPlatform, 'store_product1');
        $this->billingService->purchased(
            $userId,
            $osPlatform,
            $billingPlatform,
            'device1',
            $allowance,
            '100.000000',
            '¥100',
            10,
            'JPY',
            $this->makeFakeStoreReceiptNoSandbox('store_product1'),
            new Trigger('purchased', 'product1', 'trigger product1 name', 'sample details'),
            'product1 name',
            function () {}
        );
        //   無償一次通貨加算
        $this->currencyService->addFree(
            $userId,
            $osPlatform,
            100,
            'bonus',
            new Trigger('unit_test', 'sample id', 'sample name', 'detail')
        );
        //   購入情報に無償一次通貨を付与する
        UsrStoreProductHistory::query()
            ->update(['free_amount' => 100]);

        // 購入2回目(回収対象)
        $this->billingService->allowedToPurchase(
            $userId,
            $osPlatform,
            $billingPlatform,
            'store_product1',
            'product1',
            'device1'
        );
        $allowance = $this->billingService->getStoreAllowance($userId, $billingPlatform, 'store_product1');
        $this->billingService->purchased(
            $userId,
            $osPlatform,
            $billingPlatform,
            'device1',
            $allowance,
            '100.000000',
            '¥100',
            10,
            'JPY',
            $receipt,
            new Trigger('purchased', 'product1', 'trigger product1 name', 'sample details'),
            'product1 name',
            function () {}
        );
        if ($freeAmount > 0) {
            //   無償一次通貨加算
            $this->currencyService->addFree(
                $userId,
                $osPlatform,
                $freeAmount,
                'bonus',
                new Trigger('unit_test', 'sample id', 'sample name', 'detail')
            );
            // 購入情報に無償一次通貨を付与する
            UsrStoreProductHistory::query()
                ->update(['free_amount' => 100]);
        }
    }
}
