<?php

namespace Tests\Feature\Domain\Shop\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Shop\Services\ShopBillingService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Entities\StoreReceipt;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class ShopBillingServiceTest extends TestCase
{
    use FakeStoreReceiptTrait;

    private ShopBillingService $shopBillingService;

    public function setUp(): void
    {
        parent::setUp();
        $this->shopBillingService = $this->app->make(ShopBillingService::class);
    }

    /**
     * getOrForceInsertAllowanceメソッドで、リクエストされたproductIdがレシートに含まれていない場合、
     * ErrorCode::BILLING_VERIFY_RECEIPT_INVALID_RECEIPTの例外が投げられることを確認する
     */
    public function test_getOrForceInsertAllowance_リクエストproductIdがレシートに含まれていない場合例外が発生すること(): void
    {
        // Setup
        $usrUserId = 'test_user_123';
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $requestProductId = 'requested_product_id';
        $osPlatform = System::PLATFORM_IOS;
        $deviceId = 'test_device_123';

        Config::set('wp_currency.is_debuggable_env', false);

        // レシートにはリクエストしたproductIdと異なるproductIdが含まれている
        $receiptProductIds = ['different_product_id_1', 'different_product_id_2'];

        $storeReceipt = $this->createMock(StoreReceipt::class);
        $storeReceipt->method('getProductIds')
                    ->willReturn($receiptProductIds);
        $storeReceipt->method('getPurchaseDate')
                    ->willReturn(CarbonImmutable::now());

        // 期待する例外を設定
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::BILLING_VERIFY_RECEIPT_INVALID_RECEIPT);
        $this->expectExceptionMessage(
            sprintf(
                'Request productId was not found in the receipt. request: %s, receipt: %s',
                $requestProductId,
                implode(', ', $receiptProductIds)
            )
        );

        // Exercise
        $this->shopBillingService->getOrForceInsertAllowance(
            $usrUserId,
            $billingPlatform,
            $requestProductId,
            $storeReceipt,
            $osPlatform,
            $deviceId
        );

        // Verify
        // expectException により例外が発生することを検証
    }
}
