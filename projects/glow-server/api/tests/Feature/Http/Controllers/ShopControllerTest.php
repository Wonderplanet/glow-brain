<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstPack;
use App\Domain\Resource\Mst\Models\MstShopItem;
use App\Domain\Resource\Mst\Models\MstStoreProduct;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Shop\Entities\CurrencyPurchase;
use App\Domain\Shop\Enums\MstPackCostType;
use App\Domain\Shop\Enums\PackType;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\Enums\ShopItemCostType;
use App\Domain\Shop\Enums\ShopType;
use App\Domain\Shop\Models\UsrShopItem;
use App\Domain\Shop\Models\UsrTradePack;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use App\Exceptions\HttpStatusCode;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\DataProvider;
use WonderPlanet\Domain\Billing\Delegators\BillingDelegator;
use WonderPlanet\Domain\Billing\Models\UsrStoreAllowance;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;

class ShopControllerTest extends BaseControllerTestCase
{
    use FakeStoreReceiptTrait;

    private CurrencyDelegator $currencyDelegator;
    private BillingDelegator $billingDelegator;
    private AppCurrencyDelegator $appCurrencyDelegator;

    protected string $baseUrl = '/api/shop/';

    public function setUp(): void
    {
        parent::setUp();

        $this->currencyDelegator = $this->app->make(CurrencyDelegator::class);
        $this->billingDelegator = $this->app->make(BillingDelegator::class);
        $this->appCurrencyDelegator = $this->app->make(AppCurrencyDelegator::class);
    }

    /**
     * @test
     */
    public function allowance_購入許可登録されること()
    {
        // Setup
        $url = 'allowance';
        $params = [
            'productSubId' => 'edmo_pack_160_1_framework',
            'productId' => 'ios_edmo_pack_160_1_framework',
            'currencyCode' => 'JPY',
            'price' => '100.000',
        ];
        MstStoreProduct::factory()->createMockData();
        OprProduct::factory()->createMockData();
        $usrUserId = $this->createUsrUser()->getId();

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '1980-07-01',
        ]);

        // 購入管理データを作成
        $this->currencyDelegator->createUser(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
            0
        );

        // 年齢確認用ショップデータを作成
        $this->billingDelegator->setStoreInfo($usrUserId, 20, null);

        // Exercise
        $response = $this->sendRequest($url, $params);

        // Verify
        $response
            ->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertEquals('edmo_pack_160_1_framework', $response->json('productSubId'));
        $this->assertEquals('ios_edmo_pack_160_1_framework', $response->json('productId'));
    }

    public static function params_test_getStoreInfo_ショップ情報取得()
    {
        return [
            'usr_store_infosレコードがない' => [
                'age' => null,
                'paid_price' => 0,
                'renotify_at' => null,
            ],
            'usr_store_infosレコードがある 上限なしで再通知なし' => [
                'age' => 20,
                'paid_price' => 123,
                'renotify_at' => null,
            ],
            'usr_store_infosレコードがある 上限ありで再通知あり' => [
                'age' => 13,
                'paid_price' => 456,
                'renotify_at' => '2025-07-31 15:00:00',
            ],
        ];
    }

    #[DataProvider('params_test_getStoreInfo_ショップ情報取得')]
    public function test_getStoreInfo_ショップ情報取得(
        ?int $age = null,
        ?int $paid_price = null,
        ?string $renotify_at = null
    ) : void {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $this->fixTime('2025-07-10 00:00:00');

        $usrStoreInfo = null;
        if ($age !== null) {
            $usrStoreInfo = UsrStoreInfo::factory()->create([
                'usr_user_id' => $usrUserId,
                'age' => $age,
                'paid_price' => $paid_price,
                'renotify_at' => $renotify_at,
            ]);
        }

        // Exercise
        $response = $this->sendGetRequest('get_store_info', []);

        // Verify
        $response
            ->assertStatus(HttpStatusCode::SUCCESS);

        $this->assertArrayHasKey('usrStoreInfo', $response);
        $actual = $response['usrStoreInfo'];
        if ($usrStoreInfo !== null) {
            $this->assertEquals($usrStoreInfo->age, $actual['age']);
            $this->assertEquals($usrStoreInfo->paid_price, $actual['currentMonthTotalBilling']);
            $this->assertEquals(StringUtil::convertToISO8601($usrStoreInfo->renotify_at), $actual['renotifyAt']);
        } else {
            $this->assertNull($actual['age']);
            $this->assertEquals(0, $actual['currentMonthTotalBilling']);
            $this->assertNull($actual['renotifyAt']);
        }
    }

    public function test_setStoreInfo_ショップ情報設定()
    {
        // Setup
        $this->fixTime('2024-07-01 00:00:00');
        $intBirthDate = 20040701; // 固定時間と比較して20歳になる日付

        $usrUserId = $this->createUsrUser()->getId();

        UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => 0,
            'paid_price' => 123,
            'renotify_at' => null,
        ]);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '', // 初期化時は空文字
        ]);

        // Exercise
        $response = $this->sendRequest('set_store_info', [
            'birthDate' => $intBirthDate,
        ]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertArrayHasKey('usrStoreInfo', $response);
        $actual = $response['usrStoreInfo'];
        $this->assertEquals(20, $actual['age']);
        $this->assertEquals(0, $actual['currentMonthTotalBilling']); // 年齢設定時は課金額がリセットされる
        $this->assertNull($actual['renotifyAt']);
    }

    /**
     * @test
     */
    public function tradeShopItem_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        $usrUserId = $this->createUsrUser([
            'tutorial_status' => 0,
        ])->getId();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 1000,
        ]);
        $this->createDiamond($usrUserId, freeDiamond: 0);
        $mstShopItem = MstShopItem::factory()->create([
            'id' => fake()->uuid(),
            'shop_type' => ShopType::DAILY->value,
            'cost_type' => ShopItemCostType::COIN->value,
            'cost_amount' => 100,
            'is_first_time_free' => 0,
            'tradable_count' => 1,
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => null,
            'resource_amount' => 100,
            'start_date' => '2000-01-01 00:00:00',
            'end_date' => '2030-01-01 00:00:00'
        ])->toEntity();
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10,
        ]);

        $response = $this->sendRequest('trade_shop_item', ['mstShopItemId' => $mstShopItem->getId()]);
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $this->assertArrayHasKey('usrShopItems', $response);
        $usrShopItem = $response['usrShopItems'][0];
        $this->assertEquals($mstShopItem->getId(), $usrShopItem['mstShopItemId']);
        $this->assertEquals(1, $usrShopItem['tradeCount']);
        $this->assertEquals(1, $usrShopItem['tradeTotalCount']);

        $this->assertArrayHasKey('usrParameter', $response);
        $this->assertEquals($usrUserParameter->getCoin() - $mstShopItem->getCostAmount(), $response['usrParameter']['coin']);
        $this->assertEquals($mstShopItem->getResourceAmount(), $response['usrParameter']['freeDiamond']);

        $this->assertArrayHasKey('usrItems', $response);
    }

    /**
     * @test
     */
    public function tradeShopItem_交換上限に達している場合299が返る()
    {
        $usrUserId = $this->createUsrUser([
            'tutorial_status' => 0,
        ])->getId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 100,
        ]);
        $mstShopItem = MstShopItem::factory()->create([
            'id' => fake()->uuid(),
            'shop_type' => ShopType::DAILY->value,
            'cost_type' => ShopItemCostType::COIN->value,
            'cost_amount' => 100,
            'is_first_time_free' => 0,
            'tradable_count' => 1,
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => null,
            'resource_amount' => 100,
            'start_date' => '2000-01-01 00:00:00',
            'end_date' => '2030-01-01 00:00:00'
        ])->toEntity();
        UsrShopItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_shop_item_id' => $mstShopItem->getId(),
            'trade_count' => $mstShopItem->getTradableCount(),
            'trade_total_count' => 1,
            'last_reset_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->sendRequest('trade_shop_item', ['mstShopItemId' => $mstShopItem->getId()]);
        $response->assertStatus(HttpStatusCode::ERROR);
        $response->assertJson([
            'errorCode' => ErrorCode::SHOP_TRADE_COUNT_LIMIT,
        ]);
    }

    /**
     * @test
     */
    public function tradeShopItem_交換コスト不足場合299が返る()
    {
        $usrUserId = $this->createUsrUser([
            'tutorial_status' => 0,
        ])->getId();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10,
        ]);
        $mstShopItem = MstShopItem::factory()->create([
            'id' => fake()->uuid(),
            'shop_type' => ShopType::DAILY->value,
            'cost_type' => ShopItemCostType::COIN->value,
            'cost_amount' => 100,
            'is_first_time_free' => 0,
            'tradable_count' => 1,
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => null,
            'resource_amount' => 100,
            'start_date' => '2000-01-01 00:00:00',
            'end_date' => '2030-01-01 00:00:00'
        ])->toEntity();

        $response = $this->sendRequest('trade_shop_item', ['mstShopItemId' => $mstShopItem->getId()]);
        $response->assertStatus(HttpStatusCode::ERROR);
        $response->assertJson([
            'errorCode' => ErrorCode::LACK_OF_RESOURCES,
        ]);
    }

    public function testTradePack_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 100,
        ]);

        $deviceId = $usrUserId.' device';
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        $productId = 'edmo_pack_160_1_framework'; // mst_store_products.product_id_ios
        $storeProductId = 'edmo_pack_160_1_framework'; // mst_store_products.id
        $productSubId = 'edmo_pack_160_1_framework'; // opr_products.id

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '1980-07-01',
        ]);

        // 課金基盤関連
        // ダイヤ所持数を0で用意
        $this->createDiamond($usrUserId, paidDiamondIos: 100);
        UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => 20,
        ]);
        UsrStoreAllowance::factory()->create([
            'usr_user_id' => $usrUserId,
            'os_platform' => $osPlatform,
            'billing_platform' => $billingPlatform,
            'product_id' => $productId,
            'mst_store_product_id' => $storeProductId,
            'product_sub_id' => $productSubId,
            'device_id' => $deviceId,
        ]);

        // mst,opr
        MstStoreProduct::factory()->createMockData();
        OprProduct::factory()->create([
            'id' => $productSubId,
            'product_type' => \App\Domain\Shop\Enums\ProductType::PACK->value,
            'mst_store_product_id' => $storeProductId,
            'paid_amount' => 100,
        ]);
        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $productSubId,
            'pack_type'=> PackType::NORMAL->value,
            'sale_condition' => null,
            'cost_type' => MstPackCostType::PAID_DIAMOND->value,
            'cost_amount' => 100,
        ])->toEntity();

        // Exercise
        $response = $this->sendRequest('trade_pack', ['productSubId' => $productSubId]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $this->assertArrayHasKey('usrParameter', $response);
        $this->assertArrayHasKey('usrItems', $response);
        $this->assertArrayHasKey('usrUnits', $response);
    }



    public function testTradePack_Dailyリクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 100,
        ]);

        $deviceId = $usrUserId.' device';
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        $productId = 'edmo_pack_160_1_framework'; // mst_store_products.product_id_ios
        $storeProductId = 'edmo_pack_160_1_framework'; // mst_store_products.id
        $productSubId = 'edmo_pack_160_1_framework'; // opr_products.id

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '1980-07-01',
        ]);

        // 課金基盤関連
        // ダイヤ所持数を0で用意
        $this->createDiamond($usrUserId, paidDiamondIos: 100);
        UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => 20,
        ]);
        UsrStoreAllowance::factory()->create([
            'usr_user_id' => $usrUserId,
            'os_platform' => $osPlatform,
            'billing_platform' => $billingPlatform,
            'product_id' => $productId,
            'mst_store_product_id' => $storeProductId,
            'product_sub_id' => $productSubId,
            'device_id' => $deviceId,
        ]);

        // mst,opr
        MstStoreProduct::factory()->createMockData();
        // パック用のOprProductを個別作成
        OprProduct::factory()->create([
            'id' => $productSubId,
            'product_type' => ProductType::PACK->value,
            'mst_store_product_id' => $storeProductId,
            'paid_amount' => 100,
        ]);
        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $productSubId,
            'pack_type'=> PackType::DAILY->value,
            'sale_condition' => null,
            'cost_type' => MstPackCostType::DIAMOND->value,
            'tradable_count' => 3,
            'cost_amount' => 100,
        ])->toEntity();

        UsrTradePack::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_pack_id' => $mstPack->getId(),
            'daily_trade_count' => 1,
            'last_reset_at' => now()->format('Y-m-d H:i:s'),
        ]);

        // Exercise
        $response = $this->sendRequest('trade_pack', ['productSubId' => $productSubId]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $this->assertArrayHasKey('usrParameter', $response);
        $this->assertArrayHasKey('usrItems', $response);
        $this->assertArrayHasKey('usrUnits', $response);
        $this->assertArrayHasKey('usrTradePacks', $response);
    }

    public function test_purchaseHistory_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        $now = $this->fixTime('2025-010-10 00:00:00');

        // テストユーザー作成
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $this->currencyDelegator->createUser(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
            0
        );

        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $rawPriceString = '$100.00';
        $purchaseAmount = 1000;
        $currencyCode = 'USD';
        $currencyPurchases = collect([
            new CurrencyPurchase(
                $rawPriceString,
                $purchaseAmount,
                $currencyCode,
                $now->subDay()->toDateTimeString(),
            ),
            new CurrencyPurchase(
                $rawPriceString,
                $purchaseAmount + 1,
                $currencyCode,
                $now->subDays(2)->toDateTimeString(),
            ),
        ]);
        $purchaseHistories = collect([$billingPlatform => $currencyPurchases]);
        $key = CacheKeyUtil::getShopPurchaseHistoryKey($usrUserId);
        Redis::connection()->set($key, serialize($purchaseHistories));

        // Exercise
        $response = $this->actingAs($this->createDummyUser())
            ->withHeaders([
                'Platform' => UserConstant::PLATFORM_IOS,
                'BillingPlatform' => CurrencyConstants::PLATFORM_APPSTORE,
                'DeviceId' => $usrUserId . ' device',
            ])
            ->sendGetRequest('purchase_history');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $this->assertArrayHasKey('currencyPurchases', $response);
        $this->assertCount(2, $response['currencyPurchases']);

        $currencyPurchase = $response['currencyPurchases'][0];
        $this->assertEquals($rawPriceString, $currencyPurchase['purchasePrice']);
        $this->assertEquals($purchaseAmount, $currencyPurchase['purchaseAmount']);
        $this->assertEquals($currencyCode, $currencyPurchase['currencyCode']);
        $this->assertEquals(StringUtil::convertToISO8601($now->subDay()->toDateTimeString()), $currencyPurchase['purchaseAt']);

        $currencyPurchase = $response['currencyPurchases'][1];
        $this->assertEquals($rawPriceString, $currencyPurchase['purchasePrice']);
        $this->assertEquals($purchaseAmount + 1, $currencyPurchase['purchaseAmount']);
        $this->assertEquals($currencyCode, $currencyPurchase['currencyCode']);
        $this->assertEquals(StringUtil::convertToISO8601($now->subDays(2)->toDateTimeString()), $currencyPurchase['purchaseAt']);
    }

    /**
     * オファーコード商品(id=49)の購入時、purchasePriceとrawPriceStringが強制的に0円になることを確認
     */
    public function test_purchase_オファーコード商品の場合価格が0円に強制されること()
    {
        // Setup
        $this->fixTime('2025-01-10 00:00:00');

        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();

        // UsrUserParameter作成（必須）
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 100,
        ]);

        // 未成年ユーザーの設定（累積課金額が正しく記録されるかを確認するため）
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '2010-01-01', // 15歳
        ]);

        $deviceId = $usrUserId . ' device';
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        // オファーコード商品の設定
        $productId = 'BNEI0434_offerfreediamond150'; // mst_store_products.product_id_ios
        $storeProductId = 'offer_free_diamond_150'; // mst_store_products.id
        $productSubId = '49'; // opr_products.id (オファーコード商品)

        // 課金基盤関連
        $this->createDiamond($usrUserId);
        UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => 15,
            'paid_price' => 0,
            'renotify_at' => now()->toDateTimeString(),
        ]);
        UsrStoreAllowance::factory()->create([
            'usr_user_id' => $usrUserId,
            'os_platform' => $osPlatform,
            'billing_platform' => $billingPlatform,
            'product_id' => $productId,
            'mst_store_product_id' => $storeProductId,
            'product_sub_id' => $productSubId,
            'device_id' => $deviceId,
        ]);

        // マスターデータ作成
        MstStoreProduct::factory()->create([
            'id' => $storeProductId,
            'product_id_ios' => $productId,
            'product_id_android' => 'android_' . $storeProductId,
        ]);
        OprProduct::factory()->create([
            'id' => $productSubId,
            'product_type' => ProductType::DIAMOND->value,
            'mst_store_product_id' => $storeProductId,
            'paid_amount' => 150,
        ]);

        // レシート作成（ストア価格320円だが実際の支払いは0円）
        $receipt = $this->makeFakeStoreReceiptString($productId);

        // Exercise
        // クライアントからはストア登録価格（320円）が送られてくる想定
        // trade_packエンドポイント経由でbranchTradePackメソッドからpurchaseメソッドが呼ばれる
        $response = $this->sendRequest('trade_pack', [
            'productSubId' => $productSubId,
            'price' => '320',
            'rawPriceString' => '¥320',
            'currencyCode' => 'JPY',
            'receipt' => $receipt,
        ]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // UsrStoreInfoの累積課金額が0のままであることを確認（320円加算されていないこと）
        $usrStoreInfo = UsrStoreInfo::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(0, $usrStoreInfo->paid_price, 'オファーコード商品の累積課金額が0円であること');

        // log_storeの記録を確認
        $logStore = \WonderPlanet\Domain\Billing\Models\LogStore::query()
            ->where('usr_user_id', $usrUserId)
            ->latest()
            ->first();
        $this->assertNotNull($logStore);
        $this->assertEquals(0, (float)$logStore->purchase_price, 'log_storeのpurchase_priceが0円であること');
        $this->assertEquals('¥0', $logStore->raw_price_string, 'log_storeのraw_price_stringが¥0であること');

        // usr_store_product_historyの記録を確認
        $usrStoreProductHistory = \WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory::query()
            ->where('usr_user_id', $usrUserId)
            ->latest()
            ->first();
        $this->assertNotNull($usrStoreProductHistory);
        $this->assertEquals(0, (float)$usrStoreProductHistory->purchase_price, 'usr_store_product_historyのpurchase_priceが0円であること');
    }

    /**
     * 通常商品（オファーコード商品以外）の購入時、価格がそのまま記録されることを確認
     */
    public function test_purchase_通常商品の場合価格がそのまま記録されること()
    {
        // Setup
        $this->fixTime('2025-01-10 00:00:00');

        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();

        // UsrUserParameter作成（必須）
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 100,
        ]);

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '2010-01-01', // 15歳
        ]);

        $deviceId = $usrUserId . ' device';
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        // 通常のダイヤ商品の設定
        $productId = 'ios_diamond_100';
        $storeProductId = 'diamond_100';
        $productSubId = '10'; // オファーコード商品(49)以外

        // 課金基盤関連
        $this->createDiamond($usrUserId);
        UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => 15,
            'paid_price' => 0,
            'renotify_at' => now()->toDateTimeString(),
        ]);
        UsrStoreAllowance::factory()->create([
            'usr_user_id' => $usrUserId,
            'os_platform' => $osPlatform,
            'billing_platform' => $billingPlatform,
            'product_id' => $productId,
            'mst_store_product_id' => $storeProductId,
            'product_sub_id' => $productSubId,
            'device_id' => $deviceId,
        ]);

        // マスターデータ作成
        MstStoreProduct::factory()->create([
            'id' => $storeProductId,
            'product_id_ios' => $productId,
            'product_id_android' => 'android_' . $storeProductId,
        ]);
        OprProduct::factory()->create([
            'id' => $productSubId,
            'product_type' => ProductType::DIAMOND->value,
            'mst_store_product_id' => $storeProductId,
            'paid_amount' => 100,
        ]);

        $receipt = $this->makeFakeStoreReceiptString($productId);

        // Exercise
        // trade_packエンドポイント経由でbranchTradePackメソッドからpurchaseメソッドが呼ばれる
        $response = $this->sendRequest('trade_pack', [
            'productSubId' => $productSubId,
            'price' => '320',
            'rawPriceString' => '¥320',
            'currencyCode' => 'JPY',
            'receipt' => $receipt,
        ]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // UsrStoreInfoの累積課金額が320円加算されていることを確認
        $usrStoreInfo = UsrStoreInfo::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(320, $usrStoreInfo->paid_price, '通常商品の累積課金額が320円であること');

        // log_storeの記録を確認
        $logStore = \WonderPlanet\Domain\Billing\Models\LogStore::query()
            ->where('usr_user_id', $usrUserId)
            ->latest()
            ->first();
        $this->assertNotNull($logStore);
        $this->assertEquals(320, (float)$logStore->purchase_price, 'log_storeのpurchase_priceが320円であること');
        $this->assertEquals('¥320', $logStore->raw_price_string, 'log_storeのraw_price_stringが¥320であること');

        // usr_store_product_historyの記録を確認
        $usrStoreProductHistory = \WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory::query()
            ->where('usr_user_id', $usrUserId)
            ->latest()
            ->first();
        $this->assertNotNull($usrStoreProductHistory);
        $this->assertEquals(320, (float)$usrStoreProductHistory->purchase_price, 'usr_store_product_historyのpurchase_priceが320円であること');
    }
}
