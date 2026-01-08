<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Shop\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Models\MstStoreProduct;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstPack;
use App\Domain\Resource\Mst\Models\MstPackContent;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Shop\Enums\MstPackCostType;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\Enums\SaleCondition;
use App\Domain\Shop\Models\UsrConditionPack;
use App\Domain\Shop\Models\UsrStoreProduct;
use App\Domain\Shop\UseCases\ShopTradePackUseCase;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Http\Responses\ResultData\ShopTradePackResultData;
use Carbon\CarbonImmutable;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Models\UsrStoreAllowance;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use App\Domain\Shop\Enums\PackType;
use App\Domain\Shop\Models\UsrTradePack;
use PHPUnit\Framework\Attributes\DataProvider;

class ShopTradePackUseCaseTest extends TestCase
{
    use FakeStoreReceiptTrait;

    private ShopTradePackUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = $this->app->make(ShopTradePackUseCase::class);
    }

    public static function params_test_exec_パック購入して有償ダイヤを獲得できる()
    {
        // 獲得有償通貨量,開放条件,開放条件値,コストタイプ,コスト量
        return [
            '開放条件ありパック' => [
                'paidAmount' => 100,
                'saleCondition' => SaleCondition::USER_LEVEL->value,
                'saleConditionValue' => "5",
                'costType' => MstPackCostType::PAID_DIAMOND->value,
                'costAmount' => 100,
                'expectedPaidDiamond' => 0,
            ],
            '開放条件なしパック' => [
                'paidAmount' => 100,
                'saleCondition' => NULL,
                'saleConditionValue' => NULL,
                'costType' => MstPackCostType::PAID_DIAMOND->value,
                'costAmount' => 100,
                'expectedPaidDiamond' => 0,
            ],
            '有償通貨購入パック' => [
                'paidAmount' => 0,
                'saleCondition' => NULL,
                'saleConditionValue' => NULL,
                'costType' => MstPackCostType::PAID_DIAMOND->value,
                'costAmount' => 100,
                'expectedPaidDiamond' => 0,
            ],
        ];
    }

    /**
     * @dataProvider params_test_exec_パック購入して有償ダイヤを獲得できる
     */
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

        $productId = 'ios_pack_160_1_framework'; // mst_store_products.product_id_ios
        $storeProductId = 'pack_160_1_framework'; // mst_store_products.id
        $productSubId = 'pack_160_1_framework'; // opr_products.id
        $mstProductId = $storeProductId;
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
            'product_id_ios' => "ios_{$storeProductId}",
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
            'pack_type'=> PackType::NORMAL->value,
            'sale_condition' => $saleCondition,
            'sale_condition_value' => $saleConditionValue,
            'sale_hours' => 100,
            'cost_type' => $costType,
            'cost_amount' => $costAmount
        ])->toEntity();
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

        // 課金購入のパックを買おうとした場合は例外となるのでキャッチする
        if ($costType === MstPackCostType::CASH->value) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::VALIDATION_ERROR);
        }

        // Exercise
        $result = $this->useCase->exec(
            $currentUser,
            $platform,
            $billingPlatform,
            $mstPack->getProductSubId(),
        );

        // Verify
        $this->assertInstanceOf(ShopTradePackResultData::class, $result);

        $this->assertEquals($mstItemId, $result->usrItems->first()->getMstItemId());

        // 有償通貨の増減確認
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals(0 + 10, $diamond->getFreeAmount());
        $this->assertEquals($expectedPaidDiamond, $diamond->getPaidAmountApple());

         if ($costType === MstPackCostType::CASH->value) {
            $usrStoreProduct = UsrStoreProduct::query()->where('usr_user_id', $usrUser->getId())->first();
            $this->assertEquals(1, $usrStoreProduct->getPurchaseCount());
            $this->assertEquals(1, $usrStoreProduct->getPurchaseTotalCount());
        } else {
            $usrTradePack = UsrTradePack::query()
                ->where('usr_user_id', $usrUserId)
                ->where('mst_pack_id', $mstPack->getId())
                ->first();

            $this->assertEquals(1, $usrTradePack->getDailyTradeCount());
        }

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals(50, $usrUserParameter->getCoin());
    }

    public static function params_test_exec_パック購入_正常()
    {
        // 獲得有償通貨量,開放条件,開放条件値,コストタイプ,コスト量
        return [
            'Cost_有償ダイヤ' => [
                'diamondAmount' => 100,
                'paidDiamondAmount' => 100,
                'costType' => MstPackCostType::PAID_DIAMOND->value,
                'costAmount' => 10,
                'afterPaidDiamond' => 90,
                'afterDiamond' => 110,
                'afterCoin' => 150,
            ],
            '無償ダイヤ' => [
                'diamondAmount' => 5,
                'paidDiamondAmount' => 100,
                'costType' => MstPackCostType::DIAMOND->value,
                'costAmount' => 10,
                'afterPaidDiamond' => 95,
                'afterDiamond' => 10,
                'afterCoin' => 150,
            ],
            'AD' => [
                'diamondAmount' => 100,
                'paidDiamondAmount' => 100,
                'costType' => MstPackCostType::AD->value,
                'costAmount' => 0,
                'afterPaidDiamond' => 100,
                'afterDiamond' => 110,
                'afterCoin' => 150,
            ],
            'Free' => [
                'diamondAmount' => 100,
                'paidDiamondAmount' => 100,
                'costType' => MstPackCostType::FREE->value,
                'costAmount' => 0,
                'afterPaidDiamond' => 100,
                'afterDiamond' => 110,
                'afterCoin' => 150,
            ],
        ];
    }
    #[DataProvider('params_test_exec_パック購入_正常')]
    public function test_exec_パック購入_正常(
        int $diamondAmount,
        int $paidDiamondAmount,
        string $costType,
        int $costAmount,
        int $afterPaidDiamond,
        int $afterDiamond,
        int $afterCoin,
    ) : void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $currentUser = new CurrentUser($usrUserId);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 100,
        ]);

        $platform = UserConstant::PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        $productSubId = 'pack_160_1_framework'; // opr_products.id

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '1980-07-01',
        ]);

        // ダイヤ所持数
        $this->createDiamond($usrUserId, freeDiamond: $diamondAmount, paidDiamondIos: $paidDiamondAmount);

        $mstItemId = 'item1';
        MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::ETC->value,
            'start_date' =>  now()->format('Y-m-d H:i:s'),
            'end_date' =>  now()->addDays(1)->format('Y-m-d H:i:s'),
        ]);

        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $productSubId,
            'pack_type'=> PackType::DAILY->value,
            'sale_condition' => null,
            'cost_type' => $costType,
            'tradable_count' => 5,
            'cost_amount' => $costAmount,
        ])->toEntity();

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

        // OprProduct作成
        MstStoreProduct::factory()->create([
            'id' => $productSubId,
            'product_id_ios' => "ios_{$productSubId}",
            'product_id_android' => "android_{$productSubId}",
        ]);
        OprProduct::factory()->create([
            'id' => $productSubId,
            'mst_store_product_id' => $productSubId, // 簡略化のため同じIDを使用
            'product_type' => ProductType::PACK->value,
            'paid_amount' => 0,
        ]);

        UsrTradePack::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_pack_id' => $mstPack->getId(),
            'daily_trade_count' => 1,
            'last_reset_at' => now()->format('Y-m-d H:i:s'),
        ]);

        // Exercise
       $result = $this->useCase->exec(
            $currentUser,
            $platform,
            $billingPlatform,
            $mstPack->getProductSubId(),
        );

        // Verify
        // Rewards の確認
        $this->assertNotNull($result->rewards);
        $this->assertCount(3, $result->rewards); // COIN, FREE_DIAMOND, ITEM の3つ

        // BaseRewardオブジェクトなので、getType()メソッドを使用
        $rewardTypes = $result->rewards->map(function($reward) {
            return $reward->getType();
        })->toArray();
        
        $this->assertContains('Coin', $rewardTypes);
        $this->assertContains('FreeDiamond', $rewardTypes);
        $this->assertContains('Item', $rewardTypes);
        
        // 各報酬の内容確認
        $coinReward = $result->rewards->firstWhere(function($reward) {
            return $reward->getType() === 'Coin';
        });
        $this->assertEquals(50, $coinReward->getAmount());
        
        $diamondReward = $result->rewards->firstWhere(function($reward) {
            return $reward->getType() === 'FreeDiamond';
        });
        $this->assertEquals(10, $diamondReward->getAmount());
        
        $itemReward = $result->rewards->firstWhere(function($reward) {
            return $reward->getType() === 'Item';
        });
        $this->assertEquals(10, $itemReward->getAmount());
        $this->assertEquals($mstItemId, $itemReward->getResourceId());

        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals($afterDiamond, $diamond->getFreeAmount());
        $this->assertEquals($afterPaidDiamond, $diamond->getPaidAmountApple());

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals($afterCoin, $usrUserParameter->getCoin());

        $usrItem = UsrItem::query()->where('usr_user_id', $usrUserId)->where('mst_item_id', $mstItemId)->first();
        $this->assertEquals(10, $usrItem->getAmount());
    }

    public function test_exec_パック購入_交換上限エラー() : void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $currentUser = new CurrentUser($usrUserId);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 100,
        ]);

        $platform = UserConstant::PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        $productSubId = 'pack_160_1_framework'; // opr_products.id

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '1980-07-01',
        ]);

        // ダイヤ所持数
        $this->createDiamond($usrUserId, freeDiamond: 10, paidDiamondIos: 10);

        $mstItemId = 'item1';
        MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::ETC->value,
            'start_date' =>  now()->format('Y-m-d H:i:s'),
            'end_date' =>  now()->addDays(1)->format('Y-m-d H:i:s'),
        ]);

        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $productSubId,
            'pack_type'=> PackType::DAILY->value,
            'sale_condition' => null,
            'cost_type' => MstPackCostType::PAID_DIAMOND->value,
            'tradable_count' => 5,
            'cost_amount' => 10,
        ])->toEntity();

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

        // OprProduct作成
        MstStoreProduct::factory()->create([
            'id' => $productSubId,
            'product_id_ios' => "ios_{$productSubId}",
            'product_id_android' => "android_{$productSubId}",
        ]);
        OprProduct::factory()->create([
            'id' => $productSubId,
            'mst_store_product_id' => $productSubId, // 簡略化のため同じIDを使用
            'product_type' => ProductType::PACK->value,
            'paid_amount' => 0,
        ]);

        UsrTradePack::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_pack_id' => $mstPack->getId(),
            'daily_trade_count' => 5,
            'last_reset_at' => now()->format('Y-m-d H:i:s'),
        ]);

        // 交換上限に達しているので例外が発生すること
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::SHOP_TRADE_COUNT_LIMIT);

        // Exercise
       $this->useCase->exec(
            $currentUser,
            $platform,
            $billingPlatform,
            $mstPack->getProductSubId(),
        );

        // Verify
        // ダイヤもコインも変化ないことを確認
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals(10, $diamond->getFreeAmount());
        $this->assertEquals(10, $diamond->getPaidAmountApple());

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals(100, $usrUserParameter->getCoin());
    }

    public static function params_test_exec_パック購入_正常_日跨ぎ()
    {
        // last_reset_atを１日前に設定
        $lastResetAt = '2025-07-12 01:51:15';

        return [
            'Cost_有償ダイヤ' => [
                'diamondAmount' => 100,
                'paidDiamondAmount' => 100,
                'costType' => MstPackCostType::PAID_DIAMOND->value,
                'costAmount' => 10,
                'afterPaidDiamond' => 90,
                'afterDiamond' => 110,
                'afterCoin' => 150,
                'last_reset_at' => $lastResetAt
            ],
            '無償ダイヤ' => [
                'diamondAmount' => 5,
                'paidDiamondAmount' => 100,
                'costType' => MstPackCostType::DIAMOND->value,
                'costAmount' => 10,
                'afterPaidDiamond' => 95,
                'afterDiamond' => 10,
                'afterCoin' => 150,
                'last_reset_at' => $lastResetAt
            ],
            'AD' => [
                'diamondAmount' => 100,
                'paidDiamondAmount' => 100,
                'costType' => MstPackCostType::AD->value,
                'costAmount' => 0,
                'afterPaidDiamond' => 100,
                'afterDiamond' => 110,
                'afterCoin' => 150,
                'last_reset_at' => $lastResetAt
            ],
            'Free' => [
                'diamondAmount' => 100,
                'paidDiamondAmount' => 100,
                'costType' => MstPackCostType::FREE->value,
                'costAmount' => 0,
                'afterPaidDiamond' => 100,
                'afterDiamond' => 110,
                'afterCoin' => 150,
                'last_reset_at' => $lastResetAt
            ],
        ];
    }
    #[DataProvider('params_test_exec_パック購入_正常_日跨ぎ')]
    public function test_exec_パック購入_正常_日跨ぎ(
        int $diamondAmount,
        int $paidDiamondAmount,
        string $costType,
        int $costAmount,
        int $afterPaidDiamond,
        int $afterDiamond,
        int $afterCoin,
        string $lastResetAt,
    ) : void
    {
        // Setup
        $now = $this->fixTime('2025-07-13 01:51:15');
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $currentUser = new CurrentUser($usrUserId);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 100,
        ]);

        $platform = UserConstant::PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        $productSubId = 'pack_160_1_framework'; // opr_products.id

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '1980-07-01',
        ]);

        // ダイヤ所持数
        $this->createDiamond($usrUserId, freeDiamond: $diamondAmount, paidDiamondIos: $paidDiamondAmount);

        $mstItemId = 'item1';
        MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::ETC->value,
            'start_date' =>  $now->format('Y-m-d H:i:s'),
            'end_date' =>  $now->addDays(1)->format('Y-m-d H:i:s'),
        ]);

        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $productSubId,
            'pack_type'=> PackType::DAILY->value,
            'sale_condition' => null,
            'cost_type' => $costType,
            'tradable_count' => 5,
            'cost_amount' => $costAmount,
        ])->toEntity();

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

        // OprProduct作成
        MstStoreProduct::factory()->create([
            'id' => $productSubId,
            'product_id_ios' => "ios_{$productSubId}",
            'product_id_android' => "android_{$productSubId}",
        ]);
        OprProduct::factory()->create([
            'id' => $productSubId,
            'mst_store_product_id' => $productSubId, // 簡略化のため同じIDを使用
            'product_type' => ProductType::PACK->value,
            'paid_amount' => 0,
        ]);

        UsrTradePack::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_pack_id' => $mstPack->getId(),
            'daily_trade_count' => 3,
            'last_reset_at' => $lastResetAt,
        ]);

        // Exercise
        $this->useCase->exec(
            $currentUser,
            $platform,
            $billingPlatform,
            $mstPack->getProductSubId(),
        );

        // Verify
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals($afterDiamond, $diamond->getFreeAmount());
        $this->assertEquals($afterPaidDiamond, $diamond->getPaidAmountApple());

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals($afterCoin, $usrUserParameter->getCoin());

        $usrItem = UsrItem::query()->where('usr_user_id', $usrUserId)->where('mst_item_id', $mstItemId)->first();
        $this->assertEquals(10, $usrItem->getAmount());

        // トレードカウントとリセット日時の更新確認
        $usrTradePack = UsrTradePack::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_pack_id', $mstPack->getId())
            ->first();
        $this->assertEquals(1, $usrTradePack->getDailyTradeCount());
        $this->assertEquals($now->format('Y-m-d H:i:s'), $usrTradePack->getLastResetAt());
    }
}
