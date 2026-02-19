<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Shop\UseCases;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Shop\Constants\ShopPurchaseHistoryConstant;
use App\Domain\Shop\Entities\CurrencyPurchase;
use App\Domain\Shop\UseCases\ShopPurchaseHistoryUseCase;
use App\Http\Responses\ResultData\ShopPurchaseHistoryResultData;
use Illuminate\Support\Facades\Redis;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class ShopPurchaseHistoryUseCaseTest extends TestCase
{
    use FakeStoreReceiptTrait;

    private ShopPurchaseHistoryUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = $this->app->make(ShopPurchaseHistoryUseCase::class);
    }

    public function test_exec_正常なプリズム購入履歴を確認()
    {
        // Setup
        $now = $this->fixTime('2025-10-10 00:00:00');
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $currentUser = new CurrentUser($usrUserId);
        $currencyPurchases = collect([
            new CurrencyPurchase(
                "$5.99",
                60,
                'USD',
                $now->subDay()->toDateTimeString(),
            ),
            new CurrencyPurchase(
                "¥15,000",
                1500,
                'JPY',
                $now->subDays(ShopPurchaseHistoryConstant::HISTORY_DAYS - 1)->toDateTimeString(),
            ),
            new CurrencyPurchase(
                "¥12,000",
                1200,
                'JPY',
                $now->subDays(ShopPurchaseHistoryConstant::HISTORY_DAYS)->toDateTimeString(),
            ),
            new CurrencyPurchase(
                "¥10,000",
                1000,
                'JPY',
                $now->subDays(ShopPurchaseHistoryConstant::HISTORY_DAYS)->subSecond()->toDateTimeString(),
            ),
            new CurrencyPurchase(
                "$1.99",
                60,
                'USD',
                $now->subDays(ShopPurchaseHistoryConstant::HISTORY_DAYS + 30)->toDateTimeString(),
            ),
        ]);
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $purchaseHistories = collect([$billingPlatform => $currencyPurchases]);
        $key = CacheKeyUtil::getShopPurchaseHistoryKey($usrUserId);
        Redis::connection()->set($key, serialize($purchaseHistories));

        // Exercise
        $result = $this->useCase->exec(
            $currentUser,
            $billingPlatform,
        );

        // Verify
        $this->assertInstanceOf(ShopPurchaseHistoryResultData::class, $result);
        $this->assertCount(3, $result->currencyPurchases);

        $currencyPurchase = $result->currencyPurchases[0];
        $this->assertEquals('$5.99', $currencyPurchase->getPurchasePrice());
        $this->assertEquals('60', $currencyPurchase->getPurchaseAmount());
        $this->assertEquals('USD', $currencyPurchase->getCurrencyCode());
        $this->assertEquals($now->subDay()->toDateTimeString(), $currencyPurchase->getPurchaseAt());

        $currencyPurchase = $result->currencyPurchases[1];
        $this->assertEquals('¥15,000', $currencyPurchase->getPurchasePrice());
        $this->assertEquals('1500', $currencyPurchase->getPurchaseAmount());
        $this->assertEquals('JPY', $currencyPurchase->getCurrencyCode());
        $this->assertEquals($now->subDays(6)->toDateTimeString(), $currencyPurchase->getPurchaseAt());

        $currencyPurchase = $result->currencyPurchases[2];
        $this->assertEquals('¥12,000', $currencyPurchase->getPurchasePrice());
        $this->assertEquals('1200', $currencyPurchase->getPurchaseAmount());
        $this->assertEquals('JPY', $currencyPurchase->getCurrencyCode());
        $this->assertEquals($now->subDays(7)->toDateTimeString(), $currencyPurchase->getPurchaseAt());
    }
}
