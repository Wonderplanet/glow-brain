<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Delegators\BillingInternalDelegator;
use WonderPlanet\Domain\Billing\Repositories\LogAllowanceRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreAllowanceRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreInfoRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Billing\Services\BillingService;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Constants\ErrorCode;
use WonderPlanet\Domain\Currency\Entities\FreeCurrencyAddEntity;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Enums\FreeCurrencyType;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddFreeCurrencyOverByMaxException;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddPaidCurrencyOverByMaxException;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyException;
use WonderPlanet\Domain\Currency\Models\LogCurrencyFree;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;
use WonderPlanet\Domain\Currency\Models\UsrCurrencyFree;
use WonderPlanet\Domain\Currency\Models\UsrCurrencyPaid;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencySummaryRepository;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

class CurrencyServiceTest extends TestCase
{
    use RefreshDatabase;

    private CurrencyService $currencyService;
    private UsrCurrencySummaryRepository $usrCurrencySummaryRepository;
    private UsrCurrencyFreeRepository $usrCurrencyFreeRepository;
    private UsrCurrencyPaidRepository $usrCurrencyPaidRepository;
    private LogCurrencyFreeRepository $logCurrencyFreeRepository;
    private LogCurrencyPaidRepository $logCurrencyPaidRepository;

    private BillingService $billingService;
    private UsrStoreAllowanceRepository $usrStoreAllowanceRepository;
    private UsrStoreInfoRepository $usrStoreInfoRepository;
    private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository;
    private LogAllowanceRepository $logAllowanceRepository;
    private BillingInternalDelegator $billingInternalDelegator;

    private $beforeConfigWpCurrency;

    public function setUp(): void
    {
        parent::setUp();

        $this->usrCurrencySummaryRepository = $this->app->make(UsrCurrencySummaryRepository::class);
        $this->usrCurrencyFreeRepository = $this->app->make(UsrCurrencyFreeRepository::class);
        $this->usrCurrencyPaidRepository = $this->app->make(UsrCurrencyPaidRepository::class);
        $this->logCurrencyFreeRepository = $this->app->make(LogCurrencyFreeRepository::class);
        $this->logCurrencyPaidRepository = $this->app->make(LogCurrencyPaidRepository::class);

        $this->currencyService = $this->app->make(CurrencyService::class);

        $this->billingService = $this->app->make(BillingService::class);
        $this->usrStoreAllowanceRepository = $this->app->make(UsrStoreAllowanceRepository::class);
        $this->usrStoreInfoRepository = $this->app->make(UsrStoreInfoRepository::class);
        $this->usrStoreProductHistoryRepository = $this->app->make(UsrStoreProductHistoryRepository::class);
        $this->logAllowanceRepository = $this->app->make(LogAllowanceRepository::class);
        $this->billingInternalDelegator = $this->app->make(BillingInternalDelegator::class);

        // テスト内で変更されるConfigの値を保存
        $this->beforeConfigWpCurrency = Config::get('wp_currency');
    }

    public function tearDown(): void
    {
        // Configの値を元に戻す
        Config::set('wp_currency', $this->beforeConfigWpCurrency);

        parent::tearDown();
    }

    #[Test]
    public function registerCurrencySummary_通貨管理情報が登録されていること()
    {
        // Exercise
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 100);

        // Verify
        // summaryの確認
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(100, $usrCurrencySummary->free_amount);

        // freeの確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);
    }

    public static function addCurrencyPaidPlatformData(): array
    {
        return [
            'app store' => [CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100, 100, 0, '0.00010000'],
            'google play' => [CurrencyConstants::OS_PLATFORM_ANDROID, CurrencyConstants::PLATFORM_GOOGLEPLAY, 100, 0, 100, '0.00010000'],

            // 登録数が0の場合
            'app store zero' => [CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 0, 0, 0, '0.00000000'],
            'google play zero' => [CurrencyConstants::OS_PLATFORM_ANDROID, CurrencyConstants::PLATFORM_GOOGLEPLAY, 0, 0, 0, '0.00000000'],
        ];
    }

    #[Test]
    #[DataProvider('addCurrencyPaidPlatformData')]
    public function addCurrencyPaid_有償一次通貨レコードの登録($osPlatform, $billingPlatform, $amount, $expectedApple, $expectedGoogle, $expectPricePerAmount = '0.00010000')
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);

        // Exercise
        $this->currencyService->addCurrencyPaid(
            '1',
            $osPlatform,
            $billingPlatform,
            $amount,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', 'unit test id1', 'unit test name', 'detail')
        );

        // Verify
        // 渡したパラメータから計算した結果が登録されていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findByUserId('1')[0];
        $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $usrCurrencyPaid->seq_no);
        $this->assertEquals($amount, $usrCurrencyPaid->left_amount);
        $this->assertEquals('0.010000', $usrCurrencyPaid->purchase_price);
        $this->assertEquals($amount, $usrCurrencyPaid->purchase_amount);
        $this->assertEquals($expectPricePerAmount, $usrCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $usrCurrencyPaid->vip_point);
        $this->assertEquals('USD', $usrCurrencyPaid->currency_code);
        $this->assertEquals('dummy receipt 1', $usrCurrencyPaid->receipt_unique_id);
        $this->assertEquals(true, $usrCurrencyPaid->is_sandbox);
        $this->assertEquals($billingPlatform, $usrCurrencyPaid->billing_platform);
        $this->assertEquals($osPlatform, $usrCurrencyPaid->os_platform);

        // サマリーが更新されていること
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals($expectedApple, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals($expectedGoogle, $usrCurrencySummary->paid_amount_google);

        // ログが追加されていること
        $logCurrencyPaid = $this->logCurrencyPaidRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals($osPlatform, $logCurrencyPaid->os_platform);
        $this->assertEquals($billingPlatform, $logCurrencyPaid->billing_platform);
        $this->assertEquals($usrCurrencyPaid->id, $logCurrencyPaid->currency_paid_id);
        $this->assertEquals('dummy receipt 1', $logCurrencyPaid->receipt_unique_id);
        $this->assertEquals(LogCurrencyPaid::QUERY_INSERT, $logCurrencyPaid->query);
        $this->assertEquals('0.010000', $logCurrencyPaid->purchase_price);
        $this->assertEquals($amount, $logCurrencyPaid->purchase_amount);
        $this->assertEquals($expectPricePerAmount, $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals('USD', $logCurrencyPaid->currency_code);
        $this->assertEquals(0, $logCurrencyPaid->before_amount);
        $this->assertEquals($amount, $logCurrencyPaid->change_amount);
        $this->assertEquals($amount, $logCurrencyPaid->current_amount);
        $this->assertEquals('unit_test', $logCurrencyPaid->trigger_type);
        $this->assertEquals('unit test id1', $logCurrencyPaid->trigger_id);
        $this->assertEquals('unit test name', $logCurrencyPaid->trigger_name);
        $this->assertEquals('detail', $logCurrencyPaid->trigger_detail);
    }

    public static function addCurrencyPaidData()
    {
        return [
            '通常の計算' => ['0.00010000', '0.01', 100],
            '整数値のある計算' => ['10.00000000', '100.00', 10],
            '割り切れない単価' => ['3.33333333', '10.00', 3],
        ];
    }

    /**
     * 単価計算を集中してテストする
     */
    #[Test]
    #[DataProvider('addCurrencyPaidData')]
    public function addCurrencyPaid_有償一次通貨登録時の単価計算(
        string $expected,
        string $price,
        int $amount,
    ) {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);

        // Exercise
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            $amount,
            'USD',
            $price,
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );

        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findByUserId('1')[0];
        $this->assertEquals($expected, $usrCurrencyPaid->price_per_amount);
    }

    #[Test]
    public function addCurrencyPaid_有償一次通貨にマイナス値が登録された場合のエラー()
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);

        // Exercise
        $this->expectException(WpCurrencyException::class);
        $this->expectExceptionCode(ErrorCode::FAILED_TO_ADD_PAID_CURRENCY_BY_ZERO);
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            -100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );
    }

    #[Test]
    public function addCurencyPaid_有償一次通貨を追加する際に上限を超えた場合のエラー()
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 999999999);

        // Exercise
        $this->expectException(WpCurrencyAddCurrencyOverByMaxException::class);
        $this->expectExceptionCode(ErrorCode::ADD_CURRENCY_BY_OVER_MAX);
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );
    }

    #[Test]
    public function getCurrencyPaidAll_有償一次通貨情報を全て取得する()
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 有償一次通貨レコードを追加
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );
        // 複数取得する
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            200,
            'USD',
            '0.02',
            201,
            'dummy receipt 2',
            true,
            new Trigger('unit_test', '', '', '')
        );
        // 別のプラットフォームも取得される
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            300,
            'USD',
            '0.03',
            301,
            'dummy receipt 2',
            true,
            new Trigger('unit_test', '', '', '')
        );

        // Exercise
        $result = $this->currencyService->getCurrencyPaidAll('1');

        // Verify
        $this->assertEquals(3, count($result));
        $this->assertEquals('1', $result[0]->usr_user_id);
        $this->assertEquals(1, $result[0]->seq_no);
        $this->assertEquals(100, $result[0]->left_amount);
        $this->assertEquals('0.010000', $result[0]->purchase_price);
        $this->assertEquals(100, $result[0]->purchase_amount);
        $this->assertEquals('0.00010000', $result[0]->price_per_amount);
        $this->assertEquals(101, $result[0]->vip_point);
        $this->assertEquals('USD', $result[0]->currency_code);
        $this->assertEquals('dummy receipt 1', $result[0]->receipt_unique_id);
        $this->assertEquals(true, $result[0]->is_sandbox);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $result[0]->billing_platform);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $result[0]->os_platform);

        $this->assertEquals('1', $result[1]->usr_user_id);
        $this->assertEquals(2, $result[1]->seq_no);
        $this->assertEquals(200, $result[1]->left_amount);
        $this->assertEquals('0.020000', $result[1]->purchase_price);
        $this->assertEquals(200, $result[1]->purchase_amount);
        $this->assertEquals('0.00010000', $result[1]->price_per_amount);
        $this->assertEquals(201, $result[1]->vip_point);
        $this->assertEquals('USD', $result[1]->currency_code);
        $this->assertEquals('dummy receipt 2', $result[1]->receipt_unique_id);
        $this->assertEquals(true, $result[1]->is_sandbox);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $result[1]->billing_platform);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $result[1]->os_platform);

        $this->assertEquals('1', $result[2]->usr_user_id);
        $this->assertEquals(3, $result[2]->seq_no);
        $this->assertEquals(300, $result[2]->left_amount);
        $this->assertEquals('0.030000', $result[2]->purchase_price);
        $this->assertEquals(300, $result[2]->purchase_amount);
        $this->assertEquals('0.00010000', $result[2]->price_per_amount);
        $this->assertEquals(301, $result[2]->vip_point);
        $this->assertEquals('USD', $result[2]->currency_code);
        $this->assertEquals('dummy receipt 2', $result[2]->receipt_unique_id);
        $this->assertEquals(true, $result[2]->is_sandbox);
        $this->assertEquals(CurrencyConstants::PLATFORM_GOOGLEPLAY, $result[2]->billing_platform);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_ANDROID, $result[2]->os_platform);
    }

    #[Test]
    public function getCurrencyPaid_有償一次通貨情報を取得する()
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 有償一次通貨レコードを追加
        $this->usrStoreProductHistoryRepository
            ->insertStoreProductHistory(
                '1',
                'device1',
                20,
                'dummy receipt 1',
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                'product1',
                'store_product1',
                'mst_product1',
                'USD',
                'bundle_id1',
                'purchase_token1',
                100,
                0,
                '0.01',
                '0.00010000',
                101,
                true,
            );
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );
        // 複数取得する
        $this->usrStoreProductHistoryRepository
            ->insertStoreProductHistory(
                '1',
                'device1',
                20,
                'dummy receipt 2',
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                'product2',
                'store_product2',
                'mst_product2',
                'USD',
                'bundle_id2',
                'purchase_token2',
                200,
                0,
                '0.02',
                '0.00010000',
                201,
                true,
            );
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            200,
            'USD',
            '0.02',
            201,
            'dummy receipt 2',
            true,
            new Trigger('unit_test', '', '', '')
        );
        // 別のプラットフォームは取得されてこない
        $this->usrStoreProductHistoryRepository
            ->insertStoreProductHistory(
                '1',
                'device1',
                20,
                'dummy receipt 3',
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_GOOGLEPLAY,
                'product3',
                'store_product3',
                'mst_product3',
                'USD',
                'bundle_id3',
                'purchase_token3',
                300,
                0,
                '0.03',
                '0.00010000',
                301,
                true,
            );
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            300,
            'USD',
            '0.03',
            301,
            'dummy receipt 3',
            true,
            new Trigger('unit_test', '', '', '')
        );
        // 残高0のデータは取得されてこない
        $this->usrStoreProductHistoryRepository
            ->insertStoreProductHistory(
                '1',
                'device1',
                20,
                'dummy receipt 4',
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                'product4',
                'store_product4',
                'mst_product4',
                'USD',
                'bundle_id4',
                'purchase_token4',
                100,
                0,
                '0.03',
                '0.00030000',
                101,
                true,
            );
        $paid = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.03',
            101,
            'dummy receipt 4',
            true,
            new Trigger('unit_test', '', '', '')
        );
        $this->usrCurrencyPaidRepository->decrementPaidAmount('1', CurrencyConstants::PLATFORM_APPSTORE, $paid->id, 100);
        // 残高マイナスのデータは取得されてくる
        $this->usrStoreProductHistoryRepository
            ->insertStoreProductHistory(
                '1',
                'device1',
                20,
                'dummy receipt 5',
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                'product5',
                'store_product5',
                'mst_product5',
                'USD',
                'bundle_id5',
                'purchase_token5',
                200,
                0,
                '0.02',
                '0.00010000',
                201,
                true,
            );
        $paid = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            200,
            'USD',
            '0.02',
            201,
            'dummy receipt 5',
            true,
            new Trigger('unit_test', '', '', '')
        );
        $this->usrCurrencyPaidRepository->decrementPaidAmount('1', CurrencyConstants::PLATFORM_APPSTORE, $paid->id, 201);
        // 商品購入履歴データがなく有償通貨が付与された(管理ツールなど)
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            0,
            'debug_1_dummy_receipt_6',
            true,
            new Trigger('debugPurchased', '', '', '')
        );

        // Exercise
        $result = $this->currencyService->getCurrencyPaid('1', CurrencyConstants::PLATFORM_APPSTORE);

        // Verify
        $this->assertEquals(4, count($result));

        $this->assertEquals('1', $result[0]->usr_user_id);
        $this->assertEquals(1, $result[0]->seq_no);
        $this->assertEquals(100, $result[0]->left_amount);
        $this->assertEquals('0.010000', $result[0]->purchase_price);
        $this->assertEquals(100, $result[0]->purchase_amount);
        $this->assertEquals('0.00010000', $result[0]->price_per_amount);
        $this->assertEquals(101, $result[0]->vip_point);
        $this->assertEquals('USD', $result[0]->currency_code);
        $this->assertEquals('dummy receipt 1', $result[0]->receipt_unique_id);
        $this->assertEquals(true, $result[0]->is_sandbox);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $result[0]->billing_platform);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $result[0]->os_platform);
        //  usr_store_product_history_entity product_sub_idに絞ってチェック
        $this->assertEquals('product1', $result[0]->getUsrStoreProductHistoryEntity()->product_sub_id);

        $this->assertEquals('1', $result[1]->usr_user_id);
        $this->assertEquals(2, $result[1]->seq_no);
        $this->assertEquals(200, $result[1]->left_amount);
        $this->assertEquals('0.020000', $result[1]->purchase_price);
        $this->assertEquals(200, $result[1]->purchase_amount);
        $this->assertEquals('0.00010000', $result[1]->price_per_amount);
        $this->assertEquals(201, $result[1]->vip_point);
        $this->assertEquals('USD', $result[1]->currency_code);
        $this->assertEquals('dummy receipt 2', $result[1]->receipt_unique_id);
        $this->assertEquals(true, $result[1]->is_sandbox);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $result[1]->billing_platform);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $result[1]->os_platform);
        //  usr_store_product_history_entity product_sub_idに絞ってチェック
        $this->assertEquals('product2', $result[1]->getUsrStoreProductHistoryEntity()->product_sub_id);

        $this->assertEquals('1', $result[2]->usr_user_id);
        $this->assertEquals(5, $result[2]->seq_no);
        $this->assertEquals(-1, $result[2]->left_amount);
        $this->assertEquals('0.020000', $result[2]->purchase_price);
        $this->assertEquals(200, $result[2]->purchase_amount);
        $this->assertEquals('0.00010000', $result[2]->price_per_amount);
        $this->assertEquals(201, $result[2]->vip_point);
        $this->assertEquals('USD', $result[2]->currency_code);
        $this->assertEquals('dummy receipt 5', $result[2]->receipt_unique_id);
        $this->assertEquals(true, $result[2]->is_sandbox);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $result[2]->billing_platform);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $result[2]->os_platform);
        //  usr_store_product_history_entity product_sub_idに絞ってチェック
        $this->assertEquals('product5', $result[2]->getUsrStoreProductHistoryEntity()->product_sub_id);

        // 管理ツールから付与された
        $this->assertEquals('1', $result[3]->usr_user_id);
        $this->assertEquals(6, $result[3]->seq_no);
        $this->assertEquals(100, $result[3]->left_amount);
        $this->assertEquals('100.000000', $result[3]->purchase_price);
        $this->assertEquals(100, $result[3]->purchase_amount);
        $this->assertEquals('1.00000000', $result[3]->price_per_amount);
        $this->assertEquals(0, $result[3]->vip_point);
        $this->assertEquals('JPY', $result[3]->currency_code);
        $this->assertEquals('debug_1_dummy_receipt_6', $result[3]->receipt_unique_id);
        $this->assertEquals(true, $result[3]->is_sandbox);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $result[3]->billing_platform);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $result[3]->os_platform);
        //  usr_store_product_history_entityがnullであることをチェック
        $this->assertNull($result[3]->getUsrStoreProductHistoryEntity());
    }

    #[Test]
    public function usePaid_有償一次通貨の消費()
    {
        // Setup
        // 管理情報の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 有償一次通貨レコードを追加
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );

        // Exercise
        $this->currencyService->usePaid('1', CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 10, new Trigger('unit_test', '', '', ''));

        // Verify
        // 有償一次通貨レコードの確認
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findAllByUserIdAndBillingPlatform('1', CurrencyConstants::PLATFORM_APPSTORE)[0];
        $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(90, $usrCurrencyPaid->left_amount);

        // サマリーの確認
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(90, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(0, $usrCurrencySummary->paid_amount_google);
        $this->assertEquals(0, $usrCurrencySummary->free_amount);
    }

    #[Test]
    public function usePaid_有償一次通貨不足、無償一次通貨あり()
    {
        // 無償一次通貨があってもusePaidでは引き落とせない
        // Setup
        // 管理情報の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 100);
        // 有償一次通貨レコードを追加
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );

        // Exercise
        $this->expectException(WpCurrencyException::class);
        $this->expectExceptionCode(ErrorCode::NOT_ENOUGH_PAID_CURRENCY);
        try {
            $this->currencyService->usePaid('1', CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 101, new Trigger('unit_test', '', '', ''));
        } catch (\Exception $e) {
            // Verify
            // 有償一次通貨レコードの確認
            $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findAllByUserIdAndBillingPlatform('1', CurrencyConstants::PLATFORM_APPSTORE);
            // 残高に変更なし
            $this->assertEquals(1, count($usrCurrencyPaids));
            $usrCurrencyPaid = $usrCurrencyPaids[0];
            $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
            $this->assertEquals(100, $usrCurrencyPaid->left_amount);

            // サマリーの確認
            $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
            $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
            $this->assertEquals(100, $usrCurrencySummary->paid_amount_apple);
            $this->assertEquals(100, $usrCurrencySummary->free_amount);

            throw $e;
        }
    }

    #[Test]
    public function usePaid_有償一次通貨不足、無償一次通貨なし()
    {
        // Setup
        // 管理情報の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 有償一次通貨レコードを追加
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );

        // Exercise
        $this->expectException(WpCurrencyException::class);
        $this->expectExceptionCode(ErrorCode::NOT_ENOUGH_PAID_CURRENCY);
        try {
            $this->currencyService->usePaid(
                '1',
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                101,
                new Trigger('unit_test', '', '', '')
            );
        } catch (\Exception $e) {
            // Verify
            // 有償一次通貨レコードの確認
            $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findAllByUserIdAndBillingPlatform('1', CurrencyConstants::PLATFORM_APPSTORE);
            // 残高に変更なし
            $this->assertEquals(1, count($usrCurrencyPaids));
            $usrCurrencyPaid = $usrCurrencyPaids[0];
            $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
            $this->assertEquals(100, $usrCurrencyPaid->left_amount);

            // サマリーの確認
            $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
            $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
            $this->assertEquals(100, $usrCurrencySummary->paid_amount_apple);
            $this->assertEquals(0, $usrCurrencySummary->free_amount);

            throw $e;
        }
    }

    #[Test]
    public function usePaid_通貨管理情報が取得できない()
    {
        // Exercise
        $this->expectException(WpCurrencyException::class);
        $this->expectExceptionCode(ErrorCode::NOT_FOUND_CURRENCY_SUMMARY);
        $this->currencyService->usePaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            101,
            new Trigger('unit_test', '', '', '')
        );
    }

    public static function usePaidInternalData()
    {
        return [
            '1レコードで足りる消費数' => [100, 0, [
                [0, 'dummy receipt 1'],
                [110, 'dummy receipt 2'],
                [120, 'dummy receipt 3'],
                [130, 'dummy receipt 4'],
            ]],
            '1レコードで足りない、2レコードで足りる消費数' => [101, 0, [
                [0, 'dummy receipt 1'],
                [110, 'dummy receipt 2'],
                [119, 'dummy receipt 3'],
                [130, 'dummy receipt 4'],
            ]],
            'プラットフォームすべてのレコードを使っても足りない消費数' => [221, 1, [
                // 別プラットフォームのレコードがあるけれど、合算できないので不足分が返る
                [0, 'dummy receipt 1'],
                [110, 'dummy receipt 2'],
                [0, 'dummy receipt 3'],
                [130, 'dummy receipt 4'],
            ]],
        ];
    }

    #[Test]
    #[DataProvider('usePaidInternalData')]
    public function usePaidInternal_有償一次通貨の消費($amount, $expectedAmount, $expectedCurrent)
    {
        // Setup
        // 管理情報の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 有償一次通貨レコードを追加
        //  apple2、google2追加する
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            110,
            'USD',
            '0.01',
            111,
            'dummy receipt 2',
            true,
            new Trigger('unit_test', '', '', '')
        );
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            120,
            'USD',
            '0.01',
            121,
            'dummy receipt 3',
            true,
            new Trigger('unit_test', '', '', '')
        );
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            130,
            'USD',
            '0.01',
            131,
            'dummy receipt 4',
            true,
            new Trigger('unit_test', '', '', '')
        );

        // Exercise
        $actual = $this->callMethod(
            $this->currencyService,
            'usePaidInternal',
            [
                '1',
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                $amount,
                new Trigger('unit_test', '', '', '')
            ]
        );

        // Verify
        $this->assertEquals($expectedAmount, $actual);
        // 有償一次通貨レコードの確認
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findByUserId('1');

        $this->assertEquals(count($expectedCurrent), count($usrCurrencyPaids));
        // 各レコードの残高
        foreach ($expectedCurrent as $index => [$expected, $receipt]) {
            $usrCurrencyPaid = $usrCurrencyPaids[$index];
            $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
            $this->assertEquals($expected, $usrCurrencyPaid->left_amount);
            $this->assertEquals($receipt, $usrCurrencyPaid->receipt_unique_id);
        }
    }

    public function usePaidInternal_登録順に消費すること()
    {
        // Setup
        // 管理情報の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 有償一次通貨レコードを追加
        //  apple3を追加する
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            110,
            'USD',
            '0.01',
            111,
            'dummy receipt 2',
            true,
            new Trigger('unit_test', '', '', '')
        );
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            120,
            'USD',
            '0.01',
            121,
            'dummy receipt 3',
            true,
            new Trigger('unit_test', '', '', '')
        );

        // Exercise
        $actual = $this->callMethod(
            $this->currencyService,
            'usePaidInternal',
            [
                '1',
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                101,
                new Trigger('unit_test', '', '', '')
            ]
        );

        // Verify
        $this->assertEquals(0, $actual);
        // 有償一次通貨レコードの確認
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findByUserId('1');
        // receipt1、2から引き落とされ、3のレコードは変化なし
        $this->assertEquals(2, count($usrCurrencyPaids));
        $usrCurrencyPaid = $usrCurrencyPaids[0];
        $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(109, $usrCurrencyPaid->left_amount);
        $this->assertEquals('dummy receipt 2', $usrCurrencyPaid->receipt_unique_id);
        $usrCurrencyPaid = $usrCurrencyPaids[1];
        $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(120, $usrCurrencyPaid->left_amount);
        $this->assertEquals('dummy receipt 3', $usrCurrencyPaid->receipt_unique_id);
    }

    #[Test]
    public function usePaidInternal_残高0以下の有償一次通貨は無視されること()
    {
        // Setup
        // 管理情報の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 有償一次通貨レコードを追加
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            111,
            'dummy receipt 2',
            true,
            new Trigger('unit_test', '', '', '')
        );
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            110,
            'dummy receipt 3',
            true,
            new Trigger('unit_test', '', '', '')
        );
        // 登録時に0以下にすることができないので、直接更新する
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findByUserId('1');
        $this->usrCurrencyPaidRepository->decrementPaidAmount('1', CurrencyConstants::PLATFORM_APPSTORE, $usrCurrencyPaids[0]->id, 200);
        $this->usrCurrencyPaidRepository->decrementPaidAmount('1', CurrencyConstants::PLATFORM_APPSTORE, $usrCurrencyPaids[1]->id, 100);

        // Exercise
        $actual = $this->callMethod(
            $this->currencyService,
            'usePaidInternal',
            [
                '1',
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                99,
                new Trigger('unit_test', '', '', '')
            ]
        );

        // Verify
        $this->assertEquals(0, $actual);
        // 有償一次通貨レコードの確認
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findByUserId('1');
        // receipt1、2は無視され、3のレコードから引き落とされる
        $this->assertEquals(3, count($usrCurrencyPaids));
        $usrCurrencyPaid = $usrCurrencyPaids[0];
        $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(-100, $usrCurrencyPaid->left_amount);
        $this->assertEquals('dummy receipt 1', $usrCurrencyPaid->receipt_unique_id);
        $usrCurrencyPaid = $usrCurrencyPaids[1];
        $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(0, $usrCurrencyPaid->left_amount);
        $this->assertEquals('dummy receipt 2', $usrCurrencyPaid->receipt_unique_id);
        $usrCurrencyPaid = $usrCurrencyPaids[2];
        $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $usrCurrencyPaid->left_amount);
        $this->assertEquals('dummy receipt 3', $usrCurrencyPaid->receipt_unique_id);
    }

    #[Test]
    public function usePaidInternal_有償一次通貨消費ログの確認()
    {
        // ログの消費は消費順が絡んでくるため、別のテストで確認する
        // Setup
        // 管理情報の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 有償一次通貨レコードを追加
        //  apple2、google2追加する
        $paid1 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            110,
            'USD',
            '0.01',
            111,
            'dummy receipt 2',
            true,
            new Trigger('unit_test', '', '', '')
        );
        $paid2 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            120,
            'USD',
            '0.01',
            121,
            'dummy receipt 3',
            true,
            new Trigger('unit_test', '', '', '')
        );
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            130,
            'USD',
            '0.01',
            131,
            'dummy receipt 4',
            true,
            new Trigger('unit_test', '', '', '')
        );
        // 1つめのレコードを消費して、2つめのレコードを少し消費する
        $amount = 101;

        // Exercise
        $this->callMethod(
            $this->currencyService,
            'usePaidInternal',
            [
                '1',
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                $amount,
                new Trigger('unit_test', 'test id', 'test name', 'detail')
            ]
        );

        // Verify
        $logCurrencyPaids = $this->logCurrencyPaidRepository->findByUserId('1');
        // 対象レコードを探す
        //  対象のcurrency_paid_idのupdateレコードがあるはず
        //  1つ目の消費
        $logCurrencyPaid = array_values(array_filter(
            $logCurrencyPaids,
            function ($value) use ($paid1) {
                return $value->currency_paid_id === $paid1->id && $value->query === 'update';
            }
        ))[0];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logCurrencyPaid->billing_platform);
        $this->assertEquals('0.010000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(100, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('0.00010000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals('USD', $logCurrencyPaid->currency_code);
        $this->assertEquals(220, $logCurrencyPaid->before_amount);
        $this->assertEquals(-100, $logCurrencyPaid->change_amount);
        $this->assertEquals(120, $logCurrencyPaid->current_amount);
        $this->assertEquals('unit_test', $logCurrencyPaid->trigger_type);
        $this->assertEquals('test id', $logCurrencyPaid->trigger_id);
        $this->assertEquals('test name', $logCurrencyPaid->trigger_name);
        $this->assertEquals('detail', $logCurrencyPaid->trigger_detail);

        // 2つ目の消費
        $logCurrencyPaid = array_values(array_filter(
            $logCurrencyPaids,
            function ($value) use ($paid2) {
                return $value->currency_paid_id === $paid2->id && $value->query === 'update';
            }
        ))[0];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(3, $logCurrencyPaid->seq_no);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logCurrencyPaid->billing_platform);
        $this->assertEquals('0.010000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(120, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('0.00008333', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(121, $logCurrencyPaid->vip_point);
        $this->assertEquals('USD', $logCurrencyPaid->currency_code);
        $this->assertEquals(120, $logCurrencyPaid->before_amount);
        $this->assertEquals(-1, $logCurrencyPaid->change_amount);
        $this->assertEquals(119, $logCurrencyPaid->current_amount);
        $this->assertEquals('unit_test', $logCurrencyPaid->trigger_type);
        $this->assertEquals('test id', $logCurrencyPaid->trigger_id);
        $this->assertEquals('test name', $logCurrencyPaid->trigger_name);
        $this->assertEquals('detail', $logCurrencyPaid->trigger_detail);
    }

    #[Test]
    public function insertOrIncrementFreeCurrency_無償一次通貨の登録()
    {

        // Exercise
        $this->callMethod(
            $this->currencyService,
            'insertOrIncrementFreeCurrency',
            [
                '1',
                CurrencyConstants::OS_PLATFORM_IOS,
                new FreeCurrencyAddEntity(100, 110, 120, new Trigger('unit_test', 'sample id', 'sample name', 'detail'))
            ]
        );

        // Verify
        // 登録情報の確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(110, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(120, $usrCurrencyFree->reward_amount);

        // ログの追加確認
        $logCurrencyFree = $this->logCurrencyFreeRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logCurrencyFree->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFree->os_platform);
        $this->assertEquals(0, $logCurrencyFree->before_ingame_amount);
        $this->assertEquals(0, $logCurrencyFree->before_bonus_amount);
        $this->assertEquals(0, $logCurrencyFree->before_reward_amount);
        $this->assertEquals(100, $logCurrencyFree->change_ingame_amount);
        $this->assertEquals(110, $logCurrencyFree->change_bonus_amount);
        $this->assertEquals(120, $logCurrencyFree->change_reward_amount);
        $this->assertEquals(100, $logCurrencyFree->current_ingame_amount);
        $this->assertEquals(110, $logCurrencyFree->current_bonus_amount);
        $this->assertEquals(120, $logCurrencyFree->current_reward_amount);
        $this->assertEquals('unit_test', $logCurrencyFree->trigger_type);
        $this->assertEquals('sample id', $logCurrencyFree->trigger_id);
        $this->assertEquals('sample name', $logCurrencyFree->trigger_name);
        $this->assertEquals('detail', $logCurrencyFree->trigger_detail);
    }

    #[Test]
    public function insertOrIncrementFreeCurrency_無償一次通貨の追加()
    {
        // Setup
        // 登録済み状態にする
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);

        // Exercise
        $this->callMethod(
            $this->currencyService,
            'insertOrIncrementFreeCurrency',
            [
                '1',
                CurrencyConstants::OS_PLATFORM_IOS,
                new FreeCurrencyAddEntity(10, 20, 30, new Trigger('unit_test', 'sample id', 'sample name', 'detail'))
            ]
        );

        // Verify
        // 追加されていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(110, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(130, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(150, $usrCurrencyFree->reward_amount);

        // ログの追加確認
        $logCurrencyFree = $this->logCurrencyFreeRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logCurrencyFree->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFree->os_platform);
        $this->assertEquals(100, $logCurrencyFree->before_ingame_amount);
        $this->assertEquals(110, $logCurrencyFree->before_bonus_amount);
        $this->assertEquals(120, $logCurrencyFree->before_reward_amount);
        $this->assertEquals(10, $logCurrencyFree->change_ingame_amount);
        $this->assertEquals(20, $logCurrencyFree->change_bonus_amount);
        $this->assertEquals(30, $logCurrencyFree->change_reward_amount);
        $this->assertEquals(110, $logCurrencyFree->current_ingame_amount);
        $this->assertEquals(130, $logCurrencyFree->current_bonus_amount);
        $this->assertEquals(150, $logCurrencyFree->current_reward_amount);
        $this->assertEquals('unit_test', $logCurrencyFree->trigger_type);
        $this->assertEquals('sample id', $logCurrencyFree->trigger_id);
        $this->assertEquals('sample name', $logCurrencyFree->trigger_name);
        $this->assertEquals('detail', $logCurrencyFree->trigger_detail);
    }

    #[Test]
    public function insertOrIncrementFreeCurrencies_無償一次通貨の登録および複数追加()
    {
        // Setup
        // 登録する内容を作成
        $freeCurrencies = [
            // 最初の一つ
            new FreeCurrencyAddEntity(100, 0, 0, new Trigger('unit_test ingame', 'ingame id', 'ingame name', 'ingame')),
            new FreeCurrencyAddEntity(0, 110, 0, new Trigger('unit_test bonus', 'bonus id', 'bonus name', 'bonus')),
            new FreeCurrencyAddEntity(0, 0, 120, new Trigger('unit_test reward', 'reward id', 'reward name', 'reward')),

            // すでにあるものに加算
            new FreeCurrencyAddEntity(110, 0, 0, new Trigger('unit_test ingame2', 'ingame id2', 'ingame name2', 'ingame2')),
            new FreeCurrencyAddEntity(0, 220, 0, new Trigger('unit_test bonus2', 'bonus id2', 'bonus name2', 'bonus2')),
            new FreeCurrencyAddEntity(0, 0, 330, new Trigger('unit_test reward2', 'reward id2', 'reward name2', 'reward2')),
        ];

        // Exercise
        $this->callMethod(
            $this->currencyService,
            'insertOrIncrementFreeCurrencies',
            [
                '1',
                CurrencyConstants::OS_PLATFORM_IOS,
                $freeCurrencies
            ]
        );

        // Verify
        // 追加されていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(100 + 110, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(110 + 220, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(120 + 330, $usrCurrencyFree->reward_amount);

        // ログの追加確認
        // 登録した順にログが追加されていること
        $logCurrencyFrees = $this->logCurrencyFreeRepository->findByUserId('1');

        $this->assertEquals(6, count($logCurrencyFrees));
        $this->assertEquals('1', $logCurrencyFrees[0]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[0]->os_platform);
        $this->assertEquals(0, $logCurrencyFrees[0]->before_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[0]->before_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[0]->before_reward_amount);
        $this->assertEquals(100, $logCurrencyFrees[0]->change_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[0]->change_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[0]->change_reward_amount);
        $this->assertEquals(100, $logCurrencyFrees[0]->current_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[0]->current_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[0]->current_reward_amount);
        $this->assertEquals('unit_test ingame', $logCurrencyFrees[0]->trigger_type);
        $this->assertEquals('ingame id', $logCurrencyFrees[0]->trigger_id);
        $this->assertEquals('ingame name', $logCurrencyFrees[0]->trigger_name);
        $this->assertEquals('ingame', $logCurrencyFrees[0]->trigger_detail);

        $this->assertEquals('1', $logCurrencyFrees[1]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[1]->os_platform);
        $this->assertEquals(100, $logCurrencyFrees[1]->before_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[1]->before_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[1]->before_reward_amount);
        $this->assertEquals(0, $logCurrencyFrees[1]->change_ingame_amount);
        $this->assertEquals(110, $logCurrencyFrees[1]->change_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[1]->change_reward_amount);
        $this->assertEquals(100, $logCurrencyFrees[1]->current_ingame_amount);
        $this->assertEquals(110, $logCurrencyFrees[1]->current_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[1]->current_reward_amount);
        $this->assertEquals('unit_test bonus', $logCurrencyFrees[1]->trigger_type);
        $this->assertEquals('bonus id', $logCurrencyFrees[1]->trigger_id);
        $this->assertEquals('bonus name', $logCurrencyFrees[1]->trigger_name);
        $this->assertEquals('bonus', $logCurrencyFrees[1]->trigger_detail);

        $this->assertEquals('1', $logCurrencyFrees[2]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[2]->os_platform);
        $this->assertEquals(100, $logCurrencyFrees[2]->before_ingame_amount);
        $this->assertEquals(110, $logCurrencyFrees[2]->before_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[2]->before_reward_amount);
        $this->assertEquals(0, $logCurrencyFrees[2]->change_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[2]->change_bonus_amount);
        $this->assertEquals(120, $logCurrencyFrees[2]->change_reward_amount);
        $this->assertEquals(100, $logCurrencyFrees[2]->current_ingame_amount);
        $this->assertEquals(110, $logCurrencyFrees[2]->current_bonus_amount);
        $this->assertEquals(120, $logCurrencyFrees[2]->current_reward_amount);
        $this->assertEquals('unit_test reward', $logCurrencyFrees[2]->trigger_type);
        $this->assertEquals('reward id', $logCurrencyFrees[2]->trigger_id);
        $this->assertEquals('reward name', $logCurrencyFrees[2]->trigger_name);
        $this->assertEquals('reward', $logCurrencyFrees[2]->trigger_detail);

        $this->assertEquals('1', $logCurrencyFrees[3]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[3]->os_platform);
        $this->assertEquals(100, $logCurrencyFrees[3]->before_ingame_amount);
        $this->assertEquals(110, $logCurrencyFrees[3]->before_bonus_amount);
        $this->assertEquals(120, $logCurrencyFrees[3]->before_reward_amount);
        $this->assertEquals(110, $logCurrencyFrees[3]->change_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[3]->change_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[3]->change_reward_amount);
        $this->assertEquals(100 + 110, $logCurrencyFrees[3]->current_ingame_amount);
        $this->assertEquals(110, $logCurrencyFrees[3]->current_bonus_amount);
        $this->assertEquals(120, $logCurrencyFrees[3]->current_reward_amount);
        $this->assertEquals('unit_test ingame2', $logCurrencyFrees[3]->trigger_type);
        $this->assertEquals('ingame id2', $logCurrencyFrees[3]->trigger_id);
        $this->assertEquals('ingame name2', $logCurrencyFrees[3]->trigger_name);
        $this->assertEquals('ingame2', $logCurrencyFrees[3]->trigger_detail);

        $this->assertEquals('1', $logCurrencyFrees[4]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[4]->os_platform);
        $this->assertEquals(100 + 110, $logCurrencyFrees[4]->before_ingame_amount);
        $this->assertEquals(110, $logCurrencyFrees[4]->before_bonus_amount);
        $this->assertEquals(120, $logCurrencyFrees[4]->before_reward_amount);
        $this->assertEquals(0, $logCurrencyFrees[4]->change_ingame_amount);
        $this->assertEquals(220, $logCurrencyFrees[4]->change_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[4]->change_reward_amount);
        $this->assertEquals(100 + 110, $logCurrencyFrees[4]->current_ingame_amount);
        $this->assertEquals(110 + 220, $logCurrencyFrees[4]->current_bonus_amount);
        $this->assertEquals(120, $logCurrencyFrees[4]->current_reward_amount);
        $this->assertEquals('unit_test bonus2', $logCurrencyFrees[4]->trigger_type);
        $this->assertEquals('bonus id2', $logCurrencyFrees[4]->trigger_id);
        $this->assertEquals('bonus name2', $logCurrencyFrees[4]->trigger_name);
        $this->assertEquals('bonus2', $logCurrencyFrees[4]->trigger_detail);

        $this->assertEquals('1', $logCurrencyFrees[5]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[5]->os_platform);
        $this->assertEquals(100 + 110, $logCurrencyFrees[5]->before_ingame_amount);
        $this->assertEquals(110 + 220, $logCurrencyFrees[5]->before_bonus_amount);
        $this->assertEquals(120, $logCurrencyFrees[5]->before_reward_amount);
        $this->assertEquals(0, $logCurrencyFrees[5]->change_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[5]->change_bonus_amount);
        $this->assertEquals(330, $logCurrencyFrees[5]->change_reward_amount);
        $this->assertEquals(100 + 110, $logCurrencyFrees[5]->current_ingame_amount);
        $this->assertEquals(110 + 220, $logCurrencyFrees[5]->current_bonus_amount);
        $this->assertEquals(120 + 330, $logCurrencyFrees[5]->current_reward_amount);
        $this->assertEquals('unit_test reward2', $logCurrencyFrees[5]->trigger_type);
        $this->assertEquals('reward id2', $logCurrencyFrees[5]->trigger_id);
        $this->assertEquals('reward name2', $logCurrencyFrees[5]->trigger_name);
        $this->assertEquals('reward2', $logCurrencyFrees[5]->trigger_detail);
    }

    #[Test]
    public function insertOrIncrementFreeCurrencies_無償一次通貨の複数追加()
    {
        // Setup
        // 登録済み状態にする
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 10, 11, 12);

        // 登録する内容を作成
        $freeCurrencies = [
            // 最初の一つ
            new FreeCurrencyAddEntity(100, 0, 0, new Trigger('unit_test ingame', 'ingame id', 'ingame name', 'ingame')),
            new FreeCurrencyAddEntity(0, 110, 0, new Trigger('unit_test bonus', 'bonus id', 'bonus name', 'bonus')),
            new FreeCurrencyAddEntity(0, 0, 120, new Trigger('unit_test reward', 'reward id', 'reward name', 'reward')),

            // すでにあるものに加算
            new FreeCurrencyAddEntity(110, 0, 0, new Trigger('unit_test ingame2', 'ingame id2', 'ingame name2', 'ingame2')),
            new FreeCurrencyAddEntity(0, 220, 0, new Trigger('unit_test bonus2', 'bonus id2', 'bonus name2', 'bonus2')),
            new FreeCurrencyAddEntity(0, 0, 330, new Trigger('unit_test reward2', 'reward id2', 'reward name2', 'reward2')),
        ];

        // Exercise
        $this->callMethod(
            $this->currencyService,
            'insertOrIncrementFreeCurrencies',
            [
                '1',
                CurrencyConstants::OS_PLATFORM_IOS,
                $freeCurrencies
            ]
        );

        // Verify
        // 追加されていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(10 + 100 + 110, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(11 + 110 + 220, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(12 + 120 + 330, $usrCurrencyFree->reward_amount);

        // ログの追加確認
        // 登録した順にログが追加されていること
        $logCurrencyFrees = $this->logCurrencyFreeRepository->findByUserId('1');

        $this->assertEquals(6, count($logCurrencyFrees));
        $this->assertEquals('1', $logCurrencyFrees[0]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[0]->os_platform);
        $this->assertEquals(10, $logCurrencyFrees[0]->before_ingame_amount);
        $this->assertEquals(11, $logCurrencyFrees[0]->before_bonus_amount);
        $this->assertEquals(12, $logCurrencyFrees[0]->before_reward_amount);
        $this->assertEquals(100, $logCurrencyFrees[0]->change_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[0]->change_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[0]->change_reward_amount);
        $this->assertEquals(10 + 100, $logCurrencyFrees[0]->current_ingame_amount);
        $this->assertEquals(11, $logCurrencyFrees[0]->current_bonus_amount);
        $this->assertEquals(12, $logCurrencyFrees[0]->current_reward_amount);
        $this->assertEquals('unit_test ingame', $logCurrencyFrees[0]->trigger_type);
        $this->assertEquals('ingame id', $logCurrencyFrees[0]->trigger_id);
        $this->assertEquals('ingame name', $logCurrencyFrees[0]->trigger_name);
        $this->assertEquals('ingame', $logCurrencyFrees[0]->trigger_detail);

        $this->assertEquals('1', $logCurrencyFrees[1]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[1]->os_platform);
        $this->assertEquals(10 + 100, $logCurrencyFrees[1]->before_ingame_amount);
        $this->assertEquals(11, $logCurrencyFrees[1]->before_bonus_amount);
        $this->assertEquals(12, $logCurrencyFrees[1]->before_reward_amount);
        $this->assertEquals(0, $logCurrencyFrees[1]->change_ingame_amount);
        $this->assertEquals(110, $logCurrencyFrees[1]->change_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[1]->change_reward_amount);
        $this->assertEquals(10 + 100, $logCurrencyFrees[1]->current_ingame_amount);
        $this->assertEquals(11 + 110, $logCurrencyFrees[1]->current_bonus_amount);
        $this->assertEquals(12, $logCurrencyFrees[1]->current_reward_amount);
        $this->assertEquals('unit_test bonus', $logCurrencyFrees[1]->trigger_type);
        $this->assertEquals('bonus id', $logCurrencyFrees[1]->trigger_id);
        $this->assertEquals('bonus name', $logCurrencyFrees[1]->trigger_name);
        $this->assertEquals('bonus', $logCurrencyFrees[1]->trigger_detail);

        $this->assertEquals('1', $logCurrencyFrees[2]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[2]->os_platform);
        $this->assertEquals(10 + 100, $logCurrencyFrees[2]->before_ingame_amount);
        $this->assertEquals(11 + 110, $logCurrencyFrees[2]->before_bonus_amount);
        $this->assertEquals(12, $logCurrencyFrees[2]->before_reward_amount);
        $this->assertEquals(0, $logCurrencyFrees[2]->change_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[2]->change_bonus_amount);
        $this->assertEquals(120, $logCurrencyFrees[2]->change_reward_amount);
        $this->assertEquals(10 + 100, $logCurrencyFrees[2]->current_ingame_amount);
        $this->assertEquals(11 + 110, $logCurrencyFrees[2]->current_bonus_amount);
        $this->assertEquals(12 + 120, $logCurrencyFrees[2]->current_reward_amount);
        $this->assertEquals('unit_test reward', $logCurrencyFrees[2]->trigger_type);
        $this->assertEquals('reward id', $logCurrencyFrees[2]->trigger_id);
        $this->assertEquals('reward name', $logCurrencyFrees[2]->trigger_name);
        $this->assertEquals('reward', $logCurrencyFrees[2]->trigger_detail);

        $this->assertEquals('1', $logCurrencyFrees[3]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[3]->os_platform);
        $this->assertEquals(10 + 100, $logCurrencyFrees[3]->before_ingame_amount);
        $this->assertEquals(11 + 110, $logCurrencyFrees[3]->before_bonus_amount);
        $this->assertEquals(12 + 120, $logCurrencyFrees[3]->before_reward_amount);
        $this->assertEquals(110, $logCurrencyFrees[3]->change_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[3]->change_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[3]->change_reward_amount);
        $this->assertEquals(10 + 100 + 110, $logCurrencyFrees[3]->current_ingame_amount);
        $this->assertEquals(11 + 110, $logCurrencyFrees[3]->current_bonus_amount);
        $this->assertEquals(12 + 120, $logCurrencyFrees[3]->current_reward_amount);
        $this->assertEquals('unit_test ingame2', $logCurrencyFrees[3]->trigger_type);
        $this->assertEquals('ingame id2', $logCurrencyFrees[3]->trigger_id);
        $this->assertEquals('ingame name2', $logCurrencyFrees[3]->trigger_name);
        $this->assertEquals('ingame2', $logCurrencyFrees[3]->trigger_detail);

        $this->assertEquals('1', $logCurrencyFrees[4]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[4]->os_platform);
        $this->assertEquals(10 + 100 + 110, $logCurrencyFrees[4]->before_ingame_amount);
        $this->assertEquals(11 + 110, $logCurrencyFrees[4]->before_bonus_amount);
        $this->assertEquals(12 + 120, $logCurrencyFrees[4]->before_reward_amount);
        $this->assertEquals(0, $logCurrencyFrees[4]->change_ingame_amount);
        $this->assertEquals(220, $logCurrencyFrees[4]->change_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[4]->change_reward_amount);
        $this->assertEquals(10 + 100 + 110, $logCurrencyFrees[4]->current_ingame_amount);
        $this->assertEquals(11 + 110 + 220, $logCurrencyFrees[4]->current_bonus_amount);
        $this->assertEquals(12 + 120, $logCurrencyFrees[4]->current_reward_amount);
        $this->assertEquals('unit_test bonus2', $logCurrencyFrees[4]->trigger_type);
        $this->assertEquals('bonus id2', $logCurrencyFrees[4]->trigger_id);
        $this->assertEquals('bonus name2', $logCurrencyFrees[4]->trigger_name);
        $this->assertEquals('bonus2', $logCurrencyFrees[4]->trigger_detail);

        $this->assertEquals('1', $logCurrencyFrees[5]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[5]->os_platform);
        $this->assertEquals(10 + 100 + 110, $logCurrencyFrees[5]->before_ingame_amount);
        $this->assertEquals(11 + 110 + 220, $logCurrencyFrees[5]->before_bonus_amount);
        $this->assertEquals(12 + 120, $logCurrencyFrees[5]->before_reward_amount);
        $this->assertEquals(0, $logCurrencyFrees[5]->change_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[5]->change_bonus_amount);
        $this->assertEquals(330, $logCurrencyFrees[5]->change_reward_amount);
        $this->assertEquals(10 + 100 + 110, $logCurrencyFrees[5]->current_ingame_amount);
        $this->assertEquals(11 + 110 + 220, $logCurrencyFrees[5]->current_bonus_amount);
        $this->assertEquals(12 + 120 + 330, $logCurrencyFrees[5]->current_reward_amount);
        $this->assertEquals('unit_test reward2', $logCurrencyFrees[5]->trigger_type);
        $this->assertEquals('reward id2', $logCurrencyFrees[5]->trigger_id);
        $this->assertEquals('reward name2', $logCurrencyFrees[5]->trigger_name);
        $this->assertEquals('reward2', $logCurrencyFrees[5]->trigger_detail);
    }

    public static function addFreeData()
    {
        return [
            "ingame" => ['ingame', 100, [100, 0, 0]],
            "bonus" => ['bonus', 100, [0, 100, 0]],
            "reward" => ['reward', 100, [0, 0, 100]],
        ];
    }

    #[Test]
    #[DataProvider('addFreeData')]
    public function addFree_無償一次通貨の追加($type, $amount, $expected)
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // ログの削除
        LogCurrencyFree::query()->delete();

        // Exercise
        $this->currencyService->addFree(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            $amount,
            $type,
            new Trigger('unit_test', 'sample id', 'sample name', 'detail')
        );

        // Verify
        // summaryの確認
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals($amount, $usrCurrencySummary->free_amount);

        // freeの確認
        [$expectedIngame, $expectedBonus, $expectedReward] = $expected;
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals($expectedIngame, $usrCurrencyFree->ingame_amount);
        $this->assertEquals($expectedBonus, $usrCurrencyFree->bonus_amount);
        $this->assertEquals($expectedReward, $usrCurrencyFree->reward_amount);

        // ログの確認
        $logs = $this->logCurrencyFreeRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logs->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logs->os_platform);
        $this->assertEquals(0, $logs->before_ingame_amount);
        $this->assertEquals(0, $logs->before_bonus_amount);
        $this->assertEquals(0, $logs->before_reward_amount);
        $this->assertEquals($expectedIngame, $logs->change_ingame_amount);
        $this->assertEquals($expectedBonus, $logs->change_bonus_amount);
        $this->assertEquals($expectedReward, $logs->change_reward_amount);
        $this->assertEquals($expectedIngame, $logs->current_ingame_amount);
        $this->assertEquals($expectedBonus, $logs->current_bonus_amount);
        $this->assertEquals($expectedReward, $logs->current_reward_amount);
        $this->assertEquals('unit_test', $logs->trigger_type);
        $this->assertEquals('sample id', $logs->trigger_id);
        $this->assertEquals('sample name', $logs->trigger_name);
        $this->assertEquals('detail', $logs->trigger_detail);
    }

    #[Test]
    public function addFree_無償一次通貨を追加する際に上限を超えた場合のエラー()
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 999999999);

        // Exercise
        $this->expectException(WpCurrencyAddCurrencyOverByMaxException::class);
        $this->expectExceptionCode(ErrorCode::ADD_CURRENCY_BY_OVER_MAX);
        $this->currencyService->addFree(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            1,
            'ingame',
            new Trigger('unit_test', 'sample id', 'sample name', 'detail')
        );
    }

    #[Test]
    public function addFree_performance_登録パフォーマンスの確認()
    {
        $this->markTestSkipped('パフォーマンス測定のためスキップ 必要に応じて有効化してください');

        // 100件登録して登録時刻を測定する
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);

        // Exercise
        $len = 100;
        $start = microtime(true);
        for ($i = 0; $i < $len; $i++) {
            // 追加する個数をランダム
            $amount = rand(1, 100);
            $type = ['ingame', 'bonus', 'reward'][rand(0, 2)];

            $this->currencyService->addFree(
                '1',
                CurrencyConstants::OS_PLATFORM_IOS,
                $amount,
                $type,
                new Trigger('unit_test', 'sample id', 'sample name', 'detail')
            );
        }
        $end = microtime(true);

        // Verify
        // かかった時刻を出力(ミリ秒で切り捨てて表示)
        print_r("addFree 登録数: $len 登録時間: " . round($end - $start, 3) . "秒
");
        $this->assertTrue(true);
    }

    #[Test]
    public function addFrees_無償一次通貨の複数追加()
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // ログの削除
        LogCurrencyFree::query()->delete();

        // 登録する内容を作成
        $freeCurrencies = [
            // 最初の一つ
            new FreeCurrencyAddEntity(100, 0, 0, new Trigger('unit_test ingame', 'ingame id', 'ingame name', 'ingame')),
            new FreeCurrencyAddEntity(0, 110, 0, new Trigger('unit_test bonus', 'bonus id', 'bonus name', 'bonus')),
            new FreeCurrencyAddEntity(0, 0, 120, new Trigger('unit_test reward', 'reward id', 'reward name', 'reward')),

            // すでにあるものに加算
            new FreeCurrencyAddEntity(110, 0, 0, new Trigger('unit_test ingame2', 'ingame id2', 'ingame name2', 'ingame2')),
            new FreeCurrencyAddEntity(0, 220, 0, new Trigger('unit_test bonus2', 'bonus id2', 'bonus name2', 'bonus2')),
            new FreeCurrencyAddEntity(0, 0, 330, new Trigger('unit_test reward2', 'reward id2', 'reward name2', 'reward2')),
        ];

        // Exercise
        $this->currencyService->addFrees(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            $freeCurrencies
        );

        // Verify
        // summaryの確認
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(100 + 110 + 120 + 110 + 220 + 330, $usrCurrencySummary->free_amount);

        // 追加されていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(100 + 110, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(110 + 220, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(120 + 330, $usrCurrencyFree->reward_amount);

        // ログの追加確認
        // 登録した順にログが追加されていること
        $logCurrencyFrees = $this->logCurrencyFreeRepository->findByUserId('1');

        $this->assertEquals(6, count($logCurrencyFrees));
        $this->assertEquals('1', $logCurrencyFrees[0]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[0]->os_platform);
        $this->assertEquals(0, $logCurrencyFrees[0]->before_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[0]->before_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[0]->before_reward_amount);
        $this->assertEquals(100, $logCurrencyFrees[0]->change_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[0]->change_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[0]->change_reward_amount);
        $this->assertEquals(100, $logCurrencyFrees[0]->current_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[0]->current_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[0]->current_reward_amount);
        $this->assertEquals('unit_test ingame', $logCurrencyFrees[0]->trigger_type);
        $this->assertEquals('ingame id', $logCurrencyFrees[0]->trigger_id);
        $this->assertEquals('ingame name', $logCurrencyFrees[0]->trigger_name);
        $this->assertEquals('ingame', $logCurrencyFrees[0]->trigger_detail);

        $this->assertEquals('1', $logCurrencyFrees[1]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[1]->os_platform);
        $this->assertEquals(100, $logCurrencyFrees[1]->before_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[1]->before_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[1]->before_reward_amount);
        $this->assertEquals(0, $logCurrencyFrees[1]->change_ingame_amount);
        $this->assertEquals(110, $logCurrencyFrees[1]->change_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[1]->change_reward_amount);
        $this->assertEquals(100, $logCurrencyFrees[1]->current_ingame_amount);
        $this->assertEquals(110, $logCurrencyFrees[1]->current_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[1]->current_reward_amount);
        $this->assertEquals('unit_test bonus', $logCurrencyFrees[1]->trigger_type);
        $this->assertEquals('bonus id', $logCurrencyFrees[1]->trigger_id);
        $this->assertEquals('bonus name', $logCurrencyFrees[1]->trigger_name);
        $this->assertEquals('bonus', $logCurrencyFrees[1]->trigger_detail);

        $this->assertEquals('1', $logCurrencyFrees[2]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[2]->os_platform);
        $this->assertEquals(100, $logCurrencyFrees[2]->before_ingame_amount);
        $this->assertEquals(110, $logCurrencyFrees[2]->before_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[2]->before_reward_amount);
        $this->assertEquals(0, $logCurrencyFrees[2]->change_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[2]->change_bonus_amount);
        $this->assertEquals(120, $logCurrencyFrees[2]->change_reward_amount);
        $this->assertEquals(100, $logCurrencyFrees[2]->current_ingame_amount);
        $this->assertEquals(110, $logCurrencyFrees[2]->current_bonus_amount);
        $this->assertEquals(120, $logCurrencyFrees[2]->current_reward_amount);
        $this->assertEquals('unit_test reward', $logCurrencyFrees[2]->trigger_type);
        $this->assertEquals('reward id', $logCurrencyFrees[2]->trigger_id);
        $this->assertEquals('reward name', $logCurrencyFrees[2]->trigger_name);
        $this->assertEquals('reward', $logCurrencyFrees[2]->trigger_detail);

        $this->assertEquals('1', $logCurrencyFrees[3]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[3]->os_platform);
        $this->assertEquals(100, $logCurrencyFrees[3]->before_ingame_amount);
        $this->assertEquals(110, $logCurrencyFrees[3]->before_bonus_amount);
        $this->assertEquals(120, $logCurrencyFrees[3]->before_reward_amount);
        $this->assertEquals(110, $logCurrencyFrees[3]->change_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[3]->change_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[3]->change_reward_amount);
        $this->assertEquals(100 + 110, $logCurrencyFrees[3]->current_ingame_amount);
        $this->assertEquals(110, $logCurrencyFrees[3]->current_bonus_amount);
        $this->assertEquals(120, $logCurrencyFrees[3]->current_reward_amount);
        $this->assertEquals('unit_test ingame2', $logCurrencyFrees[3]->trigger_type);
        $this->assertEquals('ingame id2', $logCurrencyFrees[3]->trigger_id);
        $this->assertEquals('ingame name2', $logCurrencyFrees[3]->trigger_name);
        $this->assertEquals('ingame2', $logCurrencyFrees[3]->trigger_detail);

        $this->assertEquals('1', $logCurrencyFrees[4]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[4]->os_platform);
        $this->assertEquals(100 + 110, $logCurrencyFrees[4]->before_ingame_amount);
        $this->assertEquals(110, $logCurrencyFrees[4]->before_bonus_amount);
        $this->assertEquals(120, $logCurrencyFrees[4]->before_reward_amount);
        $this->assertEquals(0, $logCurrencyFrees[4]->change_ingame_amount);
        $this->assertEquals(220, $logCurrencyFrees[4]->change_bonus_amount);
        $this->assertEquals(0, $logCurrencyFrees[4]->change_reward_amount);
        $this->assertEquals(100 + 110, $logCurrencyFrees[4]->current_ingame_amount);
        $this->assertEquals(110 + 220, $logCurrencyFrees[4]->current_bonus_amount);
        $this->assertEquals(120, $logCurrencyFrees[4]->current_reward_amount);
        $this->assertEquals('unit_test bonus2', $logCurrencyFrees[4]->trigger_type);
        $this->assertEquals('bonus id2', $logCurrencyFrees[4]->trigger_id);
        $this->assertEquals('bonus name2', $logCurrencyFrees[4]->trigger_name);
        $this->assertEquals('bonus2', $logCurrencyFrees[4]->trigger_detail);

        $this->assertEquals('1', $logCurrencyFrees[5]->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFrees[5]->os_platform);
        $this->assertEquals(100 + 110, $logCurrencyFrees[5]->before_ingame_amount);
        $this->assertEquals(110 + 220, $logCurrencyFrees[5]->before_bonus_amount);
        $this->assertEquals(120, $logCurrencyFrees[5]->before_reward_amount);
        $this->assertEquals(0, $logCurrencyFrees[5]->change_ingame_amount);
        $this->assertEquals(0, $logCurrencyFrees[5]->change_bonus_amount);
        $this->assertEquals(330, $logCurrencyFrees[5]->change_reward_amount);
        $this->assertEquals(100 + 110, $logCurrencyFrees[5]->current_ingame_amount);
        $this->assertEquals(110 + 220, $logCurrencyFrees[5]->current_bonus_amount);
        $this->assertEquals(120 + 330, $logCurrencyFrees[5]->current_reward_amount);
        $this->assertEquals('unit_test reward2', $logCurrencyFrees[5]->trigger_type);
        $this->assertEquals('reward id2', $logCurrencyFrees[5]->trigger_id);
        $this->assertEquals('reward name2', $logCurrencyFrees[5]->trigger_name);
        $this->assertEquals('reward2', $logCurrencyFrees[5]->trigger_detail);
    }

    public static function addFreesMaxData()
    {
        // 最大値: 999999999
        return [
            // 初期値がオーバー
            [999999999],
            // ingameでオーバー
            [999999999 - 99],
            // bonusでオーバー
            [999999999 - 100 - 109],
            // rewardでオーバー
            [999999999 - 100 - 110 - 119],
            // 2回目のingameでオーバー
            [999999999 - 100 - 110 - 120 - 109],
            // 2回目のbonusでオーバー
            [999999999 - 100 - 110 - 120 - 110 - 219],
            // 2回目のrewardでオーバー
            [999999999 - 100 - 110 - 120 - 110 - 220 - 329],
        ];
    }

    #[Test]
    #[DataProvider('addFreesMaxData')]
    public function addFrees_無償一次通貨を追加する際に上限を超えた場合のエラー(int $initFreeAmount)
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, $initFreeAmount);
        $freeCurrencies = [
            // 最初の一つ
            new FreeCurrencyAddEntity(100, 0, 0, new Trigger('unit_test ingame', 'ingame id', 'ingame name', 'ingame')),
            new FreeCurrencyAddEntity(0, 110, 0, new Trigger('unit_test bonus', 'bonus id', 'bonus name', 'bonus')),
            new FreeCurrencyAddEntity(0, 0, 120, new Trigger('unit_test reward', 'reward id', 'reward name', 'reward')),

            // すでにあるものに加算
            new FreeCurrencyAddEntity(110, 0, 0, new Trigger('unit_test ingame2', 'ingame id2', 'ingame name2', 'ingame2')),
            new FreeCurrencyAddEntity(0, 220, 0, new Trigger('unit_test bonus2', 'bonus id2', 'bonus name2', 'bonus2')),
            new FreeCurrencyAddEntity(0, 0, 330, new Trigger('unit_test reward2', 'reward id2', 'reward name2', 'reward2')),
        ];

        // Exercise
        $this->expectException(WpCurrencyAddCurrencyOverByMaxException::class);
        $this->expectExceptionCode(ErrorCode::ADD_CURRENCY_BY_OVER_MAX);
        $this->currencyService->addFrees(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            $freeCurrencies
        );
    }

    #[Test]
    public function addFrees_performance_登録パフォーマンスの確認()
    {
        $this->markTestSkipped('パフォーマンス測定のためスキップ 必要に応じて有効化してください');

        // 100件登録して登録時刻を測定する
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);

        // 登録用データを作成(100件)
        $len = 100;
        $freeCurrencies = [];
        foreach (range(1, $len) as $i) {
            $freeCurrencies[] = new FreeCurrencyAddEntity(
                rand(1, 100),
                rand(1, 100),
                rand(1, 100),
                new Trigger('unit_test', 'sample id', 'sample name', 'detail')
            );
        }

        // Exercise
        $start = microtime(true);
        $this->currencyService->addFrees(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            $freeCurrencies
        );
        $end = microtime(true);

        // Verify
        // かかった時刻を出力(ミリ秒で切り捨てて表示)
        print_r("addFrees 登録数: $len 登録時間: " . round($end - $start, 3) . "秒
");
        $this->assertTrue(true);
    }

    #[Test]
    public function useFree_無償一次通貨を消費する()
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 100);
        // 通貨を追加
        $this->currencyService->addFree(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            500,
            'bonus',
            new Trigger('unit_test', 'sample id', 'sample name', 'detail')
        );
        // ログの削除
        LogCurrencyFree::query()->delete();

        // Exercise
        $this->currencyService->useFree(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            'bonus',
            100,
            new Trigger('unit_test', 'sample id', 'sample name', 'detail')
        );

        // Verify
        // summaryの確認
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(500, $usrCurrencySummary->free_amount);

        // freeの確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(400, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);

        // ログの確認
        $logs = $this->logCurrencyFreeRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logs->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logs->os_platform);
        $this->assertEquals(100, $logs->before_ingame_amount);
        $this->assertEquals(500, $logs->before_bonus_amount);
        $this->assertEquals(0, $logs->before_reward_amount);
        $this->assertEquals(0, $logs->change_ingame_amount);
        $this->assertEquals(-100, $logs->change_bonus_amount);
        $this->assertEquals(0, $logs->change_reward_amount);
        $this->assertEquals(100, $logs->current_ingame_amount);
        $this->assertEquals(400, $logs->current_bonus_amount);
        $this->assertEquals(0, $logs->current_reward_amount);
        $this->assertEquals('unit_test', $logs->trigger_type);
        $this->assertEquals('sample id', $logs->trigger_id);
        $this->assertEquals('sample name', $logs->trigger_name);
        $this->assertEquals('detail', $logs->trigger_detail);
    }

    public static function useFreeInternalData()
    {
        return [
            'ingameから引き落とす数量' => [[100, 100, 100], null, 100, 0, [-100, 0, 0], [0, 100, 100]],
            'ingame、rewardから引き落とす数量' => [[100, 100, 100], null, 101, 0, [-100, 0, -1], [0, 100, 99]],
            'ingame、reward、bonusから引き落とす数量' => [[100, 100, 100], null, 201, 0, [-100, -1, -100], [0, 99, 0]],
            'すべて引き落としても不足分がある' => [[100, 100, 100], null, 301, 1, [-100, -100, -100], [0, 0, 0]],
            'ingameがマイナス値になっている場合、reward以降から引き落とす' => [[-100, 100, 100], null, 100, 0, [0, 0, -100], [-100, 100, 0]],
            'ingameがマイナス値になっていて、不足分がある' => [[-100, 100, 100], null, 201, 1, [0, -100, -100], [-100, 0, 0]],
            // typeによる引き落とし
            'ingameのタイプを指定して引き落とす' => [[100, 100, 100], 'ingame', 100, 0, [-100, 0, 0], [0, 100, 100]],
            'imgameのタイプを指定して所持数以上を引き落としマイナスになる' => [[100, 100, 100], 'ingame', 101, 0, [-101, 0, 0], [-1, 100, 100]],
        ];
    }

    #[Test]
    #[DataProvider('useFreeInternalData')]
    public function useFreeInternal_無償一次通貨の消費($initAmount, $type, $amount, $expectedLeftAmount, $expectedChange, $expectedCurrent)
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 各種類の通貨を追加
        $this->currencyService->addFree(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            $initAmount[0],
            'ingame',
            new Trigger('unit_test', 'sample id', 'sample name', 'detail')
        );
        $this->currencyService->addFree(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            $initAmount[1],
            'bonus',
            new Trigger('unit_test', 'sample id', 'sample name', 'detail')
        );
        $this->currencyService->addFree(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            $initAmount[2],
            'reward',
            new Trigger('unit_test', 'sample id', 'sample name', 'detail')
        );
        // ログの削除
        LogCurrencyFree::query()->delete();

        // Exercise
        $actual = $this->callMethod(
            $this->currencyService,
            'useFreeInternal',
            [
                '1',
                CurrencyConstants::OS_PLATFORM_IOS,
                $type,
                $amount,
                new Trigger('unit_test', 'sample id', 'sample name', 'detail')
            ]
        );

        // Verify
        // 引き落としきれなかった残りのamountを照合
        $this->assertEquals($expectedLeftAmount, $actual);

        // 無償一次通貨の確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals($expectedCurrent[0], $usrCurrencyFree->ingame_amount);
        $this->assertEquals($expectedCurrent[1], $usrCurrencyFree->bonus_amount);
        $this->assertEquals($expectedCurrent[2], $usrCurrencyFree->reward_amount);

        // ログの確認
        $log = $this->logCurrencyFreeRepository->findByUserId('1')[0];
        $this->assertEquals('1', $log->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $log->os_platform);
        $this->assertEquals($initAmount[0], $log->before_ingame_amount);
        $this->assertEquals($initAmount[1], $log->before_bonus_amount);
        $this->assertEquals($initAmount[2], $log->before_reward_amount);
        $this->assertEquals($expectedChange[0], $log->change_ingame_amount);
        $this->assertEquals($expectedChange[1], $log->change_bonus_amount);
        $this->assertEquals($expectedChange[2], $log->change_reward_amount);
        $this->assertEquals($expectedCurrent[0], $log->current_ingame_amount);
        $this->assertEquals($expectedCurrent[1], $log->current_bonus_amount);
        $this->assertEquals($expectedCurrent[2], $log->current_reward_amount);
    }

    #[Test]
    public function useFreeInternal_無償一次通貨を所持しておらず引き落としが0のとき()
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // ログの削除
        LogCurrencyFree::query()->delete();

        // Exercise
        $actual = $this->callMethod(
            $this->currencyService,
            'useFreeInternal',
            [
                '1',
                CurrencyConstants::OS_PLATFORM_IOS,
                null,
                100,
                new Trigger('unit_test', 'sample id', 'sample name', 'detail')
            ]
        );

        // Verify
        // 引き落としきれなかった残りのamountを照合
        $this->assertEquals(100, $actual);

        // 無償一次通貨の確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);

        // ログの確認
        // 引き落としが全く行われなかったためログは追加されていない
        $log = $this->logCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(0, count($log));
    }

    #[Test]
    public function useFreeInternal_タイプ指定で引き落とし数が0のとき()
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 100);
        // ログの削除
        LogCurrencyFree::query()->delete();

        // Exercise
        $actual = $this->callMethod(
            $this->currencyService,
            'useFreeInternal',
            [
                '1',
                CurrencyConstants::OS_PLATFORM_IOS,
                FreeCurrencyType::Ingame->value,
                0,
                new Trigger('unit_test', 'sample id', 'sample name', 'detail')
            ]
        );

        // Verify
        // 引き落としきれなかった残りのamountを照合
        $this->assertEquals(0, $actual);

        // 無償一次通貨の確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);

        // ログの確認
        // 引き落としが全く行われなかったためログは追加されていない
        $log = $this->logCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(0, count($log));
    }

    #[Test]
    public function useCurrency_無償一次通貨のみで足りている()
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 100);
        // 有償一次通貨レコードを追加
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        // Exercise
        $this->currencyService->useCurrency('1', CurrencyConstants::OS_PLATFORM_IOS, $billingPlatform, 99, new Trigger('unit_test', '', '', ''));

        // Verify
        // 無償一次通貨の確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(1, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);

        // 有償一次通貨レコードの確認
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findAllByUserIdAndBillingPlatform('1', $billingPlatform);
        $this->assertEquals(1, count($usrCurrencyPaids));
        $usrCurrencyPaid = $usrCurrencyPaids[0];
        $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(100, $usrCurrencyPaid->left_amount);

        // summaryの確認
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(100, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(0, $usrCurrencySummary->paid_amount_google);
        $this->assertEquals(1, $usrCurrencySummary->free_amount);
    }

    #[Test]
    public function useCurrency_無償一次通貨が不足、有償一次通貨で足りる()
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 100);
        // 有償一次通貨レコードを追加
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        // Exercise
        $this->currencyService->useCurrency('1', CurrencyConstants::OS_PLATFORM_IOS, $billingPlatform, 101, new Trigger('unit_test', '', '', ''));

        // Verify
        // 無償一次通貨の確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);

        // 有償一次通貨レコードの確認
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findAllByUserIdAndBillingPlatform('1', $billingPlatform);
        $this->assertEquals(1, count($usrCurrencyPaids));
        $usrCurrencyPaid = $usrCurrencyPaids[0];
        $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(99, $usrCurrencyPaid->left_amount);

        // summaryの確認
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(99, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(0, $usrCurrencySummary->paid_amount_google);
        $this->assertEquals(0, $usrCurrencySummary->free_amount);
    }

    #[Test]
    public function useCurrency_無償一次通貨が不足、有償一次通貨も不足()
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 100);
        // 有償一次通貨レコードを追加
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        // Exercise
        $this->expectException(WpCurrencyException::class);
        $this->expectExceptionCode(ErrorCode::NOT_ENOUGH_CURRENCY);
        try {
            $this->currencyService->useCurrency('1', CurrencyConstants::OS_PLATFORM_IOS, $billingPlatform, 201, new Trigger('unit_test', '', '', ''));
        } catch (\Exception $e) {
            // Verify
            // 値に変更なし
            // 無償一次通貨の確認
            $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
            $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
            $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
            $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
            $this->assertEquals(0, $usrCurrencyFree->reward_amount);

            // 有償一次通貨レコードの確認
            $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findAllByUserIdAndBillingPlatform('1', $billingPlatform);
            $this->assertEquals(1, count($usrCurrencyPaids));
            $usrCurrencyPaid = $usrCurrencyPaids[0];
            $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
            $this->assertEquals(100, $usrCurrencyPaid->left_amount);

            // summaryの確認
            $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
            $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
            $this->assertEquals(100, $usrCurrencySummary->paid_amount_apple);
            $this->assertEquals(0, $usrCurrencySummary->paid_amount_google);
            $this->assertEquals(100, $usrCurrencySummary->free_amount);

            throw $e;
        }
    }

    #[Test]
    public function useCurrency_通貨管理情報が取得できない()
    {
        // Exercise
        $this->expectException(WpCurrencyException::class);
        $this->expectExceptionCode(ErrorCode::NOT_FOUND_CURRENCY_SUMMARY);
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            201,
            new Trigger('unit_test', '', '', '')
        );
    }

    #[Test]
    public function useCurrency_無償一次通貨情報が取得できない()
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 100);
        //  無償一次通貨情報を削除
        UsrCurrencyFree::withTrashed()->where('usr_user_id', '1')->delete();

        // Exercise
        $this->expectException(WpCurrencyException::class);
        $this->expectExceptionCode(ErrorCode::NOT_FOUND_FREE_CURRENCY);
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            99,
            new Trigger('unit_test', '', '', '')
        );
    }

    #[Test]
    public function refreshFreeCurrencySummary_無償一次通貨サマリーの更新()
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);

        // Exercise
        $this->currencyService->refreshFreeCurrencySummary('1');

        // Verify
        // summaryの確認
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(100 + 110 + 120, $usrCurrencySummary->free_amount);
    }

    #[Test]
    public function getCurrencySummary_サマリーの取得()
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);

        // Exercise
        $actual = $this->currencyService->getCurrencySummary('1');

        // Verify
        $this->assertEquals('1', $actual->usr_user_id);
        $this->assertEquals(100, $actual->free_amount);
        $this->assertEquals(0, $actual->paid_amount_apple);
        $this->assertEquals(0, $actual->paid_amount_google);
    }

    #[Test]
    public function getCurrencySummary_サマリーが存在しない()
    {
        // Exercise
        $actual = $this->currencyService->getCurrencySummary('1');

        // Verify
        $this->assertNull($actual);
    }

    #[Test]
    public function softDeleteCurrencyDataByUserId_通貨関連レコードの論理削除()
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);
        // 有償通貨残高も更新
        $this->usrCurrencySummaryRepository->updateCurrencySummaryPaid('1', CurrencyConstants::PLATFORM_APPSTORE, 90);
        $this->usrCurrencySummaryRepository->updateCurrencySummaryPaid('1', CurrencyConstants::PLATFORM_GOOGLEPLAY, 300);
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 有償一次通貨の設定
        $paid1 = $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            100,
            '1.00',
            100,
            '0.01',
            101,
            'USD',
            'dummy receipt 1',
            true
        );
        $paid2 = $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            2,
            10,
            '1.00',
            100,
            '0.01',
            101,
            'USD',
            'dummy receipt 2',
            true
        );

        // Exercise
        $this->callMethod(
            $this->currencyService,
            'softDeleteCurrencyDataByUserId',
            ['1', CurrencyConstants::OS_PLATFORM_IOS]
        );

        // Verify
        // 無償一次通貨が削除されていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertNull($usrCurrencyFree);

        // 有償一次通貨が削除されていること
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findByUserId('1');
        $this->assertEquals(0, count($usrCurrencyPaids));

        // summaryが削除されていること
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertNull($usrCurrencySummary);

        // 削除ログ
        // 無償一次通貨のログ
        $logCurrencyFree = $this->logCurrencyFreeRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logCurrencyFree->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFree->os_platform);
        $this->assertEquals(100, $logCurrencyFree->before_ingame_amount);
        $this->assertEquals(110, $logCurrencyFree->before_bonus_amount);
        $this->assertEquals(120, $logCurrencyFree->before_reward_amount);
        $this->assertEquals(-100, $logCurrencyFree->change_ingame_amount);
        $this->assertEquals(-110, $logCurrencyFree->change_bonus_amount);
        $this->assertEquals(-120, $logCurrencyFree->change_reward_amount);
        $this->assertEquals(0, $logCurrencyFree->current_ingame_amount);
        $this->assertEquals(0, $logCurrencyFree->current_bonus_amount);
        $this->assertEquals(0, $logCurrencyFree->current_reward_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_DELETE_USER, $logCurrencyFree->trigger_type);
        $this->assertEquals('', $logCurrencyFree->trigger_id);
        $this->assertEquals('', $logCurrencyFree->trigger_name);
        $this->assertEquals("soft delete user_id: 1", $logCurrencyFree->trigger_detail);

        // 有償一次通貨のログ
        $logCurrencyPaids = $this->logCurrencyPaidRepository->findByUserId('1');
        // seq_noでソート
        usort($logCurrencyPaids, function ($a, $b) {
            return $a->seq_no <=> $b->seq_no;
        });
        $logCurrencyPaid = $logCurrencyPaids[0];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logCurrencyPaid->billing_platform);
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals(true, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_DELETE, $logCurrencyPaid->query);
        $this->assertEquals('1.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(100, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('0.01000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals('USD', $logCurrencyPaid->currency_code);
        $this->assertEquals(100, $logCurrencyPaid->before_amount);
        $this->assertEquals(-100, $logCurrencyPaid->change_amount);
        $this->assertEquals(0, $logCurrencyPaid->current_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_DELETE_USER, $logCurrencyPaid->trigger_type);
        $this->assertEquals($paid1->id, $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals("soft delete user_id: 1", $logCurrencyPaid->trigger_detail);

        $logCurrencyPaid = $logCurrencyPaids[1];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_ANDROID, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_GOOGLEPLAY, $logCurrencyPaid->billing_platform);
        $this->assertEquals(2, $logCurrencyPaid->seq_no);
        $this->assertEquals(true, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_DELETE, $logCurrencyPaid->query);
        $this->assertEquals('1.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(100, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('0.01000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals('USD', $logCurrencyPaid->currency_code);
        $this->assertEquals(10, $logCurrencyPaid->before_amount);
        $this->assertEquals(-10, $logCurrencyPaid->change_amount);
        $this->assertEquals(0, $logCurrencyPaid->current_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_DELETE_USER, $logCurrencyPaid->trigger_type);
        $this->assertEquals($paid2->id, $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals("soft delete user_id: 1", $logCurrencyPaid->trigger_detail);
    }

    #[Test]
    public function softDeleteCurrencyFreeByUserId_無償一次通貨の削除()
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);

        // Exercise
        $this->callMethod(
            $this->currencyService,
            'softDeleteCurrencyFreeByUserId',
            ['1', CurrencyConstants::OS_PLATFORM_IOS]
        );

        // Verify
        // 無償一次通貨が削除されていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertNull($usrCurrencyFree);

        // 削除されたデータの残高が0になっていること
        $usrCurrencyFree = UsrCurrencyFree::withTrashed()
            ->where('usr_user_id', '1')
            ->first();
        $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);

        // 削除ログが入っていること
        $logCurrencyFree = $this->logCurrencyFreeRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logCurrencyFree->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFree->os_platform);
        $this->assertEquals(100, $logCurrencyFree->before_ingame_amount);
        $this->assertEquals(110, $logCurrencyFree->before_bonus_amount);
        $this->assertEquals(120, $logCurrencyFree->before_reward_amount);
        $this->assertEquals(-100, $logCurrencyFree->change_ingame_amount);
        $this->assertEquals(-110, $logCurrencyFree->change_bonus_amount);
        $this->assertEquals(-120, $logCurrencyFree->change_reward_amount);
        $this->assertEquals(0, $logCurrencyFree->current_ingame_amount);
        $this->assertEquals(0, $logCurrencyFree->current_bonus_amount);
        $this->assertEquals(0, $logCurrencyFree->current_reward_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_DELETE_USER, $logCurrencyFree->trigger_type);
        $this->assertEquals('', $logCurrencyFree->trigger_id);
        $this->assertEquals('', $logCurrencyFree->trigger_name);
        $this->assertEquals("soft delete user_id: 1", $logCurrencyFree->trigger_detail);
    }

    #[Test]
    public function softDeleteCurrencyPaidByUserId_有償一次通貨の削除()
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);
        // 有償一次通貨の設定
        //   残高が0
        $paid1 = $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            0,
            '1.00',
            100,
            '0.01',
            101,
            'USD',
            'dummy receipt 1',
            true
        );
        //   残高がプラス
        $paid2 = $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            2,
            100,
            '1.00',
            100,
            '0.01',
            101,
            'USD',
            'dummy receipt 2',
            true
        );
        //   残高がマイナス
        $paid3 = $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            3,
            -10,
            '1.00',
            100,
            '0.01',
            101,
            'USD',
            'dummy receipt 3',
            true
        );
        // 別のプラットフォームの有償一次通貨
        $paid4 = $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            4,
            10,
            '1.00',
            100,
            '0.01',
            101,
            'USD',
            'dummy receipt 4',
            true
        );

        // Exercise
        $this->callMethod(
            $this->currencyService,
            'softDeleteCurrencyPaidByUserId',
            ['1']
        );

        // Verify
        // 有償一次通貨が削除されていること
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findByUserId('1');
        $this->assertEquals(0, count($usrCurrencyPaids));

        // 削除された有償一次通貨の残高が0になっていること
        $usrCurrencyPaids = UsrCurrencyPaid::withTrashed()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(4, count($usrCurrencyPaids));
        foreach ($usrCurrencyPaids as $usrCurrencyPaid) {
            $this->assertEquals(0, $usrCurrencyPaid->left_amount);
        }

        // 削除ログが入っていること
        $logCurrencyPaids = $this->logCurrencyPaidRepository->findByUserId('1');
        // seq_noでソート
        usort($logCurrencyPaids, function ($a, $b) {
            return $a->seq_no <=> $b->seq_no;
        });
        $logCurrencyPaid = $logCurrencyPaids[0];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logCurrencyPaid->billing_platform);
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals(true, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_DELETE, $logCurrencyPaid->query);
        $this->assertEquals('1.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(100, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('0.01000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals('USD', $logCurrencyPaid->currency_code);
        $this->assertEquals(90, $logCurrencyPaid->before_amount);
        $this->assertEquals(0, $logCurrencyPaid->change_amount);
        $this->assertEquals(90, $logCurrencyPaid->current_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_DELETE_USER, $logCurrencyPaid->trigger_type);
        $this->assertEquals($paid1->id, $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals("soft delete user_id: 1", $logCurrencyPaid->trigger_detail);

        $logCurrencyPaid = $logCurrencyPaids[1];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logCurrencyPaid->billing_platform);
        $this->assertEquals(2, $logCurrencyPaid->seq_no);
        $this->assertEquals(true, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_DELETE, $logCurrencyPaid->query);
        $this->assertEquals('1.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(100, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('0.01000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals('USD', $logCurrencyPaid->currency_code);
        $this->assertEquals(90, $logCurrencyPaid->before_amount);
        $this->assertEquals(-100, $logCurrencyPaid->change_amount);
        $this->assertEquals(-10, $logCurrencyPaid->current_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_DELETE_USER, $logCurrencyPaid->trigger_type);
        $this->assertEquals($paid2->id, $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals("soft delete user_id: 1", $logCurrencyPaid->trigger_detail);

        $logCurrencyPaid = $logCurrencyPaids[2];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logCurrencyPaid->billing_platform);
        $this->assertEquals(3, $logCurrencyPaid->seq_no);
        $this->assertEquals(true, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_DELETE, $logCurrencyPaid->query);
        $this->assertEquals('1.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(100, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('0.01000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals('USD', $logCurrencyPaid->currency_code);
        $this->assertEquals(-10, $logCurrencyPaid->before_amount);
        $this->assertEquals(10, $logCurrencyPaid->change_amount);
        $this->assertEquals(0, $logCurrencyPaid->current_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_DELETE_USER, $logCurrencyPaid->trigger_type);
        $this->assertEquals($paid3->id, $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals("soft delete user_id: 1", $logCurrencyPaid->trigger_detail);

        // 別の課金プラットフォームのログ
        //   before_amountは課金プラットフォーム別に集計する
        $logCurrencyPaid = $logCurrencyPaids[3];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_ANDROID, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_GOOGLEPLAY, $logCurrencyPaid->billing_platform);
        $this->assertEquals(4, $logCurrencyPaid->seq_no);
        $this->assertEquals(true, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_DELETE, $logCurrencyPaid->query);
        $this->assertEquals('1.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(100, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('0.01000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals('USD', $logCurrencyPaid->currency_code);
        $this->assertEquals(10, $logCurrencyPaid->before_amount);
        $this->assertEquals(-10, $logCurrencyPaid->change_amount);
        $this->assertEquals(0, $logCurrencyPaid->current_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_DELETE_USER, $logCurrencyPaid->trigger_type);
        $this->assertEquals($paid4->id, $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals("soft delete user_id: 1", $logCurrencyPaid->trigger_detail);
    }

    #[Test]
    public function softDeleteCurrencySummaryByUserId_通貨管理情報の論理削除()
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);
        // 有償通貨残高も更新
        $this->usrCurrencySummaryRepository->updateCurrencySummaryPaid('1', CurrencyConstants::PLATFORM_APPSTORE, 200);
        $this->usrCurrencySummaryRepository->updateCurrencySummaryPaid('1', CurrencyConstants::PLATFORM_GOOGLEPLAY, 300);

        // Exercise
        $this->callMethod(
            $this->currencyService,
            'softDeleteCurrencySummaryByUserId',
            ['1', CurrencyConstants::OS_PLATFORM_IOS]
        );

        // Verify
        // summaryが取得できないこと
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertNull($usrCurrencySummary);

        // 論理削除されたsummaryの残高が0になっていること
        $usrCurrencySummary = UsrCurrencySummary::withTrashed()
            ->where('usr_user_id', '1')
            ->first();
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(0, $usrCurrencySummary->free_amount);
        $this->assertEquals(0, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(0, $usrCurrencySummary->paid_amount_google);
    }

    #[Test]
    public function softDeleteCurrencyAndBillingDataByUserId_通貨情報と課金情報を論理削除()
    {
        // Setup
        // 通貨関連情報の初期設定
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);
        // 有償通貨残高も更新
        $this->usrCurrencySummaryRepository->updateCurrencySummaryPaid('1', CurrencyConstants::PLATFORM_APPSTORE, 90);
        $this->usrCurrencySummaryRepository->updateCurrencySummaryPaid('1', CurrencyConstants::PLATFORM_GOOGLEPLAY, 300);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 有償一次通貨の設定
        $paid1 = $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            100,
            '1.00',
            100,
            '0.01',
            101,
            'USD',
            'dummy receipt 1',
            true
        );
        $paid2 = $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            2,
            10,
            '1.00',
            100,
            '0.01',
            101,
            'USD',
            'dummy receipt 2',
            true
        );
        // 課金関連情報の初期設定
        //  購入許可情報を登録
        $beforeAllowance = $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product1',
            'device1'
        );

        //  ショップ情報を登録
        $this->usrStoreInfoRepository->upsertStoreInfo('1', 20, 100, '2020-01-01 00:00:00');

        //  購入履歴を登録
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            '1',
            'device1',
            0,
            'unique_id1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'product1',
            'store_product1',
            'mst_product1',
            'JPY',
            'bundle_id1',
            'purchase_token1',
            10,
            0,
            '100.000000',
            '10.00000000',
            101,
            true,
        );


        // Exercise
        $this->currencyService->softDeleteCurrencyAndBillingDataByUserId('1', CurrencyConstants::OS_PLATFORM_IOS);

        // Verify
        // 通貨情報の確認
        // 無償一次通貨が削除されていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertNull($usrCurrencyFree);

        // 有償一次通貨が削除されていること
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findByUserId('1');
        $this->assertEquals(0, count($usrCurrencyPaids));

        // summaryが削除されていること
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertNull($usrCurrencySummary);

        // 削除ログ
        // 無償一次通貨のログ
        $logCurrencyFree = $this->logCurrencyFreeRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logCurrencyFree->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFree->os_platform);
        $this->assertEquals(100, $logCurrencyFree->before_ingame_amount);
        $this->assertEquals(110, $logCurrencyFree->before_bonus_amount);
        $this->assertEquals(120, $logCurrencyFree->before_reward_amount);
        $this->assertEquals(-100, $logCurrencyFree->change_ingame_amount);
        $this->assertEquals(-110, $logCurrencyFree->change_bonus_amount);
        $this->assertEquals(-120, $logCurrencyFree->change_reward_amount);
        $this->assertEquals(0, $logCurrencyFree->current_ingame_amount);
        $this->assertEquals(0, $logCurrencyFree->current_bonus_amount);
        $this->assertEquals(0, $logCurrencyFree->current_reward_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_DELETE_USER, $logCurrencyFree->trigger_type);
        $this->assertEquals('', $logCurrencyFree->trigger_id);
        $this->assertEquals('', $logCurrencyFree->trigger_name);
        $this->assertEquals("soft delete user_id: 1", $logCurrencyFree->trigger_detail);

        // 有償一次通貨のログ
        $logCurrencyPaids = $this->logCurrencyPaidRepository->findByUserId('1');
        // seq_noでソート
        usort($logCurrencyPaids, function ($a, $b) {
            return $a->seq_no <=> $b->seq_no;
        });
        $logCurrencyPaid = $logCurrencyPaids[0];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logCurrencyPaid->billing_platform);
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals(true, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_DELETE, $logCurrencyPaid->query);
        $this->assertEquals('1.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(100, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('0.01000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals('USD', $logCurrencyPaid->currency_code);
        $this->assertEquals(100, $logCurrencyPaid->before_amount);
        $this->assertEquals(-100, $logCurrencyPaid->change_amount);
        $this->assertEquals(0, $logCurrencyPaid->current_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_DELETE_USER, $logCurrencyPaid->trigger_type);
        $this->assertEquals($paid1->id, $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals("soft delete user_id: 1", $logCurrencyPaid->trigger_detail);

        $logCurrencyPaid = $logCurrencyPaids[1];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_ANDROID, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_GOOGLEPLAY, $logCurrencyPaid->billing_platform);
        $this->assertEquals(2, $logCurrencyPaid->seq_no);
        $this->assertEquals(true, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_DELETE, $logCurrencyPaid->query);
        $this->assertEquals('1.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(100, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('0.01000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals('USD', $logCurrencyPaid->currency_code);
        $this->assertEquals(10, $logCurrencyPaid->before_amount);
        $this->assertEquals(-10, $logCurrencyPaid->change_amount);
        $this->assertEquals(0, $logCurrencyPaid->current_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_DELETE_USER, $logCurrencyPaid->trigger_type);
        $this->assertEquals($paid2->id, $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals("soft delete user_id: 1", $logCurrencyPaid->trigger_detail);

        // 課金情報の確認
        //  購入許可情報が削除されていること
        $usrStoreAllowance = $this->billingService->getStoreAllowance('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');
        $this->assertNull($usrStoreAllowance);
        //  ショップ情報が削除されていること
        $usrStoreInfo = $this->billingService->getStoreInfo('1');
        $this->assertNull($usrStoreInfo);
        //  購入履歴が削除されていること
        $usrStoreProductHistory = $this->usrStoreProductHistoryRepository->findByReceiptUniqueIdAndBillingPlatform(
            'unique_id1',
            CurrencyConstants::PLATFORM_APPSTORE
        );
        $this->assertNull($usrStoreProductHistory);

        // allowance削除ログが入っていること
        $logAllowance = $this->logAllowanceRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logAllowance->billing_platform);
        $this->assertEquals('store_product1', $logAllowance->product_id);
        $this->assertEquals('mst_product1', $logAllowance->mst_store_product_id);
        $this->assertEquals('product1', $logAllowance->product_sub_id);
        $this->assertEquals('device1', $logAllowance->device_id);
        $this->assertEquals('delete_user', $logAllowance->trigger_type);
        $this->assertEquals($beforeAllowance->id, $logAllowance->trigger_id);
        $this->assertEquals('', $logAllowance->trigger_name);
        $this->assertEquals("soft delete user_id: 1", $logAllowance->trigger_detail);
    }

    #[Test]
    public function getMaxOwnedCurrencyAmount_変更された値の取得()
    {
        // Setup
        Config::set('wp_currency.store.max_owned_currency_amount', 100);

        // Exercise
        $actual = $this->currencyService->getMaxOwnedCurrencyAmount();

        // Verify
        $this->assertEquals(100, $actual);
    }

    #[Test]
    public function getMaxOwnedCurrencyAmount_一次通貨の最大所持数取得()
    {
        // Exercise
        $actual = $this->currencyService->getMaxOwnedCurrencyAmount();

        // Verify
        $this->assertEquals(999999999, $actual);
    }

    #[Test]
    public function validateAddCurrency_一次通貨追加時のバリデーションで上限チェックされること()
    {
        // Setup
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 999999999, 0);

        // Exercise
        $this->expectExceptionCode(ErrorCode::ADD_CURRENCY_BY_OVER_MAX);
        $this->expectException(WpCurrencyAddCurrencyOverByMaxException::class);
        $this->currencyService->validateAddCurrency('1', 1, 0);
    }

    public static function validateMaxOwnedCurrencyData(): array
    {
        return [
            // 有償一次通貨の付与
            'Apple有償一次通貨を付与し、Apple、Google、無償合わせて上限を超えない' => [1, 0, [333333333 - 1, 333333333, 333333333], true],
            'Apple有償一次通貨を付与し、Apple、Google、無償合わせて上限を超える' => [1, 0, [333333333, 333333333, 333333333], false],
            'Google有償一次通貨を保持していない場合、Apple有償一次通貨を付与し、Apple、無償合わせて上限を超えない' => [1, 0, [499999999 - 1, 0, 500000000], true],
            'Google有償一次通貨を保持していない場合、Apple有償一次通貨を付与し、Apple、無償合わせて上限を超える' => [1, 0, [499999999, 0, 500000000], false],
            '無償通過を保持していない場合、Apple有償一次通貨を付与し、Apple、Google合わせて上限を超えない' => [1, 0, [499999999 - 1, 500000000, 0], true],
            '無償通過を保持していない場合、Apple有償一次通貨を付与し、Apple、Google合わせて上限を超える' => [1, 0, [499999999, 500000000, 0], false],
            'Goole有償一次通貨、無償通過を保持していない場合、Apple有償一次通貨を付与し、Apple合わせて上限を超えない' => [1, 0, [999999999 - 1, 0, 0], true],
            'Goole有償一次通貨、無償通過を保持していない場合、Apple有償一次通貨を付与し、Apple合わせて上限を超える' => [1, 0, [999999999, 0, 0], false],
            'Apple有償一次通貨をマイナス付与し、Apple、Google、無償合わせて上限を超えない' => [-1, 0, [333333333, 333333333, 333333333], true],
            'Apple有償一次通貨をマイナス付与し、Apple、Google、無償合わせて上限を超えない(もともと上限を超えている)' => [-1, 0, [333333333 + 1, 333333333, 333333333], true],
            'Apple有償一次通貨をマイナス付与し、Apple、Google、無償合わせて上限を超える(もともと上限を超えている)' => [-1, 0, [333333333 + 2, 333333333, 333333333], false],
            // 無償一次通貨の付与
            '無償一次通貨を付与し、Apple、Google、無償合わせて上限を超えない' => [0, 1, [333333333, 333333333 - 1, 333333333], true],
            '無償一次通貨を付与し、Apple、Google、無償合わせて上限を超える' => [0, 1, [333333333, 333333333, 333333333], false],
            'Google有償一次通貨を保持していない場合、無償一次通貨を付与し、Apple、無償合わせて上限を超えない' => [0, 1, [499999999 - 1, 0, 500000000], true],
            'Google有償一次通貨を保持していない場合、無償一次通貨を付与し、Apple、無償合わせて上限を超える' => [0, 1, [499999999, 0, 500000000], false],
            'Goole、Apple有償一次通貨を保持していない場合、無償一次通貨を付与し、上限を超えない' => [0, 1, [0, 0, 999999999 - 1], true],
            'Goole、Apple有償一次通貨を保持していない場合、無償一次通貨を付与し、上限を超える' => [0, 1, [0, 0, 999999999], false],
            '無償一次通貨をマイナス付与し、Apple、Google、無償合わせて上限を超えない' => [0, -1, [333333333, 333333333, 333333333], true],
            '無償一次通貨をマイナス付与し、Apple、Google、無償合わせて上限を超えない(もともと上限を超えている)' => [0, -1, [333333333, 333333333 + 1, 333333333], true],
            '無償一次通貨をマイナス付与し、Apple、Google、無償合わせて上限を超える(もともと上限を超えている)' => [0, -1, [333333333, 333333333 + 2, 333333333], false],
            // 有償、無償一次通貨の同時付与
            'Apple優勝一次通貨と無償一次通貨を同時に付与し、Apple、Google、無償合わせて上限を超えない' => [1, 1, [333333333 - 2, 333333333, 333333333], true],
            'Apple優勝一次通貨と無償一次通貨を同時に付与し、Apple、Google、無償合わせて上限を超える' => [1, 1, [333333333 - 1, 333333333, 333333333], false],
            'Apple優勝一次通貨と無償一次通貨を同時にマイナス付与し、Apple、Google、無償合わせて上限を超えない' => [-1, -1, [333333333, 333333333, 333333333], true],
            'Apple優勝一次通貨と無償一次通貨を同時にマイナス付与し、Apple、Google、無償合わせて上限を超えない(もともと上限を超えている)' => [-1, -1, [333333333 + 2, 333333333, 333333333], true],
            'Apple優勝一次通貨と無償一次通貨を同時にマイナス付与し、Apple、Google、無償合わせて上限を超える(もともと上限を超えている)' => [-1, -1, [333333333 + 3, 333333333, 333333333], false],
            // 付与数が0
            '付与数が0の場合、もともとの所持数が上限を超えていない' => [0, 0, [333333333, 333333333, 333333333], true],
            '付与数が0の場合、もともとの所持数が上限を超えている' => [0, 0, [333333334, 333333333, 333333333], false],
        ];
    }

    #[Test]
    #[DataProvider('validateMaxOwnedCurrencyData')]
    public function validateMaxOwnedCurrency_一次通貨所持上限の最大数確認(
        int $addPaidAmount,
        int $addFreeAmount,
        array $summaryAmount,
        bool $expectedSuccess
    ) {
        // Setup
        //  summaryの初期値を設定
        [$summaryPaidAmountGoogle, $summaryPaidAmountApple, $summaryFreeAmount] = $summaryAmount;
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', $summaryFreeAmount, 0);
        $this->usrCurrencySummaryRepository->updateCurrencySummaryPaid('1', CurrencyConstants::PLATFORM_APPSTORE, $summaryPaidAmountApple);
        $this->usrCurrencySummaryRepository->updateCurrencySummaryPaid('1', CurrencyConstants::PLATFORM_GOOGLEPLAY, $summaryPaidAmountGoogle);

        // Exercise
        if (!$expectedSuccess) {
            // 失敗したら例外が発生するので、それを検知する
            $this->expectExceptionCode(ErrorCode::ADD_CURRENCY_BY_OVER_MAX);
            $this->expectException(WpCurrencyAddCurrencyOverByMaxException::class);
        }
        $this->callMethod(
            $this->currencyService,
            'validateMaxOwnedCurrency',
            [
                '1',
                $addPaidAmount,
                $addFreeAmount
            ]
        );

        // Verify
        //   例外が発生せず通過したらOK
        $this->assertTrue(true);
    }

    #[Test]
    public function validateMaxOwnedCurrency_一次通貨所持上限を無制限に設定()
    {
        // Setup
        Config::set('wp_currency.store.max_owned_currency_amount', -1);
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 999999999, 0);

        // Exercise
        $this->callMethod(
            $this->currencyService,
            'validateMaxOwnedCurrency',
            ['1', 1, 0]
        );

        // Verify
        //   例外が発生せず通過したらOK
        $this->assertTrue(true);
    }

    public static function validateMaxOwnedCurrency_上限チェックを有償と無償で別々に行うData(): array
    {
        return [
            // 無償通貨の上限チェック
            '成功 無償を付与して、有償と無償の合算なら上限を超えるが、無償の上限は超えない' => [
                'maxTotalAmount' => 999,
                'maxFreeAmount' => 999,
                'maxPaidAmount' => 999,
                'addPaidAmount' => 0,
                'addFreeAmount' => 100,
                'summaryPaidAmountGoogle' => 998,
                'summaryPaidAmountApple' => 0,
                'summaryFreeAmount' => 0,
                'expectedErrorCode' => null,
                'expectedExceptionClass' => null,
            ],
            '失敗 無償を付与して、無償の上限超える' => [
                'maxTotalAmount' => 999,
                'maxFreeAmount' => 999,
                'maxPaidAmount' => 999,
                'addPaidAmount' => 0,
                'addFreeAmount' => 999,
                'summaryPaidAmountGoogle' => 0,
                'summaryPaidAmountApple' => 0,
                'summaryFreeAmount' => 100,
                'expectedErrorCode' => ErrorCode::ADD_FREE_CURRENCY_BY_OVER_MAX,
                'expectedExceptionClass' => WpCurrencyAddFreeCurrencyOverByMaxException::class,
            ],
            // 有償通貨の上限チェック
            '成功 有償を付与して、有償と無償の合算なら上限を超えるが、有償の上限は超えない' => [
                'maxTotalAmount' => 999,
                'maxFreeAmount' => 999,
                'maxPaidAmount' => 999,
                'addPaidAmount' => 100,
                'addFreeAmount' => 0,
                'summaryPaidAmountGoogle' => 0,
                'summaryPaidAmountApple' => 0,
                'summaryFreeAmount' => 998,
                'expectedErrorCode' => null,
                'expectedExceptionClass' => null,
            ],
            '失敗 有償を付与して、有償の上限(Google)超える' => [
                'maxTotalAmount' => 999,
                'maxFreeAmount' => 999,
                'maxPaidAmount' => 999,
                'addPaidAmount' => 999,
                'addFreeAmount' => 0,
                'summaryPaidAmountGoogle' => 100,
                'summaryPaidAmountApple' => 0,
                'summaryFreeAmount' => 0,
                'expectedErrorCode' => ErrorCode::ADD_PAID_CURRENCY_BY_OVER_MAX,
                'expectedExceptionClass' => WpCurrencyAddPaidCurrencyOverByMaxException::class,
            ],
            '失敗 有償を付与して、有償の上限(Apple)超える' => [
                'maxTotalAmount' => 999,
                'maxFreeAmount' => 999,
                'maxPaidAmount' => 999,
                'addPaidAmount' => 999,
                'addFreeAmount' => 0,
                'summaryPaidAmountGoogle' => 0,
                'summaryPaidAmountApple' => 100,
                'summaryFreeAmount' => 0,
                'expectedErrorCode' => ErrorCode::ADD_PAID_CURRENCY_BY_OVER_MAX,
                'expectedExceptionClass' => WpCurrencyAddPaidCurrencyOverByMaxException::class,
            ],
        ];
    }

    #[Test]
    #[DataProvider('validateMaxOwnedCurrency_上限チェックを有償と無償で別々に行うData')]
    public function validateMaxOwnedCurrency_上限チェックを有償と無償で別々に行う(
        int $maxTotalAmount,
        int $maxFreeAmount,
        int $maxPaidAmount,
        int $addPaidAmount,
        int $addFreeAmount,
        int $summaryPaidAmountGoogle,
        int $summaryPaidAmountApple,
        int $summaryFreeAmount,
        ?int $expectedErrorCode,
        ?string $expectedExceptionClass,
    ): void{
        // Setup
        Config::set('wp_currency.store.separate_currency_limit_check', true);

        Config::set('wp_currency.store.max_owned_currency_amount', $maxTotalAmount);

        Config::set('wp_currency.store.max_owned_free_currency_amount', $maxFreeAmount);
        Config::set('wp_currency.store.max_owned_paid_currency_amount', $maxPaidAmount);

        //  summaryの初期値を設定
        $userId = 'user1';
        $this->usrCurrencySummaryRepository->insertCurrencySummary($userId, $summaryFreeAmount);
        $this->usrCurrencySummaryRepository->updateCurrencySummaryPaid($userId, CurrencyConstants::PLATFORM_APPSTORE, $summaryPaidAmountApple);
        $this->usrCurrencySummaryRepository->updateCurrencySummaryPaid($userId, CurrencyConstants::PLATFORM_GOOGLEPLAY, $summaryPaidAmountGoogle);

        // Exercise
        if ($expectedErrorCode !== null) {
            // 失敗したら例外が発生するので、それを検知する
            $this->expectExceptionCode($expectedErrorCode);
            $this->expectException($expectedExceptionClass);
        }

        $this->callMethod(
            $this->currencyService,
            'validateMaxOwnedCurrency',
            [
                $userId,
                $addPaidAmount,
                $addFreeAmount,
            ]
        );

        // Verify
        //   例外が発生せず通過したらOK
        $this->assertTrue(true);
    }

    public static function isMaxOwnedCurrencyAmountUnlimitedData(): array
    {
        return [
            '無制限に設定されている' => [-1, true],
            '一次通貨上限が設定されている' => [100, false],
        ];
    }

    #[Test]
    #[DataProvider('isMaxOwnedCurrencyAmountUnlimitedData')]
    public function isMaxOwnedCurrencyAmountUnlimited_一次通貨上限の設定確認(
        int $maxOwnedCurrencyAmount,
        bool $expected
    ) {
        // Setup
        Config::set('wp_currency.store.max_owned_currency_amount', $maxOwnedCurrencyAmount);

        // Exercise
        $actual = $this->currencyService->isMaxOwnedCurrencyAmountUnlimited();

        // Verify
        $this->assertEquals($expected, $actual);
    }

    #[Test]
    public function getCurrencyFree_無償一次通貨内訳の取得()
    {
        // Setup
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);

        // Exercise
        $actual = $this->currencyService->getCurrencyFree('1');

        // Verify
        $this->assertEquals(100, $actual->ingame_amount);
        $this->assertEquals(110, $actual->bonus_amount);
        $this->assertEquals(120, $actual->reward_amount);
    }

    #[Test]
    public function getCurrencyFree_存在しない無償一次通貨内訳の取得()
    {
        // Setup
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);

        // Exercise
        $actual = $this->currencyService->getCurrencyFree('2');

        // Verify
        $this->assertNull($actual);
    }
}
