<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Feature\Domain\Billing\Delegators;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Delegators\BillingInternalDelegator;
use WonderPlanet\Domain\Billing\Repositories\LogAllowanceRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreAllowanceRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreInfoRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Billing\Services\BillingService;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class BillingInternalDelegatorTest extends TestCase
{
    use RefreshDatabase;

    private BillingInternalDelegator $billingInternalDelegator;
    private BillingService $billingService;
    private UsrStoreAllowanceRepository $usrStoreAllowanceRepository;
    private UsrStoreInfoRepository $usrStoreInfoRepository;
    private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository;
    private LogAllowanceRepository $logAllowanceRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->billingInternalDelegator = $this->app->make(BillingInternalDelegator::class);
        $this->billingService = $this->app->make(BillingService::class);
        $this->usrStoreAllowanceRepository = $this->app->make(UsrStoreAllowanceRepository::class);
        $this->usrStoreInfoRepository = $this->app->make(UsrStoreInfoRepository::class);
        $this->usrStoreProductHistoryRepository = $this->app->make(UsrStoreProductHistoryRepository::class);
        $this->logAllowanceRepository = $this->app->make(LogAllowanceRepository::class);
    }

    #[Test]
    public function softDeleteBillingDataByUserId_ユーザーのショップ登録情報を論理削除する()
    {
        //  購入許可情報を登録
        $this->usrStoreAllowanceRepository->insertStoreAllowance(
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
        $this->billingInternalDelegator->softDeleteBillingDataByUserId('1');

        // Verify
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
        $this->assertEquals("soft delete user_id: 1", $logAllowance->trigger_detail);
    }

    #[Test]
    public function getUsrStoreProductHistoryCollectionByUserIdAndBillingPlatformAndReceiptUniqueIds_取得(): void
    {
        // Setup
        $userId = '1';
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $receiptUniqueIds = [
            'receipt_unique_id_1-1',
            'receipt_unique_id_1-2',
            'receipt_unique_id_1-3', // プラットフォームが異なるので取得しない
            'receipt_unique_id_101-1', // ユーザーIDが異なるので取得できない
        ];

        //  購入履歴を登録
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            $userId,
            'device1',
            0,
            'receipt_unique_id_1-1',
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
            0,
            true,
        );
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            $userId,
            'device1',
            0,
            'receipt_unique_id_1-2',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'product2',
            'store_product2',
            'mst_product2',
            'JPY',
            'bundle_id2',
            'purchase_token2',
            100,
            10,
            '1000.000000',
            '10.00000000',
            10,
            true,
        );
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            $userId,
            'device2',
            0,
            'receipt_unique_id_1-3',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
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
            110,
            true,
        );
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            '101',
            'device101',
            0,
            'receipt_unique_id_101-1',
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
            110,
            true,
        );

        // Exercise
        $usrStoreProductHistories = $this->billingInternalDelegator
            ->getUsrStoreProductHistoryCollectionByUserIdAndBillingPlatformAndReceiptUniqueIds(
                $userId,
                $billingPlatform,
                $receiptUniqueIds
            );

        // Verify
        //  件数チェック
        $this->assertCount(2, $usrStoreProductHistories);

        //  取得内容のチェック
        $resultCollection = collect($usrStoreProductHistories);
        $result1 = $resultCollection->first(fn ($row) => $row['receipt_unique_id'] === 'receipt_unique_id_1-1');
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $result1['os_platform']);
        $this->assertEquals($userId, $result1['usr_user_id']);
        $this->assertEquals('device1', $result1['device_id']);
        $this->assertEquals(0, $result1['age']);
        $this->assertEquals('product1', $result1['product_sub_id']);
        $this->assertEquals('store_product1', $result1['platform_product_id']);
        $this->assertEquals('mst_product1', $result1['mst_store_product_id']);
        $this->assertEquals('JPY', $result1['currency_code']);
        $this->assertEquals('bundle_id1', $result1['receipt_bundle_id']);
        $this->assertEquals(10, $result1['paid_amount']);
        $this->assertEquals(0, $result1['free_amount']);
        $this->assertEquals('100.000000', $result1['purchase_price']);
        $this->assertEquals('10.00000000', $result1['price_per_amount']);
        $this->assertEquals(0, $result1['vip_point']);
        $this->assertEquals(1, $result1['is_sandbox']);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $result1['billing_platform']);

        $result2 = $resultCollection->first(fn ($row) => $row['receipt_unique_id'] === 'receipt_unique_id_1-2');
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $result2['os_platform']);
        $this->assertEquals($userId, $result2['usr_user_id']);
        $this->assertEquals('device1', $result2['device_id']);
        $this->assertEquals(0, $result2['age']);
        $this->assertEquals('product2', $result2['product_sub_id']);
        $this->assertEquals('store_product2', $result2['platform_product_id']);
        $this->assertEquals('mst_product2', $result2['mst_store_product_id']);
        $this->assertEquals('JPY', $result2['currency_code']);
        $this->assertEquals('bundle_id2', $result2['receipt_bundle_id']);
        $this->assertEquals(100, $result2['paid_amount']);
        $this->assertEquals(10, $result2['free_amount']);
        $this->assertEquals('1000.000000', $result2['purchase_price']);
        $this->assertEquals('10.00000000', $result2['price_per_amount']);
        $this->assertEquals(10, $result2['vip_point']);
        $this->assertEquals(1, $result2['is_sandbox']);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $result2['billing_platform']);
    }
}
