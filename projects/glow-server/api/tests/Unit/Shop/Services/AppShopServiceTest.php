<?php

declare(strict_types=1);

namespace Tests\Unit\Shop\Services;

use App\Domain\Currency\Services\AppCurrencyService;
use App\Domain\Resource\Mst\Models\MstStoreProduct;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Shop\Services\AppShopService;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class AppShopServiceTest extends TestCase
{
    private AppShopService $appShopService;
    private AppCurrencyService $appCurrencyService;

    public function setUp(): void
    {
        parent::setUp();

        $this->appShopService = $this->app->make(AppShopService::class);
        $this->appCurrencyService = $this->app->make(AppCurrencyService::class);
    }

    public static function getProductIdByProductSubIdParams()
    {
        return [
            'iOS' => [CurrencyConstants::PLATFORM_APPSTORE, 'ios_edmo_pack_160_1_framework'],
            'Android' => [CurrencyConstants::PLATFORM_GOOGLEPLAY, 'android_edmo_pack_160_1_framework'],
        ];
    }
    /**
     * @test
     * @dataProvider getProductIdByProductSubIdParams
     */
    public function getProductIdByProductSubId_プロダクトIDを取得($billingPlatform, $expectedProductId)
    {
        // Setup
        MstStoreProduct::factory()->createMockData();
        OprProduct::factory()->createMockData();

        // Exercise
        $productId = $this->appShopService->getProductIdByProductSubId('edmo_pack_160_1_framework', $billingPlatform);

        // Verify
        $this->assertEquals($expectedProductId, $productId);
    }

    public static function params_test_calcStorePaidPriceNextResetAt_年齢から次の課金額リセット日時を計算する()
    {
        return [
            '18歳未満_年またぎ' => [15, "2020-12-01 00:00:00", '2020-12-31 15:00:00'],
            '18歳未満_閏年' => [16, "2024-02-29 01:00:00", '2024-02-29 15:00:00'],
            '18歳未満_月跨ぎ' => [17, "2020-11-15 02:14:22", '2020-11-30 15:00:00'],
            '18歳未満_月跨ぎ_境界直前' => [17, "2020-11-30 14:59:59", '2020-11-30 15:00:00'],
            '18歳未満_月跨ぎ_境界値' => [17, "2020-10-31 15:00:00", '2020-11-30 15:00:00'],
            '18歳以上' => [18, "2020-12-01 00:00:00", null],
        ];
    }
    /**
     * @dataProvider params_test_calcStorePaidPriceNextResetAt_年齢から次の課金額リセット日時を計算する
     */
    public function test_calcStorePaidPriceNextResetAt_年齢から次の課金額リセット日時を計算する(int $age, string $nowString, ?string $expectedRenotifyAt)
    {
        // SetUp
        $now = $this->fixTime($nowString);

        // Exercise
        $result = $this->appShopService->calcStorePaidPriceNextResetAt($age, $now);

        // Verify
        $this->assertEquals($expectedRenotifyAt, $result);
    }

    public static function params_test_validateStorePaidPrice_年齢とか金額をチェック()
    {
        return [
            '15歳以下購入可能' => [15, 4900, '100', '2023-10-06 00:00:00', true],
            '15歳以下購入不可' => [15, 4900, '101', '2023-10-06 00:00:00', false],
            '16〜17歳購入可能' => [16, 19900, '100', '2023-10-06 00:00:00', true],
            '16〜17歳購入不可' => [16, 19900, '101', '2023-10-06 00:00:00', false],
            '18歳以上購入可能' => [18, 20000, '1', null, true],
        ];
    }
    /**
     * @dataProvider params_test_validateStorePaidPrice_年齢とか金額をチェック
     */
    public function test_validateStorePaidPrice_年齢とか金額をチェック(int $age, int $paidPrice, string $purchasePrice, ?string $renotifyAt, bool $expected)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => $age,
            'paid_price' => $paidPrice,
            'renotify_at' => $renotifyAt,
        ]);

        // Exercise
        //  $expectedがfalseの場合、例外が発生
        if (!$expected) {
            $this->expectException(\Exception::class);
        }
        $this->appShopService->validateStorePaidPrice($usrUserId, $purchasePrice);

        // Verify
        //  例外が発生しなければOK
        $this->assertTrue(true);
    }

    // /**
    //  * @test
    //  */
    // public function getProductList_商品一覧を取得する()
    // {
    //     // Setup
    //     MstStoreProduct::factory()->createMockData();
    //     OprProduct::factory()->createMockData();

    //     // Exercise
    //     $actual = $this->appShopService->getProductList()->keyBy('id');

    //     // Verify
    //     $this->assertCount(5, $actual);

    //     $this->assertEquals('edmo_pack_160_1_framework', $actual['edmo_pack_160_1_framework']->id);
    // }
}
