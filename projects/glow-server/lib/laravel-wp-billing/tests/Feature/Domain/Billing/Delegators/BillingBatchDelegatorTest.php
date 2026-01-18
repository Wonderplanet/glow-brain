<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Feature\Domain\Billing\Delegators;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Delegators\BillingAdminDelegator;
use WonderPlanet\Domain\Billing\Delegators\BillingBatchDelegator;
use WonderPlanet\Domain\Billing\Entities\StoreReceipt;
use WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory;
use WonderPlanet\Domain\Billing\Repositories\LogStoreRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Billing\Services\BillingService;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencySummaryRepository;
use WonderPlanet\Domain\Currency\Services\CurrencyService;
use WonderPlanet\Tests\Traits\Domain\Currency\DataFixtureTrait;

class BillingBatchDelegatorTest extends TestCase
{
    use RefreshDatabase;
    use DataFixtureTrait;
    use FakeStoreReceiptTrait;

    private BillingBatchDelegator $billingBatchDelegator;
    private BillingAdminDelegator $billingAdminDelegator;
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

        $this->billingBatchDelegator = $this->app->make(BillingBatchDelegator::class);
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
    public function purchasedByBatch_購入成功(): void
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
        $receiptUniqueId = 'receipt_unique_batch';
        $trigger = new Trigger(
            'purchase by batch',
            'trigger_id',
            'trigger_name',
            'trigger detail purchase by batch'
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
        $this->billingBatchDelegator->purchasedByBatch(
            $userId,
            $osPlatform,
            $billingPlatform,
            $deviceId,
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
        $this->assertEquals('GrantByBatch', $receipt['Payload']);
        $this->assertEquals('batch', $receipt['Store']);
        $this->assertEquals($rawPriceString, $logStore->raw_price_string);
        $this->assertEquals('10', $logStore->paid_amount);
        $this->assertEquals(0, $logStore->free_amount);
        $this->assertEquals('purchase by batch', $logStore->trigger_type);
        $this->assertEquals('trigger_id', $logStore->trigger_id);
        $this->assertEquals('trigger_name', $logStore->trigger_name);
        $this->assertEquals('trigger detail purchase by batch', $logStore->trigger_detail);

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
        $this->assertEquals('purchase by batch', $logCurrencyPaid->trigger_type);
        $this->assertEquals('trigger_id', $logCurrencyPaid->trigger_id);
        $this->assertEquals('trigger_name', $logCurrencyPaid->trigger_name);
        $this->assertEquals('trigger detail purchase by batch', $logCurrencyPaid->trigger_detail);
    }

    #[Test]
    public function returnedPurchaseByBatch_実行(): void
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
        $deviceId = 'collect by batch';
        $receiptBundleId = 'COLLECT_BY_BATCH';
        $receiptPurchaseToken = 'COLLECT_BY_BATCH';
        $receiptUniqueId = 'COLLECT_BY_BATCH_TEST';
        $triggerDetail = 'trigger_detail_collect';

        // Exercise
        $this->billingBatchDelegator
            ->returnedPurchaseByBatch(
                $userId,
                $purchaseHistory->id,
                $deviceId,
                $receiptBundleId,
                $receiptPurchaseToken,
                $receiptUniqueId,
                $triggerDetail
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
        $collectLogStore = collect($logStores)->first(fn ($row) => $row->trigger_type === Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_BATCH);
        $this->assertEquals(1, $collectLogStore->seq_no);
        $this->assertEquals($userId, $collectLogStore->usr_user_id);
        $this->assertEquals('store_product1', $collectLogStore->platform_product_id);
        $this->assertEquals('mst_product1', $collectLogStore->mst_store_product_id);
        $this->assertEquals('product1', $collectLogStore->product_sub_id);
        $this->assertEquals('product1', $collectLogStore->product_sub_name);
        $result = json_decode($collectLogStore->raw_receipt, true);
        $this->assertEquals('CollectByBatch', $result['Payload']);
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
        $this->assertEquals(Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_BATCH, $collectLogStore->trigger_type);
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
        $logCurrencyPaid = collect($logCurrencyPaids)
            ->first(fn ($row) =>  $row->trigger_type === Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_BATCH);
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
        $this->assertEquals(Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_BATCH, $logCurrencyPaid->trigger_type);
        $this->assertEquals($purchaseHistory->id, $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals('trigger_detail_collect', $logCurrencyPaid->trigger_detail);
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
