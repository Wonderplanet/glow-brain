<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingDuplicateReceiptException;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Billing\Services\BillingStoreService;
use WonderPlanet\Domain\Billing\Services\Platforms\AppStorePlatformService;
use WonderPlanet\Domain\Billing\Services\Platforms\FakeStorePlatformService;
use WonderPlanet\Domain\Billing\Services\Platforms\GooglePlayPlatformService;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class BillingStoreServiceTest extends TestCase
{
    use RefreshDatabase;
    use FakeStoreReceiptTrait;

    private BillingStoreService $billingStoreService;
    private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->billingStoreService = $this->app->make(BillingStoreService::class);
        $this->usrStoreProductHistoryRepository = $this->app->make(UsrStoreProductHistoryRepository::class);
    }

    public static function getStorePlatformServiceData()
    {
        return [
            'Fake Store向けサービスの取得' => [CurrencyConstants::PLATFORM_APPSTORE, self::makeFakeStoreReceiptString(), FakeStorePlatformService::class],
            // AppStore、GoolePlayはbillingPlatformで判別しているのでレシートは空でも良い
            'AppStore向けサービスの取得' => [CurrencyConstants::PLATFORM_APPSTORE, '', AppStorePlatformService::class],
            'GooglePlay向けサービスの取得' => [CurrencyConstants::PLATFORM_GOOGLEPLAY, '', GooglePlayPlatformService::class],
        ];
    }

    #[Test]
    #[DataProvider('getStorePlatformServiceData')]
    public function getStorePlatformService_レシート別のプラットフォームを取得する(string $billingPlatform, string $receipt, string $class)
    {
        // Exercise
        $actual = $this->billingStoreService->getStorePlatformService($billingPlatform, $receipt, true);

        // Verify
        $this->assertInstanceOf($class, $actual);
    }

    #[Test]
    public function purchaseAcknowledge_購入が承認されること()
    {
        // Setup
        $receipt = $this->makeFakeStoreReceipt('store_product1');

        // Exercise
        //   ユニットテスト用のレシートでは何も発生しない
        //   GooglePlayのレシート処理はGooglePlayPlatformServiceTestで行う
        $this->billingStoreService->purchaseAcknowledge($receipt);

        // Verify
        //  エラーがなければOK
        $this->assertTrue(true);
    }

    #[Test]
    public function verifyReceipt_レシート検証が完了すること()
    {
        // Setup
        $receipt = self::makeFakeStoreReceiptString('unique_id1');

        // Exercise
        //  ユニットテストのレシートで疎通確認を行う
        //  各ストアのレシートは、それぞれのプラットフォームサービスのテストで行う
        $actual = $this->billingStoreService->verifyReceipt(CurrencyConstants::PLATFORM_APPSTORE, 'store_product1', $receipt);

        // Verify
        $this->assertEquals('unique_id1', $actual->getUnitqueId());
    }

    #[Test]
    public function verifyReceiptUniqueId_処理していないレシートは通す()
    {
        // Exercise
        $this->callMethod(
            $this->billingStoreService,
            'verifyReceiptUniqueId',
            [
                CurrencyConstants::PLATFORM_APPSTORE,
                'unique_id1'
            ]
        );

        // Verify
        $this->assertTrue(true);
    }

    #[Test]
    public function verifyReceiptUniqueId_処理済みのレシートはエラー()
    {
        // Setup
        $this->usrStoreProductHistoryRepository->insertStoreProductHistory(
            '1',
            'device1',
            0,
            'unique_id1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'opr_product1',
            'store_product1',
            'mst_store_product1',
            'JPY',
            'bundle_id1',
            'purchase_token1',
            1,
            0,
            '100.00',
            '100.00',
            101,
            false
        );

        // Exercise
        $this->expectException(WpBillingDuplicateReceiptException::class);
        $this->callMethod(
            $this->billingStoreService,
            'verifyReceiptUniqueId',
            [
                CurrencyConstants::PLATFORM_APPSTORE,
                'unique_id1'
            ]
        );

        // Verify
    }
}
