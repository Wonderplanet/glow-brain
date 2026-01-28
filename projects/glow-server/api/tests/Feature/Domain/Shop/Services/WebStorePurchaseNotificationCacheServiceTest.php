<?php

namespace Tests\Feature\Domain\Shop\Services;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Shop\Services\WebStorePurchaseNotificationCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebStorePurchaseNotificationCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    private WebStorePurchaseNotificationCacheService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(WebStorePurchaseNotificationCacheService::class);
    }

    public function testGetUnnotifiedPurchases_正常系_キャッシュにデータがある場合(): void
    {
        // Setup
        $usrUserId = 'test_user_001';
        $productSubIds = ['product_001', 'product_002'];
        $cacheKey = CacheKeyUtil::getWebStorePurchaseNotificationsKey($usrUserId);
        $this->setToRedis($cacheKey, $productSubIds);

        // Exercise
        $actual = $this->service->getUnnotifiedPurchases($usrUserId);

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $actual);
        $this->assertCount(2, $actual);
        $this->assertEquals(['product_001', 'product_002'], $actual->all());
    }

    public function testAddPurchaseNotifications_正常系_新規データを追加できる(): void
    {
        // Setup
        $usrUserId = 'test_user_003';
        $productSubIds = collect(['product_001', 'product_002']);

        // Exercise
        $this->service->addPurchaseNotifications($usrUserId, $productSubIds);

        // Verify
        $cacheKey = CacheKeyUtil::getWebStorePurchaseNotificationsKey($usrUserId);
        $cached = $this->getFromRedis($cacheKey);
        $this->assertIsArray($cached);
        $this->assertCount(2, $cached);
        $this->assertEquals(['product_001', 'product_002'], $cached);
    }

    public function testAddPurchaseNotifications_正常系_既存データにマージして追加できる(): void
    {
        // Setup
        $usrUserId = 'test_user_004';
        $cacheKey = CacheKeyUtil::getWebStorePurchaseNotificationsKey($usrUserId);
        $this->setToRedis($cacheKey, ['product_001']);

        $newProductSubIds = collect(['product_002', 'product_003']);

        // Exercise
        $this->service->addPurchaseNotifications($usrUserId, $newProductSubIds);

        // Verify
        $cached = $this->getFromRedis($cacheKey);
        $this->assertIsArray($cached);
        $this->assertCount(3, $cached);
        $this->assertEquals(['product_001', 'product_002', 'product_003'], $cached);
    }

    public function testGetUnnotifiedProductSubIdsAndClear_正常系_データを取得してキャッシュをクリアする(): void
    {
        // Setup
        $usrUserId = 'test_user_006';
        $productSubIds = ['product_001', 'product_002'];
        $cacheKey = CacheKeyUtil::getWebStorePurchaseNotificationsKey($usrUserId);
        $this->setToRedis($cacheKey, $productSubIds);

        // Exercise
        $actual = $this->service->getUnnotifiedProductSubIdsAndClear($usrUserId);

        // Verify
        // 取得したデータが正しい
        $this->assertCount(2, $actual);
        $this->assertEquals(['product_001', 'product_002'], $actual->all());

        // キャッシュがクリアされている
        $cached = $this->getFromRedis($cacheKey);
        $this->assertNull($cached);
    }

    public function testGetUnnotifiedProductSubIdsAndClear_正常系_データがない場合は空のCollectionを返す(): void
    {
        // Setup
        $usrUserId = 'test_user_007';
        $cacheKey = CacheKeyUtil::getWebStorePurchaseNotificationsKey($usrUserId);

        // Exercise
        $result = $this->service->getUnnotifiedProductSubIdsAndClear($usrUserId);

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertCount(0, $result);
        $this->assertTrue($result->isEmpty());

        // キャッシュは存在しない（clearNotificationsが呼ばれない）
        $cached = $this->getFromRedis($cacheKey);
        $this->assertNull($cached);
    }

    public function testClearNotifications_正常系_キャッシュを削除できる(): void
    {
        // Setup
        $usrUserId = 'test_user_008';
        $productSubIds = ['product_001', 'product_002'];
        $cacheKey = CacheKeyUtil::getWebStorePurchaseNotificationsKey($usrUserId);
        $this->setToRedis($cacheKey, $productSubIds);

        // 事前確認: キャッシュが存在する
        $this->assertNotNull($this->getFromRedis($cacheKey));

        // Exercise
        $this->service->clearNotifications($usrUserId);

        // Verify
        $cached = $this->getFromRedis($cacheKey);
        $this->assertNull($cached);
    }

    public function testClearNotifications_正常系_キャッシュが存在しない場合もエラーにならない(): void
    {
        // Setup
        $usrUserId = 'test_user_009';

        // Exercise & Verify: 例外が投げられないこと
        $this->service->clearNotifications($usrUserId);

        // アサーションなしで正常終了すればOK
        $this->assertTrue(true);
    }
}
