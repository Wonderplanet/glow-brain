<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Entities\LogAllowanceAutoInsertEntity;
use WonderPlanet\Domain\Billing\Entities\NullAllowanceCallbackEntity;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Billing\Models\LogCloseStoreTransaction;
use WonderPlanet\Domain\Billing\Repositories\LogAllowanceRepository;
use WonderPlanet\Domain\Billing\Repositories\LogStoreRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreAllowanceRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreInfoRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Billing\Services\BillingService;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Constants\ErrorCode as CurrencyErrorCode;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;
use WonderPlanet\Domain\Currency\Models\MstStoreProduct;
use WonderPlanet\Domain\Currency\Models\OprProduct;
use WonderPlanet\Domain\Currency\Repositories\MstStoreProductRepository;
use WonderPlanet\Domain\Currency\Repositories\OprProductRepository;
use WonderPlanet\Domain\Currency\Services\CurrencyService;
use WonderPlanet\Tests\Traits\Domain\Currency\DataFixtureTrait;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;
    use DataFixtureTrait;
    use FakeStoreReceiptTrait;

    private BillingService $billingService;
    private CurrencyService $currencyService;
    private UsrStoreAllowanceRepository $usrStoreAllowanceRepository;
    private UsrStoreInfoRepository $usrStoreInfoRepository;
    private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository;
    private MstStoreProductRepository $mstStoreProductRepository;
    private OprProductRepository $oprProductRepository;
    private LogStoreRepository $logStoreRepository;
    private LogAllowanceRepository $logAllowanceRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->usrStoreAllowanceRepository = $this->app->make(UsrStoreAllowanceRepository::class);
        $this->billingService = $this->app->make(BillingService::class);
        $this->currencyService = $this->app->make(CurrencyService::class);
        $this->usrStoreInfoRepository = $this->app->make(UsrStoreInfoRepository::class);
        $this->usrStoreProductHistoryRepository = $this->app->make(UsrStoreProductHistoryRepository::class);
        $this->mstStoreProductRepository = $this->app->make(MstStoreProductRepository::class);
        $this->oprProductRepository = $this->app->make(OprProductRepository::class);
        $this->logStoreRepository = $this->app->make(LogStoreRepository::class);
        $this->logAllowanceRepository = $this->app->make(LogAllowanceRepository::class);
    }

    #[Test]
    public function allowedToPurchase_許可情報を登録する()
    {
        // Setup
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);

        // Exercise
        $this->billingService->allowedToPurchase(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'product1',
            'device1',
            'testTriggerDetail'
        );

        // Verify
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findByUserIdAndProductId('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');
        $this->assertEquals('1', $usrStoreAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrStoreAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrStoreAllowance->billing_platform);
        $this->assertEquals('store_product1', $usrStoreAllowance->product_id);
        $this->assertEquals('mst_product1', $usrStoreAllowance->mst_store_product_id);
        $this->assertEquals('product1', $usrStoreAllowance->product_sub_id);
        $this->assertEquals('device1', $usrStoreAllowance->device_id);

        // ログが追加されていること
        $logAllowance = $this->logAllowanceRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logAllowance->billing_platform);
        $this->assertEquals('store_product1', $logAllowance->product_id);
        $this->assertEquals('mst_product1', $logAllowance->mst_store_product_id);
        $this->assertEquals('product1', $logAllowance->product_sub_id);
        $this->assertEquals('device1', $logAllowance->device_id);
        $this->assertEquals('insert', $logAllowance->trigger_type);
        $this->assertEquals($usrStoreAllowance->id, $logAllowance->trigger_id);
        $this->assertEquals('testTriggerDetail', $logAllowance->trigger_detail);
    }

    #[Test]
    public function allowedToPurchase_すでに許可情報がある場合に削除して登録する()
    {
        // Setup
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 許可情報を先に登録する
        $beforeStoreAllowance = $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product1',
            'device1',
        );
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);
        $this->insertOptProduct('product2', 0, 'mst_product1', 10);
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');

        // Exercise
        $usrStoreAllowance = $this->billingService->allowedToPurchase(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'product2',
            'device1'
        );
        $expectedId = $usrStoreAllowance->id;

        // Verify
        // 後から登録したIDのレコードになっている
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findByUserIdAndProductId('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');
        $this->assertEquals($expectedId, $usrStoreAllowance->id);
        $this->assertEquals('1', $usrStoreAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrStoreAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrStoreAllowance->billing_platform);
        $this->assertEquals('store_product1', $usrStoreAllowance->product_id);
        $this->assertEquals('mst_product1', $usrStoreAllowance->mst_store_product_id);
        $this->assertEquals('product2', $usrStoreAllowance->product_sub_id);
        $this->assertEquals('device1', $usrStoreAllowance->device_id);

        // 前のIDのレコードは存在しない
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findById($beforeStoreAllowance->id);
        $this->assertNull($usrStoreAllowance);

        // 前の許可レコードを削除したログがある
        $logAllowances = $this->logAllowanceRepository->findByUserId('1');
        $this->assertCount(2, $logAllowances);
        $logAllowance = array_values(array_filter($logAllowances, function ($log) {
            return $log->trigger_type === 'delete';
        }))[0];
        $this->assertEquals('1', $logAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logAllowance->billing_platform);
        $this->assertEquals('store_product1', $logAllowance->product_id);
        $this->assertEquals('mst_product1', $logAllowance->mst_store_product_id);
        $this->assertEquals('product1', $logAllowance->product_sub_id);
        $this->assertEquals('device1', $logAllowance->device_id);
        $this->assertEquals('delete', $logAllowance->trigger_type);

        // 今回の許可レコードを登録したログがある
        $logAllowance = array_values(array_filter($logAllowances, function ($log) {
            return $log->trigger_type === 'insert';
        }))[0];
        $this->assertEquals('1', $logAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logAllowance->billing_platform);
        $this->assertEquals('store_product1', $logAllowance->product_id);
        $this->assertEquals('mst_product1', $logAllowance->mst_store_product_id);
        $this->assertEquals('product2', $logAllowance->product_sub_id);
        $this->assertEquals('device1', $logAllowance->device_id);
        $this->assertEquals('insert', $logAllowance->trigger_type);
    }

    #[Test]
    public function allowedToPurchase_storeProductIdが異なる情報は登録できる()
    {
        // Setup
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 許可情報を先に登録する
        $beforeStoreAllowance = $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product1',
            'device1'
        );
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);
        $this->insertOptProduct('product2', 0, 'mst_product2', 10);
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');
        $this->insertMstStoreProduct('mst_product2', 0, 'store_product2', 'android_product_id2');

        // Exercise
        // 登録してある許可情報と異なるproductSubIdで登録する
        $usrStoreAllowance = $this->billingService->allowedToPurchase(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product2',
            'product2',
            'device1'
        );
        $product2Id = $usrStoreAllowance->id;

        // Verify
        // 登録したproductSubId別のレコードになっている
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findByUserIdAndProductId('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');
        $this->assertEquals($beforeStoreAllowance->id, $usrStoreAllowance->id);

        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findByUserIdAndProductId('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product2');
        $this->assertEquals($product2Id, $usrStoreAllowance->id);
    }

    #[Test]
    public function allowedToPurchase_oprProductIdのマスタが存在しない()
    {
        // Setup
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product_sub_id1',
            'device1'
        );

        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionCode(ErrorCode::OPR_PRODUCT_NOT_FOUND);
        $this->billingService->allowedToPurchase(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'product2',
            'device1'
        );
    }

    #[Test]
    public function allowedToPurchase_mstStoreProductIdのマスタが存在しない()
    {
        // Setup
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product_sub_id1',
            'device1'
        );
        $this->insertOptProduct('product_sub_id1', 0, 'mst_product1', 10);

        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionCode(ErrorCode::MST_STORE_PRODUCT_NOT_FOUND);
        $this->billingService->allowedToPurchase(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'product_sub_id1',
            'device1'
        );
    }

    #[Test]
    public function allowedToPurchase_productIdが一致しない()
    {
        // Setup
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product_sub_id1',
            'device1'
        );
        $this->insertOptProduct('product_sub_id1', 0, 'mst_product1', 10);
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');

        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionCode(ErrorCode::ALLOWANCE_AND_MST_STORE_PRODUCT_NOT_MATCH);
        $this->billingService->allowedToPurchase(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product2',
            'product_sub_id1',
            'device1'
        );
    }

    #[Test]
    public function allowedToPurchase_一次通貨所持上限超過()
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 999999999);
        // マスタデータ登録
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');

        // Exercise
        // 一次通貨所持上限を超えるため例外発生
        $this->expectExceptionCode(CurrencyErrorCode::ADD_CURRENCY_BY_OVER_MAX);
        $this->expectException(WpCurrencyAddCurrencyOverByMaxException::class);
        $this->billingService->allowedToPurchase(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'product1',
            'device1'
        );
    }

    #[Test]
    public function insertAllowanceAndLog_正常登録(): void
    {
        $this->billingService->insertAllowanceAndLog(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product',
            'mst_store_product',
            'product_sub_id',
            'device',
            'test'
        );

        // Verify
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findByUserIdAndProductId('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product');
        $this->assertEquals('1', $usrStoreAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrStoreAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrStoreAllowance->billing_platform);
        $this->assertEquals('store_product', $usrStoreAllowance->product_id);
        $this->assertEquals('mst_store_product', $usrStoreAllowance->mst_store_product_id);
        $this->assertEquals('product_sub_id', $usrStoreAllowance->product_sub_id);
        $this->assertEquals('device', $usrStoreAllowance->device_id);

        // ログが追加されていること
        $logAllowance = $this->logAllowanceRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logAllowance->billing_platform);
        $this->assertEquals('store_product', $logAllowance->product_id);
        $this->assertEquals('mst_store_product', $logAllowance->mst_store_product_id);
        $this->assertEquals('product_sub_id', $logAllowance->product_sub_id);
        $this->assertEquals('device', $logAllowance->device_id);
        $this->assertEquals('insert', $logAllowance->trigger_type);
        $this->assertEquals($usrStoreAllowance->id, $logAllowance->trigger_id);
        $this->assertEquals('test', $logAllowance->trigger_detail);
    }

    public static function getAllowanceIdData()
    {
        return [
            "レコードが存在している" => [true, '1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product1'],
            "ユーザーIDが違うため存在していない" => [false, '2', CurrencyConstants::PLATFORM_APPSTORE, 'store_product1'],
            "プラットフォームが違うため存在していない" => [false, '1', CurrencyConstants::PLATFORM_GOOGLEPLAY, 'store_product1'],
            "store_product_idがallowandeと違うため存在していない" => [false, '1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product2'],
        ];
    }

    #[Test]
    #[DataProvider('getAllowanceIdData')]
    public function getStoreAllowance_許可情報が存在する(
        bool $expected,
        string $userId,
        string $billingPlatform,
        string $storeProductId,
    ) {
        // Setup
        // 許可情報を先に登録する
        $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product1',
            'device1'
        );
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);

        // Exercise
        $result = $this->billingService->getStoreAllowance($userId, $billingPlatform, $storeProductId);

        // Verify
        //  存在していれば$resultにIDが入ってきているので、それを照合する
        $this->assertEquals($expected, !empty($result));
    }

    #[Test]
    #[DataProvider('getAllowanceIdData')]
    public function getOrCreateStoreAllowance_許可情報が存在する(
        bool $expected,
        string $userId,
        string $billingPlatform,
        string $storeProductId,
    ) {
        // Setup
        // 許可情報を先に登録する
        $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product1',
            'device1'
        );
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);

        // Exercise
        $result = $this->billingService->getOrCreateStoreAllowance($userId, $billingPlatform, $storeProductId);

        // Verify
        //  存在していれば$resultにIDが入ってきているので、それを照合する
        $this->assertEquals($expected, !empty($result));
    }

    #[Test]
    public function getOrCreateStoreAllowance_allowanceが取得できコールバックに違うIDを指定したとき() {
        // Setup
        $user = $this->createUsrUser();
        $userId = $user->getId();
        $deviceId = $userId . ' device';
        // 許可情報を先に登録する
        $this->usrStoreAllowanceRepository->insertStoreAllowance(
            $userId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product1',
            $deviceId
        );
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);

        // Exercise
        $result = $this->billingService->getOrCreateStoreAllowance(
            $userId,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            function (): NullAllowanceCallbackEntity {
                return new NullAllowanceCallbackEntity(
                    CurrencyConstants::OS_PLATFORM_IOS,
                    'store_product99',
                    'product99',
                    'device99',
                    ['info' => 'test99']
                );
            }
        );

        // Verify
        //  存在していれば$resultにIDが入ってきているので、それを照合する
        $this->assertEquals(true, !empty($result));
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findByUserIdAndProductId($userId, CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');
        $this->assertEquals($userId, $usrStoreAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrStoreAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrStoreAllowance->billing_platform);
        $this->assertEquals('store_product1', $usrStoreAllowance->product_id);
        $this->assertEquals('mst_product1', $usrStoreAllowance->mst_store_product_id);
        $this->assertEquals('product1', $usrStoreAllowance->product_sub_id);
        $this->assertEquals($deviceId, $usrStoreAllowance->device_id);
    }

    #[Test]
    public function getOrCreateStoreAllowance_allowanceが取得できコールバックがnullのとき() {
        // Setup
        $user = $this->createUsrUser();
        $userId = $user->getId();
        // 許可情報を先に登録する
        $this->usrStoreAllowanceRepository->insertStoreAllowance(
            $userId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product1',
            'device1'
        );
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);

        // Exercise
        $result = $this->billingService->getOrCreateStoreAllowance(
            $userId, 
            CurrencyConstants::PLATFORM_APPSTORE, 
            'store_product1',
            function () { return null; }
        );

        // Verify
        //  存在していれば$resultにIDが入ってきているので、それを照合する
        $this->assertEquals(true, !empty($result));
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findByUserIdAndProductId($userId, CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');
        $this->assertEquals($userId, $usrStoreAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrStoreAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrStoreAllowance->billing_platform);
        $this->assertEquals('store_product1', $usrStoreAllowance->product_id);
        $this->assertEquals('mst_product1', $usrStoreAllowance->mst_store_product_id);
        $this->assertEquals('product1', $usrStoreAllowance->product_sub_id);
        $this->assertEquals('device1', $usrStoreAllowance->device_id);
    }

    #[Test]
    public function getOrCreateStoreAllowance_allowanceがnullでコールバックから自動挿入() {
        // Setup
        $user = $this->createUsrUser();
        $userId = $user->getId();
        $deviceId = $userId . ' device';
        $this->currencyService->registerCurrencySummary($userId, CurrencyConstants::OS_PLATFORM_IOS, 0);
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');

        // Exercise
        $this->billingService->getOrCreateStoreAllowance(
            $userId,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            function () use (
                $deviceId,
            ): NullAllowanceCallbackEntity {
                return new NullAllowanceCallbackEntity(
                    CurrencyConstants::OS_PLATFORM_IOS,
                    'store_product1',
                    'product1',
                    $deviceId,
                    ['info' => 'auto_insert']
                );
            }
        );

        // Verify
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findByUserIdAndProductId(
            $userId,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1'
        );
        $this->assertEquals($userId, $usrStoreAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrStoreAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrStoreAllowance->billing_platform);
        $this->assertEquals('store_product1', $usrStoreAllowance->product_id);
        $this->assertEquals('mst_product1', $usrStoreAllowance->mst_store_product_id);
        $this->assertEquals('product1', $usrStoreAllowance->product_sub_id);
        $this->assertEquals($deviceId, $usrStoreAllowance->device_id);
        // ログが追加されていること
        $logAllowance = $this->logAllowanceRepository->findByUserId($userId)[0];
        $logAllowanceAutoInsertEntity = new LogAllowanceAutoInsertEntity(
            'auto_insert',
            'store_product1',
            ['info' => 'auto_insert'],
        );
        $this->assertEquals($logAllowanceAutoInsertEntity->toDetail(), $logAllowance->trigger_detail);
    }

    #[Test]
    public function getOrCreateStoreAllowance_allowanceがnullでコールバックが異なるProductIdを指定したとき() {
        // Setup
        $user = $this->createUsrUser();
        $userId = $user->getId();
        $deviceId = $userId . ' device';
        $this->currencyService->registerCurrencySummary($userId, CurrencyConstants::OS_PLATFORM_IOS, 0);
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');

        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionCode(ErrorCode::OPR_PRODUCT_NOT_FOUND);
        $this->billingService->getOrCreateStoreAllowance(
            $userId,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product2',
            function () use (
                $deviceId,
            ): NullAllowanceCallbackEntity {
                return new NullAllowanceCallbackEntity(
                    CurrencyConstants::OS_PLATFORM_IOS,
                    'store_product2',
                    'product2',
                    $deviceId,
                );
            }
        );

        // Verify
        $this->assertTrue(false);
    }

    #[Test]
    public function getOrCreateStoreAllowance_allowanceがnullでコールバックなし() {
        // Setup
        $user = $this->createUsrUser();
        $userId = $user->getId();
        $this->currencyService->registerCurrencySummary($userId, CurrencyConstants::OS_PLATFORM_IOS, 0);
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');
        // Exercise
        $return = $this->billingService->getOrCreateStoreAllowance($userId, CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');
        // Verify
        $this->assertEquals(null, $return);
    }

    #[Test]
    public function getOrCreateStoreAllowance_allowanceがnullでコールバックの返り値nullのとき() {
        // Setup
        $user = $this->createUsrUser();
        $userId = $user->getId();
        $this->currencyService->registerCurrencySummary($userId, CurrencyConstants::OS_PLATFORM_IOS, 0);
        $this->insertOptProduct('product1', 0, 'mst_product1', 10);
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');
        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_ALLOWANCE);
        $this->billingService->getOrCreateStoreAllowance(
            $userId,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            function () { return null; }
        );
        // Verify
        $this->assertTrue(false);
    }

    #[Test]
    public function verifyReceipt_レシートを検証する()
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
        // 各種パラメータ
        $paidAmount = 10;
        $rawPriceString = '¥100';
        $purchasePrice = '100.000000';
        $vipPoint = 101;
        $currencyCode = 'JPY';
        // VIPポイントの集計のため、レシートはサンドボックスでないものを使用する
        $receipt = $this->makeFakeStoreReceiptNoSandbox('store_product1');
        $receiptUniqueId = $receipt->getUnitqueId();
        $loggingProductSubName = 'product1 name';

        //  コールバック実行の確認用フラグ
        $actualCallbackFlg = false;
        // 配布するマスタデータを作成
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');
        // ユーザーの所持情報を登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 購入許可情報を登録
        $this->insertOptProduct('product1', 0, 'mst_product1', $paidAmount);
        $this->billingService->allowedToPurchase(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'product1',
            'device1'
        );
        $allowance = $this->billingService->getStoreAllowance('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');
        // ストア情報を登録
        $this->billingService->setStoreInfo('1', 20, '2020-01-01 00:00:00');
        // VerifyのためにallowedToPurchaseで入ったログを消しておく
        $this->logAllowanceRepository->deleteAllByUserId('1');

        // Exercise
        $this->billingService->purchased(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'device1',
            $allowance,
            $purchasePrice,
            $rawPriceString,
            $vipPoint,
            $currencyCode,
            $receipt,
            new Trigger('purchased', 'product1', 'trigger product1 name', 'sample details'),
            $loggingProductSubName,
            function () use (&$actualCallbackFlg) {
                // 更新を呼び出し元の変数に反映するため、参照渡しとしている
                $actualCallbackFlg = true;
            }
        );

        // Verify
        //  購入した数だけ所持数が増えていること
        $currencySummary = $this->currencyService->getCurrencySummary('1');
        $this->assertEquals($paidAmount, $currencySummary->paid_amount_apple);

        //  paidの管理レコードが追加されていること
        $usrCurrencyPaid = $this->currencyService->getCurrencyPaidAll('1')[0];
        $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $usrCurrencyPaid->seq_no);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrCurrencyPaid->billing_platform);
        $this->assertEquals($paidAmount, $usrCurrencyPaid->left_amount);
        $this->assertEquals($purchasePrice, $usrCurrencyPaid->purchase_price);
        $this->assertEquals($paidAmount, $usrCurrencyPaid->purchase_amount);
        $this->assertEquals('10.00000000', $usrCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $usrCurrencyPaid->vip_point);
        $this->assertEquals($currencyCode, $usrCurrencyPaid->currency_code);
        $this->assertEquals($receiptUniqueId, $usrCurrencyPaid->receipt_unique_id);
        $this->assertEquals(false, $usrCurrencyPaid->is_sandbox);

        //  コールバックが動作していること
        $this->assertTrue($actualCallbackFlg);

        // 購入許可情報が削除されていること
        $afterAllowance = $this->billingService->getStoreAllowance('1', CurrencyConstants::PLATFORM_APPSTORE, 'product1');
        $this->assertNull($afterAllowance);

        // ストア情報の累計購入額に加算されていること
        $usrStoreInfo = $this->billingService->getStoreInfo('1');
        $this->assertEquals('100.000000', $usrStoreInfo->paid_price);
        $this->assertEquals(101, $usrStoreInfo->total_vip_point);

        // ストア購入履歴が登録されていること
        $usrStoreProductHistory = $this->usrStoreProductHistoryRepository->findByReceiptUniqueIdAndBillingPlatform(
            $receipt->getUnitqueId(),
            CurrencyConstants::PLATFORM_APPSTORE
        );
        $this->assertEquals('1', $usrStoreProductHistory->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrStoreProductHistory->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrStoreProductHistory->billing_platform);
        $this->assertEquals('product1', $usrStoreProductHistory->product_sub_id);
        $this->assertEquals('store_product1', $usrStoreProductHistory->platform_product_id);
        $this->assertEquals('mst_product1', $usrStoreProductHistory->mst_store_product_id);
        $this->assertEquals('JPY', $usrStoreProductHistory->currency_code);
        $this->assertEquals('100.000000', $usrStoreProductHistory->purchase_price);
        $this->assertEquals('10.00000000', $usrStoreProductHistory->price_per_amount);
        $this->assertEquals(101, $usrStoreProductHistory->vip_point);
        $this->assertEquals('device1', $usrStoreProductHistory->device_id);
        $this->assertEquals(20, $usrStoreProductHistory->age);

        // ログが追加されていること
        // log_store
        $logStore = $this->logStoreRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logStore->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logStore->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logStore->billing_platform);
        $this->assertEquals('device1', $logStore->device_id);
        $this->assertEquals(20, $logStore->age);
        $this->assertEquals('store_product1', $logStore->platform_product_id);
        $this->assertEquals('mst_product1', $logStore->mst_store_product_id);
        $this->assertEquals('product1', $logStore->product_sub_id);
        $this->assertEquals('product1 name', $logStore->product_sub_name);
        $this->assertEquals('100.000000', $logStore->purchase_price);
        $this->assertEquals('10.00000000', $logStore->price_per_amount);
        $this->assertEquals(101, $logStore->vip_point);
        $this->assertEquals('JPY', $logStore->currency_code);
        $this->assertEquals($receiptUniqueId, $logStore->receipt_unique_id);
        $this->assertEquals($receipt->getBundleId(), $logStore->receipt_bundle_id);
        $this->assertEquals($receipt->getReceipt(), $logStore->raw_receipt);
        $this->assertEquals($rawPriceString, $logStore->raw_price_string);
        $this->assertEquals($paidAmount, $logStore->paid_amount);
        $this->assertEquals(0, $logStore->free_amount);
        $this->assertEquals('purchased', $logStore->trigger_type);
        $this->assertEquals('product1', $logStore->trigger_id);
        $this->assertEquals('trigger product1 name', $logStore->trigger_name);
        $this->assertEquals('sample details', $logStore->trigger_detail);

        // log_allowance
        $logAllowance = $this->logAllowanceRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logAllowance->billing_platform);
        $this->assertEquals('store_product1', $logAllowance->product_id);
        $this->assertEquals('mst_product1', $logAllowance->mst_store_product_id);
        $this->assertEquals('product1', $logAllowance->product_sub_id);
        $this->assertEquals('device1', $logAllowance->device_id);
        $this->assertEquals('delete', $logAllowance->trigger_type);
        $this->assertEquals($allowance->id, $logAllowance->trigger_id);
        $this->assertEquals('', $logAllowance->trigger_name);
        $this->assertEquals('', $logAllowance->trigger_detail);

        // log_currency_paids
        $logCurrencyPaid = LogCurrencyPaid::query()->where('usr_user_id', '1')->first();
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logCurrencyPaid->billing_platform);
        $this->assertEquals($usrCurrencyPaid->id, $logCurrencyPaid->currency_paid_id);
        $this->assertEquals($receiptUniqueId, $logCurrencyPaid->receipt_unique_id);
        $this->assertEquals(false, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_INSERT, $logCurrencyPaid->query);
        $this->assertEquals($purchasePrice, $logCurrencyPaid->purchase_price);
        $this->assertEquals($paidAmount, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('10.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals($currencyCode, $logCurrencyPaid->currency_code);
        $this->assertEquals(0, $logCurrencyPaid->before_amount);
        $this->assertEquals($paidAmount, $logCurrencyPaid->change_amount);
        $this->assertEquals($paidAmount, $logCurrencyPaid->current_amount);
        $this->assertEquals('purchased', $logCurrencyPaid->trigger_type);
        $this->assertEquals('product1', $logCurrencyPaid->trigger_id);
        $this->assertEquals('trigger product1 name', $logCurrencyPaid->trigger_name);
        $this->assertEquals('sample details', $logCurrencyPaid->trigger_detail);
    }

    #[Test]
    public function purchased_有償通貨配布個数が0の商品を購入する()
    {
        // Setup
        // 各種パラメータ
        // 有償通貨の配布数0個の商品にする (パスなど有償通貨を伴わない商品)
        $paidAmount = 0;
        $rawPriceString = '¥100';
        $purchasePrice = '100.000000';
        $vipPoint = 101;
        $currencyCode = 'JPY';
        $receipt = $this->makeFakeStoreReceipt('store_product1');
        $receiptUniqueId = $receipt->getUnitqueId();
        $loggingProductSubName = 'product1 name';

        //  コールバック実行の確認用フラグ
        $actualCallbackFlg = false;
        // 配布するマスタデータを作成
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');
        // ユーザーの所持情報を登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 購入許可情報を登録
        $this->insertOptProduct('product1', 0, 'mst_product1', $paidAmount);
        $this->billingService->allowedToPurchase(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'product1',
            'device1'
        );
        $allowance = $this->billingService->getStoreAllowance('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');
        // ストア情報を登録
        $this->billingService->setStoreInfo('1', 20, '2020-01-01 00:00:00');
        // VerifyのためにallowedToPurchaseで入ったログを消しておく
        $this->logAllowanceRepository->deleteAllByUserId('1');

        // Exercise
        $this->billingService->purchased(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'device1',
            $allowance,
            $purchasePrice,
            $rawPriceString,
            $vipPoint,
            $currencyCode,
            $receipt,
            new Trigger('purchased', 'product1', 'trigger product1 name', 'sample details'),
            $loggingProductSubName,
            function () use (&$actualCallbackFlg) {
                // 更新を呼び出し元の変数に反映するため、参照渡しとしている
                $actualCallbackFlg = true;
            }
        );

        // Verify
        //  有償一次通貨は0のままであること
        $currencySummary = $this->currencyService->getCurrencySummary('1');
        $this->assertEquals(0, $currencySummary->paid_amount_apple);

        //  paidの管理レコードが追加されていること
        $usrCurrencyPaid = $this->currencyService->getCurrencyPaidAll('1')[0];
        $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrCurrencyPaid->billing_platform);
        $this->assertEquals($paidAmount, $usrCurrencyPaid->left_amount);
        $this->assertEquals($purchasePrice, $usrCurrencyPaid->purchase_price);
        $this->assertEquals($paidAmount, $usrCurrencyPaid->purchase_amount);
        $this->assertEquals('0.00000000', $usrCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $usrCurrencyPaid->vip_point);
        $this->assertEquals($currencyCode, $usrCurrencyPaid->currency_code);
        $this->assertEquals($receiptUniqueId, $usrCurrencyPaid->receipt_unique_id);
        $this->assertEquals(true, $usrCurrencyPaid->is_sandbox);

        //  コールバックが動作していること
        $this->assertTrue($actualCallbackFlg);

        // 購入許可情報が削除されていること
        $afterAllowance = $this->billingService->getStoreAllowance('1', CurrencyConstants::PLATFORM_APPSTORE, 'product1');
        $this->assertNull($afterAllowance);

        // ストア情報の累計購入額に加算されていること
        $usrStoreInfo = $this->billingService->getStoreInfo('1');
        $this->assertEquals('100.000000', $usrStoreInfo->paid_price);

        // ストア購入履歴が登録されていること
        $usrStoreProductHistory = $this->usrStoreProductHistoryRepository->findByReceiptUniqueIdAndBillingPlatform(
            $receipt->getUnitqueId(),
            CurrencyConstants::PLATFORM_APPSTORE
        );
        $this->assertEquals('1', $usrStoreProductHistory->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrStoreProductHistory->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrStoreProductHistory->billing_platform);
        $this->assertEquals('product1', $usrStoreProductHistory->product_sub_id);
        $this->assertEquals('store_product1', $usrStoreProductHistory->platform_product_id);
        $this->assertEquals('mst_product1', $usrStoreProductHistory->mst_store_product_id);
        $this->assertEquals('JPY', $usrStoreProductHistory->currency_code);
        $this->assertEquals('100.000000', $usrStoreProductHistory->purchase_price);
        $this->assertEquals('0.00000000', $usrStoreProductHistory->price_per_amount);
        $this->assertEquals(101, $usrStoreProductHistory->vip_point);
        $this->assertEquals('device1', $usrStoreProductHistory->device_id);
        $this->assertEquals(20, $usrStoreProductHistory->age);
        $this->assertEquals(0, $usrStoreProductHistory->paid_amount);
        $this->assertEquals(0, $usrStoreProductHistory->free_amount);

        // ログが追加されていること
        // log_store
        $logStore = $this->logStoreRepository->findByUserId('1')[0];
        $this->assertEquals(1, $logStore->seq_no);
        $this->assertEquals('1', $logStore->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logStore->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logStore->billing_platform);
        $this->assertEquals('device1', $logStore->device_id);
        $this->assertEquals(20, $logStore->age);
        $this->assertEquals('store_product1', $logStore->platform_product_id);
        $this->assertEquals('mst_product1', $logStore->mst_store_product_id);
        $this->assertEquals('product1', $logStore->product_sub_id);
        $this->assertEquals('product1 name', $logStore->product_sub_name);
        $this->assertEquals('100.000000', $logStore->purchase_price);
        $this->assertEquals('0.00000000', $logStore->price_per_amount);
        $this->assertEquals(101, $logStore->vip_point);
        $this->assertEquals('JPY', $logStore->currency_code);
        $this->assertEquals($receiptUniqueId, $logStore->receipt_unique_id);
        $this->assertEquals($receipt->getBundleId(), $logStore->receipt_bundle_id);
        $this->assertEquals($receipt->getReceipt(), $logStore->raw_receipt);
        $this->assertEquals($rawPriceString, $logStore->raw_price_string);
        $this->assertEquals($paidAmount, $logStore->paid_amount);
        $this->assertEquals(0, $logStore->free_amount);
        $this->assertEquals('purchased', $logStore->trigger_type);
        $this->assertEquals('product1', $logStore->trigger_id);
        $this->assertEquals('trigger product1 name', $logStore->trigger_name);
        $this->assertEquals('sample details', $logStore->trigger_detail);

        // log_allowance
        $logAllowance = $this->logAllowanceRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logAllowance->billing_platform);
        $this->assertEquals('store_product1', $logAllowance->product_id);
        $this->assertEquals('mst_product1', $logAllowance->mst_store_product_id);
        $this->assertEquals('product1', $logAllowance->product_sub_id);
        $this->assertEquals('device1', $logAllowance->device_id);
        $this->assertEquals('delete', $logAllowance->trigger_type);
        $this->assertEquals($allowance->id, $logAllowance->trigger_id);
        $this->assertEquals('', $logAllowance->trigger_name);
        $this->assertEquals('', $logAllowance->trigger_detail);

        // log_currency_paids
        $logCurrencyPaid = LogCurrencyPaid::query()->where('usr_user_id', '1')->first();
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logCurrencyPaid->billing_platform);
        $this->assertEquals($usrCurrencyPaid->id, $logCurrencyPaid->currency_paid_id);
        $this->assertEquals($receiptUniqueId, $logCurrencyPaid->receipt_unique_id);
        $this->assertEquals(true, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_INSERT, $logCurrencyPaid->query);
        $this->assertEquals($purchasePrice, $logCurrencyPaid->purchase_price);
        $this->assertEquals($paidAmount, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('0.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals($currencyCode, $logCurrencyPaid->currency_code);
        $this->assertEquals(0, $logCurrencyPaid->before_amount);
        $this->assertEquals($paidAmount, $logCurrencyPaid->change_amount);
        $this->assertEquals($paidAmount, $logCurrencyPaid->current_amount);
        $this->assertEquals('purchased', $logCurrencyPaid->trigger_type);
        $this->assertEquals('product1', $logCurrencyPaid->trigger_id);
        $this->assertEquals('trigger product1 name', $logCurrencyPaid->trigger_name);
        $this->assertEquals('sample details', $logCurrencyPaid->trigger_detail);
    }

    #[Test]
    public function purchased_VIPポイントが加算されること()
    {
        // Setup
        // 各種パラメータ
        $paidAmount = 10;
        $rawPriceString = '¥100';
        $purchasePrice = '100.000000';
        $vipPoint = 101;
        $currencyCode = 'JPY';
        // レシートはサンドボックスではないものを用意する
        $receipt = $this->makeFakeStoreReceiptNoSandbox('store_product1');
        $loggingProductSubName = 'product1 name';

        // 配布するマスタデータを作成
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');
        // ユーザーの所持情報を登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 購入許可情報を登録
        $this->insertOptProduct('product1', 0, 'mst_product1', $paidAmount);
        $this->billingService->allowedToPurchase(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'product1',
            'device1'
        );
        $allowance = $this->billingService->getStoreAllowance('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');
        // ストア情報を登録
        $this->billingService->setStoreInfo('1', 20, '2020-01-01 00:00:00');
        // VerifyのためにallowedToPurchaseで入ったログを消しておく
        $this->logAllowanceRepository->deleteAllByUserId('1');
        // 合算のため、購入済みの履歴を追加
        //   サンドボックスではない購入履歴として登録する
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            '1',
            'device1',
            20,
            'before_receipt_unique_id',
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
        $this->billingService->purchased(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'device1',
            $allowance,
            $purchasePrice,
            $rawPriceString,
            $vipPoint,
            $currencyCode,
            $receipt,
            new Trigger('purchased', 'product1', 'trigger product1 name', 'sample details'),
            $loggingProductSubName,
            function () {
            }
        );

        // Verify
        // ストア情報のVIPポイントが加算されていること
        $usrStoreInfo = $this->billingService->getStoreInfo('1');
        $this->assertEquals(201, $usrStoreInfo->total_vip_point);
    }


    #[Test]
    #[DataProvider('purchasedIncrementPaidPriceData')]
    public function purchased_incrementPaidPriceのチェック(
        string $currencyCode,
        ?string $renotifyAt,
        int $expected
    ): void {
        // 下記パターンだとusr_store_infoが更新されないのでそのチェック
        //  通貨コードがBillingPurchaseTrait::ADD_PAID_CURRENCY_CODES(JPY)以外
        //  usr_sotre_info.renotify_atがnull

        // Setup
        // 各種パラメータ
        $userId = '1';
        $paidAmount = 10;
        $rawPriceString = '¥100';
        $purchasePrice = '100.000000';
        $vipPoint = 101;
        // レシートはサンドボックスではないものを用意する
        $receipt = $this->makeFakeStoreReceiptNoSandbox('store_product1');
        $loggingProductSubName = 'product1 name';

        // 配布するマスタデータを作成
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');
        // ユーザーの所持情報を登録
        $this->currencyService->registerCurrencySummary($userId, CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 購入許可情報を登録
        $this->insertOptProduct('product1', 0, 'mst_product1', $paidAmount);
        $this->billingService->allowedToPurchase(
            $userId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'product1',
            'device1'
        );
        $allowance = $this->billingService->getStoreAllowance($userId, CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');
        // ストア情報を登録
        $this->billingService->setStoreInfo($userId, 20, $renotifyAt);
        // VerifyのためにallowedToPurchaseで入ったログを消しておく
        $this->logAllowanceRepository->deleteAllByUserId($userId);
        // 合算のため、購入済みの履歴を追加
        //   サンドボックスではない購入履歴として登録する
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            $userId,
            'device1',
            20,
            'before_receipt_unique_id',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'product1',
            'store_product1',
            'mst_product1',
            $currencyCode,
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
        $this->billingService->purchased(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'device1',
            $allowance,
            $purchasePrice,
            $rawPriceString,
            $vipPoint,
            $currencyCode,
            $receipt,
            new Trigger('purchased', 'product1', 'trigger product1 name', 'sample details'),
            $loggingProductSubName,
            function () {
            }
        );

        // Verify
        //  usr_sotre_info.paid_priceに絞ってチェックする
        $usrStoreInfo = $this->billingService->getStoreInfo($userId);
        $this->assertEquals($expected, $usrStoreInfo->paid_price);
    }

    /**
     * @return array
     */
    public static function purchasedIncrementPaidPriceData(): array
    {
        return [
            // $currencyCode, $renotifyAt, $expected
            '更新する' => ['JPY', '2024-01-01 00:00:00', 100],
            '更新しない 年齢確認日がnull' => ['JPY', null, 0],
            '更新しない 通貨コードが国外' => ['USD', '2024-01-01 00:00:00', 0],
        ];
    }

    #[Test]
    public function purchased_usr_store_infoがなくてエラー(): void
    {
        // Setup
        // 各種パラメータ
        $userId = '1';
        $paidAmount = 10;
        $rawPriceString = '¥100';
        $purchasePrice = '100.000000';
        $vipPoint = 101;
        // レシートはサンドボックスではないものを用意する
        $receipt = $this->makeFakeStoreReceiptNoSandbox('store_product1');
        $loggingProductSubName = 'product1 name';

        // 配布するマスタデータを作成
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');
        // ユーザーの所持情報を登録
        $this->currencyService->registerCurrencySummary($userId, CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 購入許可情報を登録
        $this->insertOptProduct('product1', 0, 'mst_product1', $paidAmount);
        $this->billingService->allowedToPurchase(
            $userId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'product1',
            'device1'
        );
        $allowance = $this->billingService->getStoreAllowance($userId, CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');

        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('Billing-1: usr_store_info not found');
        $this->billingService->purchased(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'device1',
            $allowance,
            $purchasePrice,
            $rawPriceString,
            $vipPoint,
            'JPY',
            $receipt,
            new Trigger('purchased', 'product1', 'trigger product1 name', 'sample details'),
            $loggingProductSubName,
            function () {
            }
        );
    }

    #[Test]
    public function purchased_購入時に一次通貨の上限を超えた場合のエラー()
    {
        // Setup
        // 各種パラメータ
        $paidAmount = 1;
        $rawPriceString = '¥100';
        $purchasePrice = '100.000000';
        $vipPoint = 101;
        $currencyCode = 'JPY';
        // VIPポイントの集計のため、レシートはサンドボックスでないものを使用する
        $receipt = $this->makeFakeStoreReceiptNoSandbox('store_product1');
        $loggingProductSubName = 'product1 name';

        //  コールバック実行の確認用フラグ
        $actualCallbackFlg = false;
        // 配布するマスタデータを作成
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');
        // ユーザーの所持情報を登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 購入許可情報を登録
        $this->insertOptProduct('product1', 0, 'mst_product1', $paidAmount);
        $this->billingService->allowedToPurchase(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'product1',
            'device1'
        );
        $allowance = $this->billingService->getStoreAllowance('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');
        // ストア情報を登録
        $this->billingService->setStoreInfo('1', 20, '2020-01-01 00:00:00');
        // VerifyのためにallowedToPurchaseで入ったログを消しておく
        $this->logAllowanceRepository->deleteAllByUserId('1');
        // allowanceを通過した後で、一次通貨を超えるようにする
        $this->currencyService->addFree(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            999999999,
            CurrencyConstants::FREE_CURRENCY_TYPE_INGAME,
            new Trigger('test', 'test', 'test', 'test')
        );

        // Exercise
        // 一次通貨所持上限を超えるため例外発生
        $this->expectExceptionCode(CurrencyErrorCode::ADD_CURRENCY_BY_OVER_MAX);
        $this->expectException(WpCurrencyAddCurrencyOverByMaxException::class);
        $this->billingService->purchased(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'device1',
            $allowance,
            $purchasePrice,
            $rawPriceString,
            $vipPoint,
            $currencyCode,
            $receipt,
            new Trigger('purchased', 'product1', 'trigger product1 name', 'sample details'),
            $loggingProductSubName,
            function () use (&$actualCallbackFlg) {
                // 更新を呼び出し元の変数に反映するため、参照渡しとしている
                $actualCallbackFlg = true;
            }
        );
    }

    public static function verifyPurchaseStoreProductData()
    {
        return [
            '正常に検証完了' => [
                ['mst_store_product_id1', 'ios_product_id1', 'android_product_id1'],
                ['opr_product1', 'mst_store_product_id1'],
                CurrencyConstants::PLATFORM_APPSTORE,
                'ios_product_id1',
                null,
            ],
            'mst_store_productとproduct_idが不整合' => [
                ['mst_store_product_id1', 'ios_product_id2', 'android_product_id1'],
                ['opr_product1', 'mst_store_product_id1'],
                CurrencyConstants::PLATFORM_APPSTORE,
                'ios_product_id1',
                ErrorCode::ALLOWANCE_AND_MST_STORE_PRODUCT_NOT_MATCH,
            ],
            'mst_store_productとbillingPlatformのproduct_idが不整合' => [
                ['mst_store_product_id1', 'ios_product_id1', 'android_product_id1'],
                ['opr_product1', 'mst_store_product_id1'],
                CurrencyConstants::PLATFORM_APPSTORE,
                'android_product_id1',
                ErrorCode::ALLOWANCE_AND_MST_STORE_PRODUCT_NOT_MATCH,
            ],
            'mst_store_productとopr_productが不整合' => [
                ['mst_store_product_id1', 'ios_product_id1', 'android_product_id1'],
                ['opr_product1', 'mst_store_product_id2'],
                CurrencyConstants::PLATFORM_APPSTORE,
                'ios_product_id1',
                ErrorCode::ALLOWANCE_AND_OPR_PRODUCT_NOT_MATCH,
            ],
            'mst_store_preocutがnull' => [
                null,
                ['opr_product1', 'mst_store_product_id1'],
                CurrencyConstants::PLATFORM_APPSTORE,
                'ios_product_id1',
                ErrorCode::MST_STORE_PRODUCT_NOT_FOUND,
            ],
            'opr_productがnull' => [
                ['mst_store_product_id1', 'ios_product_id1', 'android_product_id1'],
                null,
                CurrencyConstants::PLATFORM_APPSTORE,
                'ios_product_id1',
                ErrorCode::OPR_PRODUCT_NOT_FOUND,
            ],
        ];
    }
    #[Test]
    #[DataProvider('verifyPurchaseStoreProductData')]
    public function verifyPurchaseStoreProduct_マスタのストアデータを検証(
        ?array $mstStoreProductData,
        ?array $oprProductData,
        string $billingPlatform,
        string $productId,
        ?int $expectedErrorCode,
    ) {
        // Setup
        $mstStoreProduct = (is_null($mstStoreProductData)) ? null :
            MstStoreProduct::factory()->make(
                [
                    'id' => $mstStoreProductData[0],
                    'product_id_ios' => $mstStoreProductData[1],
                    'product_id_android' => $mstStoreProductData[2],
                ]
            );
        $oprProduct = (is_null($oprProductData)) ? null :
            OprProduct::factory()->make(
                [
                    'id' => $oprProductData[0],
                    'mst_store_product_id' => $oprProductData[1],
                ]
            );
        // expectedErrorCodeがnullでなければ発生する例外を設定
        if (!is_null($expectedErrorCode)) {
            $this->expectExceptionCode($expectedErrorCode);
        }

        // Exercise
        $this->callMethod(
            $this->billingService,
            'verifyPurchaseStoreProduct',
            [
                $billingPlatform,
                $productId,
                $mstStoreProduct,
                $oprProduct
            ]
        );

        // Verify
        // 例外が発生しなければOK
        $this->assertTrue(true);
    }


    #[Test]
    public function getStoreInfo_ショップ情報を取得()
    {
        // Setup
        $this->usrStoreInfoRepository->upsertStoreInfo('1', 20, 100, '2020-01-01 00:00:00');

        // Exercise
        $usrStoreInfo = $this->billingService->getStoreInfo('1');

        // Verify
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(20, $usrStoreInfo->age);
        $this->assertEquals('100.000000', $usrStoreInfo->paid_price);
        $this->assertEquals('2020-01-01 00:00:00', $usrStoreInfo->renotify_at);
    }

    #[Test]
    public function getStoreInfo_ショップ情報が存在しない()
    {
        // Exercise
        $usrStoreInfo = $this->billingService->getStoreInfo('1');

        // Verify
        $this->assertNull($usrStoreInfo);
    }

    #[Test]
    public function setStoreInfo_ショップ情報を登録()
    {
        // Exercise
        $usrStoreInfo = $this->billingService->setStoreInfo('1', 20, '2020-01-01 00:00:00');

        // Verify
        // 戻り値を確認
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(20, $usrStoreInfo->age);
        $this->assertEquals(0, $usrStoreInfo->paid_price);
        $this->assertEquals('2020-01-01 00:00:00', $usrStoreInfo->renotify_at);

        // データが更新されていることを確認
        $usrStoreInfo = $this->billingService->getStoreInfo('1');
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(20, $usrStoreInfo->age);
        $this->assertEquals('0.000000', $usrStoreInfo->paid_price);
        $this->assertEquals('2020-01-01 00:00:00', $usrStoreInfo->renotify_at);
    }

    #[Test]
    public function setStoreInfo_ショップ情報を更新()
    {
        // Setup
        //  paid_priceを0以外にしておく
        $this->billingService->setStoreInfo('1', 20, '2020-01-01 00:00:00');
        $this->usrStoreInfoRepository->incrementPaidPrice('1', '100');

        // Exercise
        $usrStoreInfo = $this->billingService->setStoreInfo('1', 21, '2020-02-01 00:00:00');

        // Verify
        // 戻り値を確認
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(21, $usrStoreInfo->age);
        $this->assertEquals(0, $usrStoreInfo->paid_price);
        $this->assertEquals('2020-02-01 00:00:00', $usrStoreInfo->renotify_at);

        // データが更新されていることを確認
        $usrStoreInfo = $this->billingService->getStoreInfo('1');
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(21, $usrStoreInfo->age);
        $this->assertEquals('0.000000', $usrStoreInfo->paid_price);
        $this->assertEquals('2020-02-01 00:00:00', $usrStoreInfo->renotify_at);
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
        $result = $this->billingService->hasStoreProductHistory('1');

        // Verify
        $this->assertTrue($result);
    }

    #[Test]
    public function forceClosePurchase_購入トランザクション終了後処理確認()
    {
        // Setup
        // 各種パラメータ
        $user = $this->createUsrUser();
        $userId = $user->getId();
        $deviceId = $userId . ' device';
        $paidAmount = 10;
        $rawPriceString = '¥100';
        $purchasePrice = '100.000000';
        $currencyCode = 'JPY';
        $receipt = $this->makeFakeStoreReceiptNoSandbox('android_product_id1');
        $receiptUniqueId = $receipt->getUnitqueId();
        $loggingProductSubName = 'product1 name';

        //  コールバック実行の確認用フラグ
        $actualCallbackFlg = false;
        // 配布するマスタデータを作成
        $this->insertMstStoreProduct('mst_product1', 0, 'store_product1', 'android_product_id1');
        // ユーザーの所持情報を登録
        $this->currencyService->registerCurrencySummary($userId, CurrencyConstants::OS_PLATFORM_ANDROID, 0);
        // 購入許可情報を登録
        $this->insertOptProduct('product1', 0, 'mst_product1', $paidAmount);
        $this->billingService->allowedToPurchase(
            $userId,
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            'android_product_id1',
            'product1',
            $deviceId
        );
        $allowance = $this->billingService->getStoreAllowance($userId, CurrencyConstants::PLATFORM_GOOGLEPLAY, 'android_product_id1');
        // ストア情報を登録
        $this->billingService->setStoreInfo($userId, 20, '2020-01-01 00:00:00');

        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionCode(ErrorCode::BILLING_TRANSACTION_END);
        $this->billingService->forceClosePurchase(
            $userId,
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            $deviceId,
            $allowance,
            $purchasePrice,
            $rawPriceString,
            $currencyCode,
            $receipt,
            new Trigger('purchased', 'product1', 'trigger product1 name', 'sample details'),
            $loggingProductSubName,
        );

        // Verify
        //  購入した数だけ所持数が増えていること
        $currencySummary = $this->currencyService->getCurrencySummary($userId);
        $this->assertEquals($paidAmount, $currencySummary->paid_amount_apple);

        //  paidの管理レコードが追加されていること
        $usrCurrencyPaid = $this->currencyService->getCurrencyPaidAll($userId)[0];
        $this->assertEquals($userId, $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $usrCurrencyPaid->seq_no);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_ANDROID, $usrCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_GOOGLEPLAY, $usrCurrencyPaid->billing_platform);
        $this->assertEquals($paidAmount, $usrCurrencyPaid->left_amount);
        $this->assertEquals($purchasePrice, $usrCurrencyPaid->purchase_price);
        $this->assertEquals($paidAmount, $usrCurrencyPaid->purchase_amount);
        $this->assertEquals('10.00000000', $usrCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $usrCurrencyPaid->vip_point);
        $this->assertEquals($currencyCode, $usrCurrencyPaid->currency_code);
        $this->assertEquals($receiptUniqueId, $usrCurrencyPaid->receipt_unique_id);
        $this->assertEquals(false, $usrCurrencyPaid->is_sandbox);

        //  コールバックが動作していること
        $this->assertTrue($actualCallbackFlg);

        // 購入許可情報が削除されていること
        $afterAllowance = $this->billingService->getStoreAllowance($userId, CurrencyConstants::PLATFORM_GOOGLEPLAY, 'product1');
        $this->assertNull($afterAllowance);

        // ストア情報の累計購入額に加算されていること
        $usrStoreInfo = $this->billingService->getStoreInfo($userId);
        $this->assertEquals('100.000000', $usrStoreInfo->paid_price);
        $this->assertEquals(101, $usrStoreInfo->total_vip_point);

        // ストア購入履歴が登録されていること
        $usrStoreProductHistory = $this->usrStoreProductHistoryRepository->findByReceiptUniqueIdAndBillingPlatform(
            $receipt->getUnitqueId(),
            CurrencyConstants::PLATFORM_GOOGLEPLAY
        );
        $this->assertEquals($userId, $usrStoreProductHistory->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_ANDROID, $usrStoreProductHistory->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_GOOGLEPLAY, $usrStoreProductHistory->billing_platform);
        $this->assertEquals('product1', $usrStoreProductHistory->product_sub_id);
        $this->assertEquals('android_product_id1', $usrStoreProductHistory->platform_product_id);
        $this->assertEquals('mst_product1', $usrStoreProductHistory->mst_store_product_id);
        $this->assertEquals('JPY', $usrStoreProductHistory->currency_code);
        $this->assertEquals('100.000000', $usrStoreProductHistory->purchase_price);
        $this->assertEquals('10.00000000', $usrStoreProductHistory->price_per_amount);
        $this->assertEquals(101, $usrStoreProductHistory->vip_point);
        $this->assertEquals($deviceId, $usrStoreProductHistory->device_id);
        $this->assertEquals(20, $usrStoreProductHistory->age);

        // ログが追加されていること
        // log_store
        $logStore = $this->logStoreRepository->findByUserId($userId)[0];
        $this->assertEquals($userId, $logStore->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_ANDROID, $logStore->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_GOOGLEPLAY, $logStore->billing_platform);
        $this->assertEquals($deviceId, $logStore->device_id);
        $this->assertEquals(20, $logStore->age);
        $this->assertEquals('android_product_id1', $logStore->platform_product_id);
        $this->assertEquals('mst_product1', $logStore->mst_store_product_id);
        $this->assertEquals('product1', $logStore->product_sub_id);
        $this->assertEquals('product1 name', $logStore->product_sub_name);
        $this->assertEquals('100.000000', $logStore->purchase_price);
        $this->assertEquals('10.00000000', $logStore->price_per_amount);
        $this->assertEquals(101, $logStore->vip_point);
        $this->assertEquals('JPY', $logStore->currency_code);
        $this->assertEquals($receiptUniqueId, $logStore->receipt_unique_id);
        $this->assertEquals($receipt->getBundleId(), $logStore->receipt_bundle_id);
        $this->assertEquals($receipt->getReceipt(), $logStore->raw_receipt);
        $this->assertEquals($rawPriceString, $logStore->raw_price_string);
        $this->assertEquals($paidAmount, $logStore->paid_amount);
        $this->assertEquals(0, $logStore->free_amount);
        $this->assertEquals('purchased', $logStore->trigger_type);
        $this->assertEquals('product1', $logStore->trigger_id);
        $this->assertEquals('trigger product1 name', $logStore->trigger_name);
        $this->assertEquals('sample details', $logStore->trigger_detail);

        // log_allowance
        $logAllowance = $this->logAllowanceRepository->findByUserId($userId)[0];
        $this->assertEquals($userId, $logAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_ANDROID, $logAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_GOOGLEPLAY, $logAllowance->billing_platform);
        $this->assertEquals('android_product_id1', $logAllowance->product_id);
        $this->assertEquals('mst_product1', $logAllowance->mst_store_product_id);
        $this->assertEquals('product1', $logAllowance->product_sub_id);
        $this->assertEquals($deviceId, $logAllowance->device_id);
        $this->assertEquals('delete', $logAllowance->trigger_type);
        $this->assertEquals($allowance->id, $logAllowance->trigger_id);
        $this->assertEquals('', $logAllowance->trigger_name);
        $this->assertEquals('', $logAllowance->trigger_detail);

        // log_currency_paids
        $logCurrencyPaid = LogCurrencyPaid::query()->where('usr_user_id', $userId)->first();
        $this->assertEquals($userId, $logCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_ANDROID, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_GOOGLEPLAY, $logCurrencyPaid->billing_platform);
        $this->assertEquals($usrCurrencyPaid->id, $logCurrencyPaid->currency_paid_id);
        $this->assertEquals($receiptUniqueId, $logCurrencyPaid->receipt_unique_id);
        $this->assertEquals(false, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_INSERT, $logCurrencyPaid->query);
        $this->assertEquals($purchasePrice, $logCurrencyPaid->purchase_price);
        $this->assertEquals($paidAmount, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('10.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals($currencyCode, $logCurrencyPaid->currency_code);
        $this->assertEquals(0, $logCurrencyPaid->before_amount);
        $this->assertEquals($paidAmount, $logCurrencyPaid->change_amount);
        $this->assertEquals($paidAmount, $logCurrencyPaid->current_amount);
        $this->assertEquals('purchased', $logCurrencyPaid->trigger_type);
        $this->assertEquals('product1', $logCurrencyPaid->trigger_id);
        $this->assertEquals('trigger product1 name', $logCurrencyPaid->trigger_name);
        $this->assertEquals('sample details', $logCurrencyPaid->trigger_detail);

        // log_close_store_transactions
        $logCloseStoreTransaction = LogCloseStoreTransaction::query()->where('usr_user_id', $userId)->first();
        $this->assertEquals($userId, $logCloseStoreTransaction->usr_user_id);
        $this->assertEquals('android_product_id1', $logCloseStoreTransaction->platform_product_id);
        $this->assertEquals('mst_product1', $logCloseStoreTransaction->mst_store_product_id);
        $this->assertEquals('product1', $logCloseStoreTransaction->product_sub_id);
        $this->assertEquals('product1 name', $logCloseStoreTransaction->product_sub_name);
        $this->assertEquals($receipt->getReceipt(), $logCloseStoreTransaction->raw_receipt);
        $this->assertEquals($rawPriceString, $logCloseStoreTransaction->raw_price_string);
        $this->assertEquals('JPY', $logCloseStoreTransaction->currency_code);
        $this->assertEquals($receipt->getUnitqueId(), $logCloseStoreTransaction->receipt_unique_id);
        $this->assertEquals($receipt->getBundleId(), $logCloseStoreTransaction->receipt_bundle_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_ANDROID, $logCloseStoreTransaction->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_GOOGLEPLAY, $logCloseStoreTransaction->billing_platform);
        $this->assertEquals($deviceId, $logCloseStoreTransaction->device_id);
        $this->assertEquals($purchasePrice, $logCloseStoreTransaction->purchase_price);
        $this->assertEquals(false, $logCloseStoreTransaction->is_sandbox);
        $this->assertEquals($logStore->id, $logCloseStoreTransaction->log_store_id);
        $this->assertEquals($usrStoreProductHistory->id, $logCloseStoreTransaction->usr_store_product_history_id);
        $this->assertEquals('purchased', $logCloseStoreTransaction->trigger_type);
        $this->assertEquals('trigger product1 name', $logCloseStoreTransaction->trigger_name);
        $this->assertEquals('product1', $logCloseStoreTransaction->trigger_id);
        $this->assertEquals('sample details', $logCloseStoreTransaction->trigger_detail);
    }
}
