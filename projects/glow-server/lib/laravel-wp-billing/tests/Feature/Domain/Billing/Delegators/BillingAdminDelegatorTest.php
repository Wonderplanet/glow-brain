<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Feature\Domain\Billing\Delegators;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Delegators\BillingAdminDelegator;
use WonderPlanet\Domain\Billing\Entities\StoreReceipt;
use WonderPlanet\Domain\Billing\Models\LogStore;
use WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory;
use WonderPlanet\Domain\Billing\Repositories\LogAllowanceRepository;
use WonderPlanet\Domain\Billing\Repositories\LogStoreRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreAllowanceRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Billing\Services\BillingService;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;
use WonderPlanet\Domain\Currency\Entities\CollectPaidCurrencyAdminTrigger;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Models\AdmForeignCurrencyRate;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencySummaryRepository;
use WonderPlanet\Domain\Currency\Services\CurrencyService;
use WonderPlanet\Tests\Traits\Domain\Currency\DataFixtureTrait;

class BillingAdminDelegatorTest extends TestCase
{
    use RefreshDatabase;
    use DataFixtureTrait;
    use FakeStoreReceiptTrait;

    private BillingAdminDelegator $billingAdminDelegator;
    private UsrStoreAllowanceRepository $usrStoreAllowanceRepository;
    private LogAllowanceRepository $logAllowanceRepository;
    private LogStoreRepository $logStoreRepository;
    private CurrencyDelegator $currencyDelegator;
    private BillingService $billingService;
    private UsrCurrencyPaidRepository $usrCurrencyPaidRepository;
    private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository;
    private CurrencyService $currencyService;
    private UsrCurrencySummaryRepository $usrCurrencySummaryRepository;
    private LogCurrencyPaidRepository $logCurrencyPaidRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->billingAdminDelegator = $this->app->make(BillingAdminDelegator::class);
        $this->usrStoreAllowanceRepository = $this->app->make(UsrStoreAllowanceRepository::class);
        $this->logAllowanceRepository = $this->app->make(LogAllowanceRepository::class);
        $this->logStoreRepository = $this->app->make(LogStoreRepository::class);
        $this->currencyDelegator = $this->app->make(CurrencyDelegator::class);
        $this->billingService = $this->app->make(BillingService::class);
        $this->usrCurrencyPaidRepository = $this->app->make(UsrCurrencyPaidRepository::class);
        $this->usrStoreProductHistoryRepository = $this->app->make(UsrStoreProductHistoryRepository::class);
        $this->currencyService = $this->app->make(CurrencyService::class);
        $this->usrCurrencySummaryRepository = $this->app->make(UsrCurrencySummaryRepository::class);
        $this->logCurrencyPaidRepository = $this->app->make(LogCurrencyPaidRepository::class);
    }

    public function tearDown(): void
    {
        $this->setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function insertAllowanceAndLog_正常登録()
    {
        // Exercise
        $this->billingAdminDelegator->insertAllowanceAndLog(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'productId',
            'mstStoreProductId',
            'productSubId',
            'deviceId',
            'triggerDetail'
        );

        // Verify
        // UsrStoreAllowance
        $usrStoreAllowance = $this->usrStoreAllowanceRepository
            ->findAllByUserId(
                '1'
            )[0];
        $this->assertEquals('1', $usrStoreAllowance['usr_user_id']);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS,  $usrStoreAllowance['os_platform']);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE,  $usrStoreAllowance['billing_platform']);
        $this->assertEquals('productId',  $usrStoreAllowance['product_id']);
        $this->assertEquals('mstStoreProductId',  $usrStoreAllowance['mst_store_product_id']);
        $this->assertEquals('productSubId',  $usrStoreAllowance['product_sub_id']);
        $this->assertEquals('deviceId',  $usrStoreAllowance['device_id']);

        // LogAllowance
        $logAllowance = $this->logAllowanceRepository
            ->findByUserId(
                '1'
            )[0];
        $this->assertEquals('1', $logAllowance['usr_user_id']);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logAllowance['os_platform']);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logAllowance['billing_platform']);
        $this->assertEquals('deviceId', $logAllowance['device_id']);
        $this->assertEquals('productId', $logAllowance['product_id']);
        $this->assertEquals('mstStoreProductId', $logAllowance['mst_store_product_id']);
        $this->assertEquals('productSubId', $logAllowance['product_sub_id']);
        $this->assertEquals(Trigger::TRIGGER_TYPE_ALLOWANCE_INSERT, $logAllowance['trigger_type']);
        $this->assertEquals($usrStoreAllowance['id'], $logAllowance['trigger_id']);
        $this->assertEquals('triggerDetail', $logAllowance['trigger_detail']);
    }

    #[Test]
    public function getYearMonthOptions_取得(): void
    {
        // setUp
        // 現在時刻を2023-12-31 23:59:59に固定
        $this->setTestNow(Carbon::parse('2023-12-31 23:59:59'));
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
            'vip_point' => 101,
            'is_sandbox' => false,
            'trigger_type' => '',
            'trigger_id' => '',
            'trigger_name' => '',
            'trigger_detail' => '',
            'request_id' => 'request-id-1',
            'nginx_request_id' => 'nginx-request-id-1',
            'created_at' => '2022-04-01 12:00:00',
            'updated_at' => '2022-04-01 12:00:00',
        ]);

        // Exercise
        [$resultYears, $resultMonthsByYear] = $this->billingAdminDelegator
            ->getYearMonthOptions();

        // Verify
        $this->assertSame(
            [
                '2022' => '2022',
                '2023' => '2023',
            ],
            $resultYears
        );
        $this->assertSame(
            [
                '2022' => array_combine(range(4, 12), range(4, 12)),
                '2023' => array_combine(range(1, 11), range(1, 11)),
            ],
            $resultMonthsByYear
        );
    }

    #[Test]
    #[DataProvider('getBillingLogReportData')]
    public function getBillingLogReport_正常実行(
        bool $isIncludeSandbox,
        int $expectedRowCount,
        string $expectedFileName
    ): void {
        // Setup
        $this->makeBillingLogReportRecordWithinTerm();
        $this->makeBillingLogReportRecordOutOfTerm();
        $this->makeAdmForeignCurrencyRate();

        // Exercise
        $billingLogReport = $this->billingAdminDelegator
            ->getBillingLogReport('2023', '12', $isIncludeSandbox, 100000);
        $dataCollection = $this->getProperty($billingLogReport, 'data');

        // Verify
        $this->assertEquals($expectedRowCount, $dataCollection->count());

        // 取得データが正しいこと
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
    public function purchasedByTool_購入成功(): void
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
        $this->billingAdminDelegator->purchasedByTool(
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

        //  購入許可情報が削除されていること
        $afterAllowance = $this->billingService->getStoreAllowance($userId, $billingPlatform, $storeProductId);
        $this->assertNull($afterAllowance);

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
    public function returnedPurchase_実行(): void
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
        $this->billingAdminDelegator
            ->returnedPurchase(
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
        $this->assertEquals(-1 * $purchaseHistory->free_amount, $collectHistory->free_amount);
        $this->assertEquals($purchaseHistory->purchase_price, $collectHistory->purchase_price);
        $this->assertEquals($purchaseHistory->price_per_amount, $collectHistory->price_per_amount);
        $this->assertEquals(-1 * $purchaseHistory->vip_point, $collectHistory->vip_point);
        $this->assertEquals($purchaseHistory->is_sandbox, $collectHistory->is_sandbox);
        $this->assertEquals($billingPlatform, $collectHistory->billing_platform);
        //   LogStore
        $logStores = $this->logStoreRepository->findByUserId($userId);
        //    ログ件数が2件ある(購入1件、回収1件)
        $this->assertCount(2, $logStores);
        //    回収ログのチェック
        $collectLogStore = collect($logStores)->first(fn ($row) => $row->trigger_type === Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN);
        $this->assertEquals(1, $collectLogStore->seq_no);
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
        //    レコードが1件ある
        $this->assertCount(1, $usrCurrencyPaids);
        $collectCurrencyPaid = collect($usrCurrencyPaids)->first(fn ($row) => $row->left_amount === 0);
        //    回収レコードの減算チェック
        $this->assertEquals($userId, $collectCurrencyPaid->usr_user_id);
        $this->assertEquals($billingPlatform, $collectCurrencyPaid->billing_platform);
        $this->assertEquals(0, $collectCurrencyPaid->left_amount);
        $this->assertEquals($purchaseHistory->receipt_unique_id, $collectCurrencyPaid->receipt_unique_id);
        //  usrCurrencySummary
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId($userId);
        //   各プラットフォームの通貨チェック
        $this->assertEquals(0, $usrCurrencySummary->getPaidAmountApple());
        $this->assertEquals(0, $usrCurrencySummary->getPaidAmountGoogle());
        $this->assertEquals(0, $usrCurrencySummary->getTotalPaidAmount());
        //  logCurrencyPaidのチェック
        $logCurrencyPaids = $this->logCurrencyPaidRepository->findByUserId($userId);
        //   レコードが2件ある(購入1件、回収1件)
        $this->assertCount(2, $logCurrencyPaids);
        $logCurrencyPaid = collect($logCurrencyPaids)->first(
            function ($row) {
                return $row->trigger_type === 'collect_currency_paid';
            });
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals($userId, $logCurrencyPaid->usr_user_id);
        $this->assertEquals($receiptUniqueId, $logCurrencyPaid->receipt_unique_id);
        $this->assertFalse((bool) $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_UPDATE, $logCurrencyPaid->query);
        $this->assertEquals('100.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(-10, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('10.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(10, $logCurrencyPaid->vip_point);
        $this->assertEquals('JPY', $logCurrencyPaid->currency_code);
        $this->assertEquals(10, $logCurrencyPaid->before_amount);
        $this->assertEquals(-10, $logCurrencyPaid->change_amount);
        $this->assertEquals(0, $logCurrencyPaid->current_amount);
        $this->assertEquals($osPlatform, $logCurrencyPaid->os_platform);
        $this->assertEquals($billingPlatform, $logCurrencyPaid->billing_platform);
        $this->assertEquals('collect_currency_paid', $logCurrencyPaid->trigger_type);
        $this->assertEquals($purchaseHistory->id, $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals('trigger_detail_collect', $logCurrencyPaid->trigger_detail);
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
            101,
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
            101,
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
            101,
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
            101,
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
        string $billingPlatform
    ): void {
        // 配布するマスタデータを作成
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);
        // ユーザーの所持情報を登録
        $this->currencyService->registerCurrencySummary($userId, $osPlatform, 0);
        // ストア情報を登録
        $this->billingService->setStoreInfo($userId, 20, '2020-01-01 00:00:00');

        // 購入(回収対象)
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
    }
}
