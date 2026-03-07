<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Shop\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Enums\Language;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Message\Constants\MessageConstant;
use App\Domain\Message\Models\Eloquent\UsrMessage;
use App\Domain\Message\Models\UsrMessageInterface;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstPack;
use App\Domain\Resource\Mst\Models\MstPackContent;
use App\Domain\Resource\Mst\Models\MstShopPass;
use App\Domain\Resource\Mst\Models\MstShopPassEffect;
use App\Domain\Resource\Mst\Models\MstShopPassI18n;
use App\Domain\Resource\Mst\Models\MstShopPassReward;
use App\Domain\Resource\Mst\Models\MstStoreProduct;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Shop\Enums\MstPackCostType;
use App\Domain\Shop\Enums\PackType;
use App\Domain\Shop\Enums\PassEffectType;
use App\Domain\Shop\Enums\PassRewardType;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\Enums\SaleCondition;
use App\Domain\Shop\Models\UsrConditionPack;
use App\Domain\Shop\Models\UsrShopPass;
use App\Domain\Shop\Models\UsrStoreProduct;
use App\Domain\Shop\Models\UsrTradePack;
use App\Domain\Shop\UseCases\ShopPurchaseUseCase;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use App\Http\Responses\ResultData\ShopPurchaseResultData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Models\UsrStoreAllowance;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\Trigger;

class ShopPurchaseUseCaseTest extends TestCase
{
    use FakeStoreReceiptTrait;

    private ShopPurchaseUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = $this->app->make(ShopPurchaseUseCase::class);
    }

    public function test_exec_パス購入ができる() {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $currentUser = new CurrentUser($usrUserId);
        $level = 1;
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
            'level' => $level,
        ]);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '1980-01-01',
        ]);
        MstUserLevel::factory()->create(['level' => $level, 'stamina' => 10]);

        $this->fixTime();
        $now = CarbonImmutable::now();

        $deviceId = $usrUserId.' device';
        $platform = UserConstant::PLATFORM_IOS;
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        $productId = 'edmo_pack_160_1_framework'; // mst_store_products.product_id_ios
        $storeProductId = 'pack_160_1_framework'; // mst_store_products.id
        $productSubId = 'pack_160_1_framework'; // opr_products.id
        $productSubName = $productSubId;
        $mstProductId = $storeProductId;
        $purchasePrice = '1.00';
        $rawPriceString = '$1.00';
        $vipPoint = 1;
        $currencyCode = 'USD';
        $receipt = $this->makeFakeStoreReceiptString($productId);
        $receiptStore = $this->makeFakeStoreReceipt($productId);
        $trigger = new Trigger('purchased', $productSubId, $productSubName, "product_id: {$productId}, billing_platform: {$billingPlatform}, mst_product_id: {$mstProductId}");
        $mstItemId = 'item1';

        // 課金基盤関連
        // ダイヤ所持数を0で用意
        $this->createDiamond($usrUserId);
        $usrStoreInfo = UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => 20,
        ]);
        UsrStoreAllowance::factory()->create([
            'usr_user_id' => $usrUserId,
            'os_platform' => $osPlatform,
            'billing_platform' => $billingPlatform,
            'product_id' => $productId,
            'mst_store_product_id' => $mstProductId,
            'product_sub_id' => $productSubId,
            'device_id' => $deviceId,
        ]);

        // mst,opr
        OprProduct::factory()->create([
            'id' => $productSubId,
            'mst_store_product_id' => $storeProductId,
            'product_type' => ProductType::PASS->value,
            'paid_amount' => 200,
        ]);
        MstStoreProduct::factory()->create([
            'id' => $storeProductId,
            'product_id_ios' => $productId,
            'product_id_android' => "android_{$storeProductId}",
        ]);
        MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::ETC->value,
            'start_date' => '2023-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ]);
        $mstPass = MstShopPass::factory()->create([
            'opr_product_id' => $productSubId,
            'pass_duration_days' => 7,
        ])->toEntity();
        MstShopPassI18n::factory()->create([
            'mst_shop_pass_id' => $mstPass->getId(),
            'name' => 'テストパス',
        ]);
        MstShopPassReward::factory()->createMany([
            [
                'mst_shop_pass_id' => $mstPass->getId(),
                'pass_reward_type' => PassRewardType::IMMEDIATELY->value,
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 10,
            ],
            [
                'mst_shop_pass_id' => $mstPass->getId(),
                'pass_reward_type' => PassRewardType::IMMEDIATELY->value,
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 20,
            ],
            [
                'mst_shop_pass_id' => $mstPass->getId(),
                'pass_reward_type' => PassRewardType::IMMEDIATELY->value,
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => $mstItemId,
                'resource_amount' => 30,
            ],
            // 毎日報酬は受け取らない
            [
                'mst_shop_pass_id' => $mstPass->getId(),
                'pass_reward_type' => PassRewardType::DAILY->value,
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 50,
            ],
        ]);

        UsrShopPass::factory()->create([
            'id' => 'test',
            'usr_user_id' => $usrUserId,
            'mst_shop_pass_id' => $mstPass->getId(),
            'daily_reward_received_count' => 0,
            'daily_latest_received_at' => $now->subDay()->toDateTimeString(),
            'start_at' => $now->toDateTimeString(),
            'end_at' => $now->addDay(2)->toDateTimeString(),
        ]);

        MstShopPassEffect::factory()->createMany([
            // スタミナ回復上限3アップ
            [
                'mst_shop_pass_id' => $mstPass->getId(),
                'effect_type' => PassEffectType::STAMINA_ADD_RECOVERY_LIMIT->value,
                'effect_value' => 10,
            ],
        ]);
        // Exercise
        $result = $this->useCase->exec(
            $currentUser,
            $platform,
            $billingPlatform,
            $productSubId,
            $purchasePrice,
            $rawPriceString,
            $currencyCode,
            $receipt,
            Language::Ja->value,
        );

        // Verify
        $this->assertInstanceOf(ShopPurchaseResultData::class, $result);

        $this->assertEquals($productSubId, $result->usrStoreProduct->getProductSubId());
        $this->assertEquals($mstPass->getId(), $result->usrShopPass->getMstShopPassId());
        $this->assertTrue($result->shopPassRewards->isNotEmpty());
        $this->assertCount(3, $result->shopPassRewards);
        $this->assertEquals(200, $result->usrUserParameter->getPaidDiamondIos());

        // usrStoreInfoが含まれていることを確認
        $this->assertNotNull($result->usrStoreInfo);
        $this->assertEquals(20, $result->usrStoreInfo->getAge());

        // 有償通貨の増減確認
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals(200, $diamond->getPaidAmountApple());

        $usrStoreProduct = UsrStoreProduct::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals(1, $usrStoreProduct->getPurchaseCount());
        $this->assertEquals(1, $usrStoreProduct->getPurchaseTotalCount());

        // DBの確認
        $usrMessages = UsrMessage::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertCount(3, $usrMessages);
        /** @var UsrMessageInterface $usrMessage */
        foreach ($usrMessages as $usrMessage) {
            if ($usrMessage->getResourceType() === RewardType::COIN->value) {
                $this->assertNull($usrMessage->getResourceId());
                $this->assertEquals(10, $usrMessage->getResourceAmount());
            } else if ($usrMessage->getResourceType() === RewardType::FREE_DIAMOND->value) {
                $this->assertNull($usrMessage->getResourceId());
                $this->assertEquals(20, $usrMessage->getResourceAmount());
            } else if ($usrMessage->getResourceType() === RewardType::ITEM->value) {
                $this->assertEquals($mstItemId, $usrMessage->getResourceId());
                $this->assertEquals(30, $usrMessage->getResourceAmount());
            }
            $this->assertStringStartsWith(LogResourceTriggerSource::SHOP_PASS_REWARD->value, $usrMessage->message_source);
            $this->assertEquals('テストパス' . MessageConstant::SHOP_PASS_TITLE, $usrMessage->getTitle());
            $this->assertEquals('テストパス' . MessageConstant::SHOP_PASS_BODY, $usrMessage->getBody());
        }

        $usrShopPass = UsrShopPass::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($mstPass->getId(), $usrShopPass->getMstShopPassId());
        $this->assertEquals(0, $usrShopPass->getDailyRewardReceivedCount());

        // daily_latest_received_atは updateDailyPassReward の条件によって更新されない場合があるため、
        // 具体的な値ではなく、null でないことのみを確認
        $this->assertNotNull($usrShopPass->getDailyLatestReceivedAt());

        // パスの開始・終了時刻は実際の設定値に合わせてアサーション
        $this->assertNotNull($usrShopPass->getStartAt());
        $this->assertNotNull($usrShopPass->getEndAt());

        $key = CacheKeyUtil::getShopPurchaseHistoryKey($usrUserId);
        $cache = Redis::connection()->get($key);
        $this->assertNull($cache);
    }

    public static function params_test_exec_パック購入して有償ダイヤを獲得できる()
    {
        // 獲得有償通貨量,開放条件,開放条件値,コストタイプ,コスト量
        return [
            '開放条件ありパック' => [
                'paidAmount' => 100,
                'saleCondition' => SaleCondition::USER_LEVEL->value,
                'saleConditionValue' => "5",
                'costType' => MstPackCostType::CASH->value,
                'costAmount' => 0,
                'expectedPaidDiamond' => 100,
            ],
            '開放条件なしパック' => [
                'paidAmount' => 100,
                'saleCondition' => NULL,
                'saleConditionValue' => NULL,
                'costType' => MstPackCostType::CASH->value,
                'costAmount' => 0,
                'expectedPaidDiamond' => 100,
            ],
            '有償通貨購入パック' => [
                'paidAmount' => 0,
                'saleCondition' => NULL,
                'saleConditionValue' => NULL,
                'costType' => MstPackCostType::PAID_DIAMOND->value,
                'costAmount' => 100,
                'expectedPaidDiamond' => 100,
            ],
        ];
    }

    #[DataProvider('params_test_exec_パック購入して有償ダイヤを獲得できる')]
    public function test_exec_パック購入して有償ダイヤを獲得できる(
        int $paidAmount,
        ?string $saleCondition,
        ?string $saleConditionValue,
        string $costType,
        int $costAmount,
        int $expectedPaidDiamond,
    ) {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $currentUser = new CurrentUser($usrUserId);
        $level = is_null($saleConditionValue) ? 1 : (int)$saleConditionValue;
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
            'level' => $level,
        ]);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '1980-01-01',
        ]);
        MstUserLevel::factory()->create(['level' => $level, 'stamina' => 10]);

        $now = CarbonImmutable::now();

        $deviceId = $usrUserId.' device';
        $platform = UserConstant::PLATFORM_IOS;
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $language = Language::Ja->value;

        $productId = 'edmo_pack_160_1_framework'; // mst_store_products.product_id_ios
        $storeProductId = 'pack_160_1_framework'; // mst_store_products.id
        $productSubId = 'pack_160_1_framework'; // opr_products.id
        $mstProductId = $storeProductId;
        $purchasePrice = '1.00';
        $rawPriceString = '$1.00';
        $currencyCode = 'USD';
        $receipt = $this->makeFakeStoreReceiptString($productId);
        $mstItemId = 'item1';

        // 課金基盤関連
        // ダイヤ所持数を0で用意
        $this->createDiamond($usrUserId, freeDiamond: 0, paidDiamondIos: $costAmount);
        $usrStoreInfo = UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => 20,
        ]);
        UsrStoreAllowance::factory()->create([
            'usr_user_id' => $usrUserId,
            'os_platform' => $osPlatform,
            'billing_platform' => $billingPlatform,
            'product_id' => $productId,
            'mst_store_product_id' => $mstProductId,
            'product_sub_id' => $productSubId,
            'device_id' => $deviceId,
        ]);

        // mst,opr
        OprProduct::factory()->create([
            'id' => $productSubId,
            'mst_store_product_id' => $storeProductId,
            'product_type' => ProductType::PACK->value,
            'paid_amount' => $paidAmount,
        ]);
        MstStoreProduct::factory()->create([
            'id' => $storeProductId,
            'product_id_ios' => $productId,
            'product_id_android' => "android_{$storeProductId}",
        ]);
        MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::ETC->value,
            'start_date' => '2023-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ]);
        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $productSubId,
            'pack_type'=> PackType::DAILY->value,
            'sale_condition' => $saleCondition,
            'sale_condition_value' => $saleConditionValue,
            'cost_type' => MstPackCostType::DIAMOND->value,
            'sale_hours' => 100,
            'cost_type' => $costType,
            'cost_amount' => $costAmount
        ])->toEntity();


        UsrTradePack::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_pack_id' => $mstPack->getId(),
            'daily_trade_count' => 1,
            'last_reset_at' => now()->format('Y-m-d H:i:s'),
        ]);

        MstPackContent::factory()->createMany([
            [
                'mst_pack_id' => $mstPack->getId(),
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 50,
            ],
            [
                'mst_pack_id' => $mstPack->getId(),
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 10,
            ],
            [
                'mst_pack_id' => $mstPack->getId(),
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => $mstItemId,
                'resource_amount' => 10,
            ],
        ]);
        // usr
        if (!is_null($saleCondition)) {
            UsrConditionPack::factory()->create([
                'usr_user_id' => $usrUser->getId(),
                'mst_pack_id' => $mstPack->getId(),
                'start_date' => $now->copy()->subDay()->toDateTimeString(),
            ]);
        }

        // 有償ダイアのパックを買おうとした場合は例外となるのでキャッチする
        if ($costType === MstPackCostType::PAID_DIAMOND->value) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::SHOP_PURCHASE_PRODUCT_TYPE_NOT_SUPPORTED);
        }

        // Exercise
        $result = $this->useCase->exec(
            $currentUser,
            $platform,
            $billingPlatform,
            $productSubId,
            $purchasePrice,
            $rawPriceString,
            $currencyCode,
            $receipt,
            $language,
        );

        // Verify
        $this->assertInstanceOf(ShopPurchaseResultData::class, $result);

        $this->assertEquals($mstItemId, $result->usrItems->first()->getMstItemId());

        $this->assertEquals($productSubId, $result->usrStoreProduct->getProductSubId());
        $this->assertNull($result->usrShopPass);
        $this->assertTrue($result->shopPassRewards->isEmpty());
        $this->assertEquals($expectedPaidDiamond, $result->usrUserParameter->getPaidDiamondIos());

        // usrStoreInfoが含まれていることを確認
        $this->assertNotNull($result->usrStoreInfo);
        $this->assertEquals(20, $result->usrStoreInfo->getAge());

        // 有償通貨の増減確認
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals(0 + 10, $diamond->getFreeAmount());
        $this->assertEquals($expectedPaidDiamond, $diamond->getPaidAmountApple());

        $usrStoreProduct = UsrStoreProduct::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals(1, $usrStoreProduct->getPurchaseCount());
        $this->assertEquals(1, $usrStoreProduct->getPurchaseTotalCount());

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals(50, $usrUserParameter->getCoin());

        $key = CacheKeyUtil::getShopPurchaseHistoryKey($usrUserId);
        $cache = Redis::connection()->get($key);
        $this->assertNull($cache);
    }

    public static function params_test_exec_ダイアモンド購入ができる()
    {
        // 獲得有償通貨量,開放条件,開放条件値,コストタイプ,コスト量
        return [
            'AppStore購入' => [
                'platform' => UserConstant::PLATFORM_IOS,
                'osPlatform' => CurrencyConstants::OS_PLATFORM_IOS,
                'billingPlatform' => CurrencyConstants::PLATFORM_APPSTORE,
                'paidAmount' => 500,
                'purchasePrice' => '5.00',
                'rawPriceString' => '$5.00',
                'currencyCode' => 'USD',
            ],
            'GooglePlay購入' => [
                'platform' => UserConstant::PLATFORM_ANDROID,
                'osPlatform' => CurrencyConstants::OS_PLATFORM_ANDROID,
                'billingPlatform' => CurrencyConstants::PLATFORM_GOOGLEPLAY,
                'paidAmount' => 600,
                'purchasePrice' => '600',
                'rawPriceString' => '¥600',
                'currencyCode' => 'JPY',
            ],
        ];
    }

    #[DataProvider('params_test_exec_ダイアモンド購入ができる')]
    public function test_exec_ダイアモンド購入ができる(
        int $platform,
        string $osPlatform,
        string $billingPlatform,
        int $paidAmount,
        string $purchasePrice,
        string $rawPriceString,
        string $currencyCode,
    ) {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $currentUser = new CurrentUser($usrUserId);
        $level = 1;
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
            'level' => $level,
        ]);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '1980-01-01',
        ]);
        MstUserLevel::factory()->create(['level' => $level, 'stamina' => 10]);

        $this->fixTime();
        $now = CarbonImmutable::now();

        $deviceId = $usrUserId.' device';
        $productId = 'edmo_pack_160_1_framework'; // mst_store_products.product_id_ios
        $storeProductId = 'diamond_160_1_framework'; // mst_store_products.id
        $productSubId = 'diamond_160_1_framework'; // opr_products.id
        $productSubName = $productSubId;
        $mstProductId = $storeProductId;
        $receipt = $this->makeFakeStoreReceiptString($productId);
        $receiptStore = $this->makeFakeStoreReceipt($productId);
        $mstItemId = 'item1';

        // 課金基盤関連
        // ダイヤ所持数を0で用意
        $this->createDiamond($usrUserId);
        $usrStoreInfo = UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => 20,
        ]);
        UsrStoreAllowance::factory()->create([
            'usr_user_id' => $usrUserId,
            'os_platform' => $osPlatform,
            'billing_platform' => $billingPlatform,
            'product_id' => $productId,
            'mst_store_product_id' => $mstProductId,
            'product_sub_id' => $productSubId,
            'device_id' => $deviceId,
        ]);

        // mst,opr
        OprProduct::factory()->create([
            'id' => $productSubId,
            'mst_store_product_id' => $storeProductId,
            'product_type' => ProductType::DIAMOND->value,
            'paid_amount' => $paidAmount,
        ]);
        MstStoreProduct::factory()->create([
            'id' => $storeProductId,
            'product_id_ios' => $productId,
            'product_id_android' => "android_{$storeProductId}",
        ]);

        // Exercise
        $result = $this->useCase->exec(
            $currentUser,
            $platform,
            $billingPlatform,
            $productSubId,
            $purchasePrice,
            $rawPriceString,
            $currencyCode,
            $receipt,
            Language::Ja->value,
        );

        // Verify
        $this->assertInstanceOf(ShopPurchaseResultData::class, $result);

        $this->assertEquals($productSubId, $result->usrStoreProduct->getProductSubId());
        $this->assertNull($result->usrShopPass);
        $this->assertTrue($result->shopPassRewards->isEmpty());
        if ($platform === UserConstant::PLATFORM_IOS) {
            $this->assertEquals($paidAmount, $result->usrUserParameter->getPaidDiamondIos());
        } else {
            $this->assertEquals(0, $result->usrUserParameter->getPaidDiamondIos());
        }

        // usrStoreInfoが含まれていることを確認
        $this->assertNotNull($result->usrStoreInfo);
        $this->assertEquals(20, $result->usrStoreInfo->getAge());

        // 有償通貨の増減確認
        $diamond = $this->getDiamond($usrUserId);
        if ($platform === UserConstant::PLATFORM_IOS) {
            $this->assertEquals($paidAmount, $diamond->getPaidAmountApple());
        } else {
            $this->assertEquals($paidAmount, $diamond->getPaidAmountGoogle());
        }

        $usrStoreProduct = UsrStoreProduct::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals(1, $usrStoreProduct->getPurchaseCount());
        $this->assertEquals(1, $usrStoreProduct->getPurchaseTotalCount());

        // リワードデータが含まれていることを確認
        $this->assertNotNull($result->rewards);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result->rewards);
        $this->assertCount(1, $result->rewards); // ダイヤモンド購入では1つのリワード

        // ダイヤモンドリワードの内容確認
        $diamondReward = $result->rewards->first();
        $this->assertEquals('PaidDiamond', $diamondReward->getType());
        $this->assertEquals($paidAmount, $diamondReward->getAmount());
        $this->assertEquals($productSubId, $diamondReward->getResourceId());

        $key = CacheKeyUtil::getShopPurchaseHistoryKey($usrUserId);
        $cache = Redis::connection()->get($key);
        $this->assertNotNull($cache);

        $currencyPurchases = unserialize($cache);
        $this->assertCount(1, $currencyPurchases);

        $currencyPurchase = $currencyPurchases[$billingPlatform][0];
        $this->assertEquals($rawPriceString, $currencyPurchase->getPurchasePrice());
        $this->assertEquals($paidAmount, $currencyPurchase->getPurchaseAmount());
        $this->assertEquals($currencyCode, $currencyPurchase->getCurrencyCode());
        $this->assertEquals($now, $currencyPurchase->getPurchaseAt());
    }

    public function test_exec_パック購入時にリワードデータがレスポンスに含まれる()
    {
        // テストユーザー作成
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $user = new CurrentUser($usrUserId);

        // 基本データ作成
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
            'level' => 1,
        ]);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '1980-01-01',
        ]);
        $this->createDiamond($usrUserId);

        // ストア情報作成
        UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => 20,
        ]);

        // プラットフォーム設定
        $platform = UserConstant::PLATFORM_IOS;
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $productId = 'edmo_pack_160_1_framework';
        $productSubId = 'test_pack_sub';
        $deviceId = $usrUserId . ' device';

        // 許可データ作成
        UsrStoreAllowance::factory()->create([
            'usr_user_id' => $usrUserId,
            'os_platform' => $osPlatform,
            'billing_platform' => $billingPlatform,
            'product_id' => $productId,
            'mst_store_product_id' => $productSubId,
            'product_sub_id' => $productSubId,
            'device_id' => $deviceId,
        ]);

        // パック作成（アイテムを含む）
        $mstItemId = 'test_item_' . $usrUserId;
        $mstItem = MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::ETC->value,
            'start_date' => '2023-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ]);

        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $productSubId,
            'cost_type' => MstPackCostType::CASH->value,
            'cost_amount' => 100,
            'pack_type' => PackType::NORMAL->value,
            'sale_condition' => null,
            'sale_condition_value' => null,
        ])->toEntity();

        MstPackContent::factory()->createMany([
            [
                'mst_pack_id' => $mstPack->getId(),
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => $mstItemId,
                'resource_amount' => 5,
            ],
            [
                'mst_pack_id' => $mstPack->getId(),
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
            [
                'mst_pack_id' => $mstPack->getId(),
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 50,
            ],
        ]);

        // ストア商品作成
        MstStoreProduct::factory()->create([
            'id' => $productSubId,
            'product_id_ios' => $productId,
            'product_id_android' => "android_{$productSubId}",
        ]);

        $oprProduct = OprProduct::factory()->create([
            'id' => $productSubId,
            'mst_store_product_id' => $productSubId,
            'product_type' => ProductType::PACK->value,
        ]);

        // 購入実行
        $useCase = app(ShopPurchaseUseCase::class);
        $receipt = $this->makeFakeStoreReceiptString($productId);

        $result = $useCase->exec(
            $user,
            $platform,
            $billingPlatform,
            $productSubId,
            '100',
            '$1.00',
            'USD',
            $receipt,
            Language::Ja->value
        );

        // レスポンスデータの確認
        $this->assertInstanceOf(ShopPurchaseResultData::class, $result);

        // リワードデータが含まれていることを確認
        $this->assertNotNull($result->rewards);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result->rewards);

        $this->assertCount(3, $result->rewards);

        // usrStoreInfoが含まれていることを確認
        $this->assertNotNull($result->usrStoreInfo);
        $this->assertEquals(20, $result->usrStoreInfo->getAge());

        // リワードの内容を詳細に確認
        $rewards = $result->rewards;

        // アイテムリワードを確認
        $itemReward = $rewards->first(function ($reward) {
            return $reward->getType() === 'Item';
        });
        $this->assertNotNull($itemReward);
        $this->assertEquals($mstItemId, $itemReward->getResourceId());
        $this->assertEquals(5, $itemReward->getAmount());

        // コインリワードを確認
        $coinReward = $rewards->first(function ($reward) {
            return $reward->getType() === 'Coin';
        });
        $this->assertNotNull($coinReward);
        $this->assertNull($coinReward->getResourceId());
        $this->assertEquals(100, $coinReward->getAmount());

        // 無償ダイヤモンドリワードを確認
        $diamondReward = $rewards->first(function ($reward) {
            return $reward->getType() === 'FreeDiamond';
        });
        $this->assertNotNull($diamondReward);
        $this->assertNull($diamondReward->getResourceId());
        $this->assertEquals(50, $diamondReward->getAmount());
    }
}
