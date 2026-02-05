<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Entities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

class UsrCurrencyPaidEntityTest extends TestCase
{
    use RefreshDatabase;

    private CurrencyService $currencyService;
    private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->currencyService = $this->app->make(CurrencyService::class);
        $this->usrStoreProductHistoryRepository = $this->app->make(UsrStoreProductHistoryRepository::class);
    }

    #[Test]
    public function setUsrStoreProductHistoryEntity_セットされるかのチェック(): void
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
                50,
                true,
            );
        $usrStoreProductHistory = $this->usrStoreProductHistoryRepository
            ->findByReceiptUniqueIdAndBillingPlatform(
                'receipt unique id',
                CurrencyConstants::PLATFORM_APPSTORE
            );
        $usrStoreProductHistoryEntity = $usrStoreProductHistory->getModelEntity();

        // 通貨管理情報を登録
        $this->currencyService->registerCurrencySummary(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            0,
        );
        // 有償一次通貨レコードを追加
        $usrCurrencyPaid = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100.00000000',
            50,
            'receipt unique id',
            true,
            new Trigger('unit_test', '', '', '')
        );

        // Exercise
        $usrCurrencyPaid->setUsrStoreProductHistoryEntity($usrStoreProductHistoryEntity);

        // Verify
        $usrCurrencyPaidEntity = $usrCurrencyPaid->getModelEntity();
        $resultEntity = $usrCurrencyPaidEntity->getUsrStoreProductHistoryEntity();
        $this->assertEquals('receipt unique id', $resultEntity->getReceiptUniqueId());
        $this->assertEquals('iOS', $resultEntity->getOsPlatform());
        $this->assertEquals('1', $resultEntity->getUsrUserId());
        $this->assertEquals('device1', $resultEntity->getDeviceId());
        $this->assertEquals(20, $resultEntity->getAge());
        $this->assertEquals('product1', $resultEntity->getProductSubId());
        $this->assertEquals('store_product1', $resultEntity->getPlatformProductId());
        $this->assertEquals('mst_product1', $resultEntity->getMstStoreProductId());
        $this->assertEquals('bundle_id1', $resultEntity->getReceiptBundleId());
        $this->assertEquals(100, $resultEntity->getPaidAmount());
        $this->assertEquals(0, $resultEntity->getFreeAmount());
        $this->assertEquals('100.000000', $resultEntity->getPurchasePrice());
        $this->assertEquals('1.00000000', $resultEntity->getPricePerAmount());
        $this->assertEquals(50, $resultEntity->getVipPoint());
        $this->assertTrue($resultEntity->getIsSandbox());
        $this->assertEquals('AppStore', $resultEntity->getBillingPlatform());
    }
}
