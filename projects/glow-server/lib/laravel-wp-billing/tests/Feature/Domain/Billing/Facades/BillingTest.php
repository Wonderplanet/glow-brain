<?php

namespace WonderPlanet\Tests\Feature\Domain\Billing\Facades;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Delegators\BillingDelegator;
use WonderPlanet\Domain\Billing\Facades\Billing;
use WonderPlanet\Domain\Billing\Repositories\LogAllowanceRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreAllowanceRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreInfoRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Billing\Services\BillingService;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencySummaryRepository;
use WonderPlanet\Domain\Currency\Services\CurrencyService;
use WonderPlanet\Tests\Traits\Domain\Currency\DataFixtureTrait;

/**
 * 課金基盤のFacadeのテスト
 */
class BillingTest extends TestCase
{
    use RefreshDatabase;
    use DataFixtureTrait;
    use FakeStoreReceiptTrait;

    private UsrStoreAllowanceRepository $usrStoreAllowanceRepository;
    private BillingService $billingService;
    private CurrencyService $currencyService;
    private UsrCurrencySummaryRepository $usrCurrencySummaryRepository;
    private UsrCurrencyFreeRepository $usrCurrencyFreeRepository;
    private UsrCurrencyPaidRepository $usrCurrencyPaidRepository;
    private UsrStoreInfoRepository $usrStoreInfoRepository;
    private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository;
    private LogAllowanceRepository $logAllowanceRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->usrStoreAllowanceRepository = $this->app->make(UsrStoreAllowanceRepository::class);
        $this->billingService = $this->app->make(BillingService::class);
        $this->usrCurrencySummaryRepository = $this->app->make(UsrCurrencySummaryRepository::class);
        $this->usrCurrencyFreeRepository = $this->app->make(UsrCurrencyFreeRepository::class);
        $this->currencyService = $this->app->make(CurrencyService::class);
        $this->usrCurrencyPaidRepository = $this->app->make(UsrCurrencyPaidRepository::class);
        $this->usrStoreInfoRepository = $this->app->make(UsrStoreInfoRepository::class);
        $this->usrStoreProductHistoryRepository = $this->app->make(UsrStoreProductHistoryRepository::class);
        $this->logAllowanceRepository = $this->app->make(LogAllowanceRepository::class);
    }

    #[Test]
    public function allowedToPurchase_許可情報を登録する()
    {
        // Setup
        // 通貨管理情報を登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');

        // Exercise
        Billing::allowedToPurchase(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'product1',
            'device1',
            'triggerDetailTest'
        );

        // Verify
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findByUserIdAndProductId('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');
        $this->assertEquals('1', $usrStoreAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrStoreAllowance->billing_platform);
        $this->assertEquals('store_product1', $usrStoreAllowance->product_id);
        $this->assertEquals('mst_product1', $usrStoreAllowance->mst_store_product_id);
        $this->assertEquals('product1', $usrStoreAllowance->product_sub_id);
        $this->assertEquals('device1', $usrStoreAllowance->device_id);

        // trigger_detailが登録されていること
        $logAllowance = $this->logAllowanceRepository->findByUserId('1')[0];
        $this->assertEquals('triggerDetailTest', $logAllowance->trigger_detail);
    }

    #[Test]
    public function getStoreAllowance_許可情報を取得する()
    {
        // Setup
        // 通貨管理情報を登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');
        Billing::allowedToPurchase(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'product1',
            'device1'
        );

        // Exercise
        $result = Billing::getStoreAllowance('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');

        // Verify
        $this->assertNotNull($result);
    }

    #[Test]
    public function verifyReceipt_レシート検証する()
    {
        // Setup
        $receipt = self::makeFakeStoreReceiptString('unique_id1');

        // Exercise
        $actual = $this->billingService->verifyReceipt(CurrencyConstants::PLATFORM_APPSTORE, 'store_product1', $receipt);

        // Verify
        $this->assertEquals('unique_id1', $actual->getUnitqueId());
    }

    #[Test]
    public function purchased_購入する()
    {
        // Setup
        // 通貨管理情報を登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 配布するマスタデータを作成
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product_id', 'android_product_id1');
        // 購入情報を登録
        $this->insertOptProduct('product1', 0, 'mst_product1', 100);
        Billing::allowedToPurchase(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product_id',
            'product1',
            'device1'
        );
        $allowance = Billing::getStoreAllowance('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product_id');
        $receipt = $this->makeFakeStoreReceipt('store_product_id', 'receipt1');
        // ストア情報を登録
        $this->billingService->setStoreInfo('1', 20, '2020-01-01 00:00:00');

        // Exercise
        Billing::purchased(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'device1',
            $allowance,
            '0.01',
            '$0.01',
            101,
            'USD',
            $receipt,
            new Trigger('purchased', 'product1', 'product1 name', 'detail'),
            'product1 name',
            function () {
            }
        );

        // Verify
        // 通貨管理の確認 (所持数が増えていることだけ確認)
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(100, $usrCurrencySummary->paid_amount_apple);

        // 所持通貨データの確認 (最低限の照合だけ)
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findByUserId('1')[0];
        $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrCurrencyPaid->billing_platform);
        $this->assertEquals(100, $usrCurrencyPaid->left_amount);
    }

    #[Test]
    public function getStoreInfo_ショップ情報を取得()
    {
        // Setup
        $this->usrStoreInfoRepository->upsertStoreInfo('1', 20, 100, '2020-01-01 00:00:00');

        // Exercise
        $usrStoreInfo = Billing::getStoreInfo('1');

        // Verify
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(20, $usrStoreInfo->age);
        $this->assertEquals(100.000000, $usrStoreInfo->paid_price);
        $this->assertEquals('2020-01-01 00:00:00', $usrStoreInfo->renotify_at);
    }

    #[Test]
    public function setStoreInfo_ショップ情報を登録()
    {
        // Exercise
        $usrStoreInfo = Billing::setStoreInfo('1', 20, '2020-01-01 00:00:00');

        // Verify
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(20, $usrStoreInfo->age);
        $this->assertEquals('0.000000', $usrStoreInfo->paid_price);
        $this->assertEquals('2020-01-01 00:00:00', $usrStoreInfo->renotify_at);
    }

    #[Test]
    public function hasStoreProductHistory_ショップ履歴の有無を確認()
    {
        // Setup
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            '1',
            'device1',
            20,
            'receipt_unique_id1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'product1',
            'store_product1',
            'mst_product1',
            'JPY',
            'bundle1',
            'purchase_token1',
            1,
            0,
            '100',
            '100',
            100,
            false,
        );

        // Exercise
        $result = Billing::hasStoreProductHistory('1');

        // Verify
        $this->assertTrue($result);
    }
}
