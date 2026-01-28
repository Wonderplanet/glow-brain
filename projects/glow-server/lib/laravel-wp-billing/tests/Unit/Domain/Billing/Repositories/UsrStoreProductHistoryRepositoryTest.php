<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class UsrStoreProductHistoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usrStoreProductHistoryRepository = $this->app->make(UsrStoreProductHistoryRepository::class);
    }

    #[Test]
    public function insertStoreProductHistory_ストア購入履歴を登録()
    {
        // Exercise
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            '1',
            'device1',
            1,
            'receipt_unique_id',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'product_sub_id',
            'platform_product_id',
            'mst_store_product_id',
            'JPY',
            'receipt_bundle_id',
            'receipt_purchase_token',
            100,
            0,
            '1.00',
            '0.01',
            110,
            false
        );

        // Verify
        $usrStoreProductHistory = $this->usrStoreProductHistoryRepository->findByReceiptUniqueIdAndBillingPlatform(
            'receipt_unique_id',
            CurrencyConstants::PLATFORM_APPSTORE
        );
        $this->assertNotNull($usrStoreProductHistory);
        $this->assertSame('1', $usrStoreProductHistory->usr_user_id);
        $this->assertSame('device1', $usrStoreProductHistory->device_id);
        $this->assertSame(1, $usrStoreProductHistory->age);
        $this->assertSame('receipt_unique_id', $usrStoreProductHistory->receipt_unique_id);
        $this->assertSame(CurrencyConstants::OS_PLATFORM_IOS, $usrStoreProductHistory->os_platform);
        $this->assertSame(CurrencyConstants::PLATFORM_APPSTORE, $usrStoreProductHistory->billing_platform);
        $this->assertSame('product_sub_id', $usrStoreProductHistory->product_sub_id);
        $this->assertSame('platform_product_id', $usrStoreProductHistory->platform_product_id);
        $this->assertSame('mst_store_product_id', $usrStoreProductHistory->mst_store_product_id);
        $this->assertSame('JPY', $usrStoreProductHistory->currency_code);
        $this->assertSame('receipt_bundle_id', $usrStoreProductHistory->receipt_bundle_id);
        $this->assertSame(100, $usrStoreProductHistory->paid_amount);
        $this->assertSame(0, $usrStoreProductHistory->free_amount);
        $this->assertSame('1.000000', $usrStoreProductHistory->purchase_price);
        $this->assertSame('0.01000000', $usrStoreProductHistory->price_per_amount);
        $this->assertSame(110, $usrStoreProductHistory->vip_point);
        $this->assertSame(0, $usrStoreProductHistory->is_sandbox);
    }

    #[Test]
    public function sumVipPoint_VIPポイントを集計する()
    {
        // Setup
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            '1',
            'device1',
            1,
            'receipt_unique_id',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'product_sub_id',
            'platform_product_id',
            'mst_store_product_id',
            'JPY',
            'receipt_bundle_id',
            'receipt_purchase_token',
            100,
            0,
            '1.00',
            '0.01',
            101,
            false
        );
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            '1',
            'device1',
            1,
            'receipt_unique_id-2',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'product_sub_id',
            'platform_product_id',
            'mst_store_product_id',
            'JPY',
            'receipt_bundle_id',
            'receipt_purchase_token',
            200,
            0,
            '1.00',
            '0.01',
            202,
            false
        );
        // 別のユーザーの履歴
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            '2',
            'device1',
            1,
            'receipt_unique_id-3',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'product_sub_id',
            'platform_product_id',
            'mst_store_product_id',
            'JPY',
            'receipt_bundle_id',
            'receipt_purchase_token',
            1000,
            0,
            '1.00',
            '0.01',
            1000,
            false
        );
        // sandboxレシートは集計対象外
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            '1',
            'device1',
            1,
            'receipt_unique_id-4',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'product_sub_id',
            'platform_product_id',
            'mst_store_product_id',
            'JPY',
            'receipt_bundle_id',
            'receipt_purchase_token',
            2000,
            0,
            '1.00',
            '0.01',
            2000,
            true
        );

        // Exercise
        $sumVipPoint = $this->usrStoreProductHistoryRepository->sumVipPoint('1');

        // Verify
        $this->assertSame(303, $sumVipPoint);
    }

    #[Test]
    public function softDeleteByUserId_論理削除する()
    {
        // Setup
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            '1',
            'device1',
            0,
            'receipt_unique_id',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'product_sub_id',
            'platform_product_id',
            'mst_store_product_id',
            'JPY',
            'receipt_bundle_id',
            'receipt_purchase_token',
            100,
            0,
            '1.00',
            '0.01',
            110,
            true,
        );
        // 別のユーザー情報
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            '2',
            'device1',
            0,
            'receipt_unique_id2',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'product_sub_id',
            'platform_product_id',
            'mst_store_product_id',
            'JPY',
            'receipt_bundle_id',
            'receipt_purchase_token',
            100,
            0,
            '1.00',
            '0.01',
            110,
            true,
        );

        // Exercise
        $this->usrStoreProductHistoryRepository->softDeleteByUserId('1');

        // Verify
        $usrStoreProductHistory = $this->usrStoreProductHistoryRepository->findByReceiptUniqueIdAndBillingPlatform(
            'receipt_unique_id',
            CurrencyConstants::PLATFORM_APPSTORE
        );
        $this->assertNull($usrStoreProductHistory);

        // 論理削除されていることを確認
        $usrStoreProductHistory = UsrStoreProductHistory::withTrashed()
            ->where('usr_user_id', '1')
            ->first();
        $this->assertSame('1', $usrStoreProductHistory->usr_user_id);
        $this->assertNotNull($usrStoreProductHistory->deleted_at);

        // 別のユーザー情報は削除されていないこと
        $usrStoreProductHistory = UsrStoreProductHistory::withTrashed()
            ->where('usr_user_id', '2')
            ->first();
        $this->assertNotNull($usrStoreProductHistory);
    }

    #[Test]
    public function hasStoreProductHistory_ショップ履歴あり()
    {
        // Setup
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            '1',
            'device1',
            0,
            'receipt_unique_id',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'product_sub_id',
            'platform_product_id',
            'mst_store_product_id',
            'JPY',
            'receipt_bundle_id',
            'receipt_purchase_token',
            100,
            0,
            '1.00',
            '0.01',
            110,
            true,
        );

        // Exercise
        $hasStoreProductHistory = $this->usrStoreProductHistoryRepository->hasStoreProductHistory('1');

        // Verify
        $this->assertTrue($hasStoreProductHistory);
    }

    #[Test]
    public function hasStoreProductHistory_ショップ履歴なし()
    {
        // Exercise
        $hasStoreProductHistory = $this->usrStoreProductHistoryRepository->hasStoreProductHistory('1');

        // Verify
        $this->assertFalse($hasStoreProductHistory);
    }

    #[Test]
    public function findByUserIdAndReceiptUniqueIdsFromBillingPlatform_正常取得(): void
    {
        // Setup
        $userId = '1';
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $receiptUniqueIds = [
            'receipt_unique_id_1-1',
            'receipt_unique_id_1-2',
            'receipt_unique_id_1-3', // プラットフォームが異なるので取得しない
            'receipt_unique_id_101-1',
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
            0,
            true,
        );

        // Exercise
        $usrStoreProductHistoryCollection = $this->usrStoreProductHistoryRepository
            ->findByUserIdAndReceiptUniqueIdsFromBillingPlatform(
                $userId,
                $billingPlatform,
                $receiptUniqueIds
            );

        // Verify
        //  件数チェック
        $this->assertCount(2, $usrStoreProductHistoryCollection);

        //  取得内容のチェック
        $result1 = $usrStoreProductHistoryCollection->first(fn ($row) => $row['receipt_unique_id'] === 'receipt_unique_id_1-1');
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $result1['os_platform']);
        $this->assertEquals('1', $result1['usr_user_id']);
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

        $result2 = $usrStoreProductHistoryCollection->first(fn ($row) => $row['receipt_unique_id'] === 'receipt_unique_id_1-2');
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $result2['os_platform']);
        $this->assertEquals('1', $result2['usr_user_id']);
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
