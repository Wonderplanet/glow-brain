<?php

namespace Tests\Feature\Domain\Shop\Services;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Shop\Constants\ShopPurchaseHistoryConstant;
use App\Domain\Shop\Entities\CurrencyPurchase;
use App\Domain\Shop\Services\ShopPurchaseHistoryService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class ShopPurchaseHistoryServiceTest extends TestCase
{
    use FakeStoreReceiptTrait;

    private ShopPurchaseHistoryService $shopPurchaseHistoryService;

    public function setUp(): void
    {
        parent::setUp();
        $this->shopPurchaseHistoryService = $this->app->make(ShopPurchaseHistoryService::class);
    }

    public function test_setCurrencyPurchaseHistory_正常にプリズム購入履歴を設定(): void
    {
        $usrUserId = 'test_user_1';
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $purchasePrice = '100.00';
        $rawPriceString = '$100.00';
        $purchaseAmount = 1000;
        $currencyCode = 'USD';
        $now = $this->fixTime('2025-10-10 00:00:00');

        $this->shopPurchaseHistoryService->setCurrencyPurchaseHistory(
            $usrUserId,
            $billingPlatform,
            $purchasePrice,
            $purchaseAmount,
            $currencyCode,
            $now->subDay()
        );

        $key = CacheKeyUtil::getShopPurchaseHistoryKey($usrUserId);
        $cache = Redis::connection()->get($key);
        $this->assertNotNull($cache);

        $currencyPurchases = unserialize($cache);
        $this->assertCount(1, $currencyPurchases);

        $currencyPurchase = $currencyPurchases[$billingPlatform][0];
        $this->assertEquals($rawPriceString, $currencyPurchase->getPurchasePrice());
        $this->assertEquals($purchaseAmount, $currencyPurchase->getPurchaseAmount());
        $this->assertEquals($currencyCode, $currencyPurchase->getCurrencyCode());
        $this->assertEquals($now->subDay()->toDateTimeString(), $currencyPurchase->getPurchaseAt());
    }

    public function test_setCurrencyPurchaseHistory_プリズム購入履歴を最大数以上に登録不可(): void
    {
        $usrUserId = 'test_user_1';
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $purchasePrice = '100.00';
        $rawPriceString = '$100.00';
        $purchaseAmount = 1000;
        $currencyCode = 'USD';
        $now = $this->fixTime('2025-10-10 00:00:00');

        for ($i = 0; $i < ShopPurchaseHistoryConstant::HISTORY_LIMIT + 10; $i++) {
            $this->shopPurchaseHistoryService->setCurrencyPurchaseHistory(
                $usrUserId,
                $billingPlatform,
                $purchasePrice,
                $purchaseAmount,
                $currencyCode,
                $now->subDays(4)->addHours(1 + $i)
            );
        }

        $key = CacheKeyUtil::getShopPurchaseHistoryKey($usrUserId);
        $cache = Redis::connection()->get($key);
        $this->assertNotNull($cache);

        $currencyPurchases = unserialize($cache);
        $this->assertCount(ShopPurchaseHistoryConstant::HISTORY_LIMIT, $currencyPurchases[$billingPlatform]);

        $currencyPurchase = $currencyPurchases[$billingPlatform][0];
        $this->assertEquals($rawPriceString, $currencyPurchase->getPurchasePrice());
        $this->assertEquals($purchaseAmount, $currencyPurchase->getPurchaseAmount());
        $this->assertEquals($currencyCode, $currencyPurchase->getCurrencyCode());
        $this->assertEquals($now->subDays(4)->addHours(60)->toDateTimeString(), $currencyPurchase->getPurchaseAt());
    }

    public function test_getCurrencyPurchaseHistory_正常にプリズム購入履歴を取得(): void
    {
        $usrUserId = 'test_user_1';
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $rawPriceString = '$100.00';
        $purchaseAmount = 1000;
        $currencyCode = 'USD';
        $now = CarbonImmutable::now();

        $currencyPurchases = collect([
            new CurrencyPurchase(
                $rawPriceString,
                $purchaseAmount,
                $currencyCode,
                $now->subDay()->toDateTimeString(),
            ),
            new CurrencyPurchase(
                $rawPriceString,
                $purchaseAmount,
                $currencyCode,
                $now->subDays(2)->toDateTimeString(),
            ),
        ]);
        $purchaseHistories = collect([$billingPlatform => $currencyPurchases]);
        $key = CacheKeyUtil::getShopPurchaseHistoryKey($usrUserId);
        Redis::connection()->set($key, serialize($purchaseHistories));

        $result = $this->shopPurchaseHistoryService->getCurrencyPurchaseHistory($usrUserId, $billingPlatform, $now,);
        $this->assertCount(2, $result);

        $currencyPurchase = $result[0];
        $this->assertEquals($rawPriceString, $currencyPurchase->getPurchasePrice());
        $this->assertEquals($purchaseAmount, $currencyPurchase->getPurchaseAmount());
        $this->assertEquals($currencyCode, $currencyPurchase->getCurrencyCode());
        $this->assertEquals($now->subDay()->toDateTimeString(), $currencyPurchase->getPurchaseAt());
    }

    public function test_getCurrencyPurchaseHistory_想定より過去の履歴は取得不可(): void
    {
        $usrUserId = 'test_user_1';
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $rawPriceString = '$100.00';
        $purchaseAmount = 1000;
        $currencyCode = 'USD';
        $now = CarbonImmutable::now();

        $currencyPurchases = collect([
            new CurrencyPurchase(
                $rawPriceString,
                $purchaseAmount + 1,
                $currencyCode,
                $now->subDay()->toDateTimeString(),
            ),
            new CurrencyPurchase(
                $rawPriceString,
                $purchaseAmount,
                $currencyCode,
                $now->subDays(ShopPurchaseHistoryConstant::HISTORY_DAYS + 1)->toDateTimeString(),
            ),
        ]);
        $purchaseHistories = collect([$billingPlatform => $currencyPurchases]);
        $key = CacheKeyUtil::getShopPurchaseHistoryKey($usrUserId);
        Redis::connection()->set($key, serialize($purchaseHistories));

        $result = $this->shopPurchaseHistoryService->getCurrencyPurchaseHistory($usrUserId, $billingPlatform, $now,);
        $this->assertCount(1, $result);

        $currencyPurchase = $result[0];
        $this->assertEquals($rawPriceString, $currencyPurchase->getPurchasePrice());
        $this->assertEquals($purchaseAmount + 1, $currencyPurchase->getPurchaseAmount());
        $this->assertEquals($currencyCode, $currencyPurchase->getCurrencyCode());
        $this->assertEquals($now->subDay()->toDateTimeString(), $currencyPurchase->getPurchaseAt());
    }
}
