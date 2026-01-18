<?php

declare(strict_types=1);

namespace Tests\Unit\Currency\Services;

use App\Domain\Common\Constants\System;
use App\Domain\Currency\Services\AppCurrencyService;
use App\Domain\Resource\Mst\Models\MstStoreProduct;
use App\Domain\Resource\Mst\Models\OprProduct;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class AppCurrencyServiceTest extends TestCase
{
    private AppCurrencyService $currencyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currencyService = $this->app->make(AppCurrencyService::class);
    }

    #[Test]
    public function getMstStoreProductById_mst_store_productを取得する()
    {
        // Setup
        MstStoreProduct::factory()->createMockData();

        // Exercise
        $storeProduct = $this->currencyService->getMstStoreProductById('edmo_pack_160_1_framework');

        // Verify
        $this->assertEquals('edmo_pack_160_1_framework', $storeProduct->getId());
        $this->assertEquals('ios_edmo_pack_160_1_framework', $storeProduct->getProductIdIos());
        $this->assertEquals('android_edmo_pack_160_1_framework', $storeProduct->getProductIdAndroid());
    }

    public static function getMstStoreProductByProductIdData()
    {
        return [
            'iOS' => [CurrencyConstants::PLATFORM_APPSTORE, 'ios_edmo_pack_160_1_framework'],
            'android' => [CurrencyConstants::PLATFORM_GOOGLEPLAY, 'android_edmo_pack_160_1_framework'],
        ];
    }

    #[Test]
    #[DataProvider('getMstStoreProductByProductIdData')]
    public function getMstStoreProductByProductId_課金プラットフォームとストアのプロダクトIDからmst_store_productを取得する($billingPlatform, $productId)
    {
        // Setup
        MstStoreProduct::factory()->createMockData();

        // Exercise
        $result = $this->currencyService->getMstStoreProductByProductId($productId, $billingPlatform);

        // Verify
        $this->assertEquals('edmo_pack_160_1_framework', $result->getId());
    }

    #[Test]
    public function getOprProductById_OprProductを取得する()
    {
        // Setup
        OprProduct::factory()->create([
            'id' => 'test_id',
        ]);

        // Exercise
        $result = $this->currencyService->getOprProductById('test_id');

        // Verify
        $this->assertEquals('test_id', $result->getId());
    }

    #[Test]
    public function getOprProductByMstProductId_mst_store_product_idからOprProductを取得する()
    {
        // Setup
        OprProduct::factory()->create([
            'id' => 'test_id',
            'mst_store_product_id' => 'mst_store_product_id',
        ]);

        // Exercise
        $result = $this->currencyService->getOprProductByMstProductId('mst_store_product_id');

        // Verify
        $this->assertEquals('test_id', $result->getId());
    }

    public static function getBillingPlatformData(): array
    {
        return [
            'iOS' => [System::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE],
            'android' => [System::PLATFORM_ANDROID, CurrencyConstants::PLATFORM_GOOGLEPLAY],
        ];
    }

    #[Test]
    #[DataProvider('getBillingPlatformData')]
    public function getBillingPlatform_正しいプラットフォーム名を取得(string $platform, string $expected): void
    {
        // Exercise
        $result = $this->currencyService->getBillingPlatform($platform);

        // Verify
        $this->assertEquals($expected, $result);
    }

    public static function getOsPlatformData(): array
    {
        return [
            'iOS' => [System::PLATFORM_IOS, CurrencyConstants::OS_PLATFORM_IOS],
            'android' => [System::PLATFORM_ANDROID, CurrencyConstants::OS_PLATFORM_ANDROID],
        ];
    }

    #[Test]
    #[DataProvider('getOsPlatformData')]
    public function getOsPlatform_正しいOSプラットフォーム名を取得(string $platform, string $expected): void
    {
        // Exercise
        $result = $this->currencyService->getOsPlatform($platform);

        // Verify
        $this->assertEquals($expected, $result);
    }
}
