<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Entities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Entities\UsrStoreProductHistoryEntity;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class UsrStoreProductHistoryEntityTest extends TestCase
{
    use RefreshDatabase;

    private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->usrStoreProductHistoryRepository = $this->app->make(UsrStoreProductHistoryRepository::class);
    }

    #[Test]
    public function スキーマチェック(): void
    {
        // Setup
        $this->usrStoreProductHistoryRepository
            ->insertStoreProductHistory(
                '1',
                'device1',
                20,
                'receipt unique id',
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                'product1',
                'store_product1',
                'mst_product1',
                'JPY',
                'bundle_id1',
                'purchase_token1',
                100,
                0,
                '100.000000',
                '1.00000000',
                101,
                true,
            );
        $usrStoreProductHistory = $this->usrStoreProductHistoryRepository
            ->findByReceiptUniqueIdAndBillingPlatform(
                'receipt unique id',
                CurrencyConstants::PLATFORM_APPSTORE
            );

        // Exercise
        /** @var UsrStoreProductHistoryEntity $entity */
        $entity = $usrStoreProductHistory->getModelEntity();

        // Verify
        $this->assertEquals('receipt unique id', $entity->getReceiptUniqueId());
        $this->assertEquals('iOS', $entity->getOsPlatform());
        $this->assertEquals('1', $entity->getUsrUserId());
        $this->assertEquals('device1', $entity->getDeviceId());
        $this->assertEquals(20, $entity->getAge());
        $this->assertEquals('product1', $entity->getProductSubId());
        $this->assertEquals('store_product1', $entity->getPlatformProductId());
        $this->assertEquals('mst_product1', $entity->getMstStoreProductId());
        $this->assertEquals('bundle_id1', $entity->getReceiptBundleId());
        $this->assertEquals(100, $entity->getPaidAmount());
        $this->assertEquals(0, $entity->getFreeAmount());
        $this->assertEquals('100.000000', $entity->getPurchasePrice());
        $this->assertEquals('1.00000000', $entity->getPricePerAmount());
        $this->assertEquals(101, $entity->getVipPoint());
        $this->assertEquals(true, $entity->getIsSandbox());
        $this->assertEquals('AppStore', $entity->getBillingPlatform());
    }
}
