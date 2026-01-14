<?php

namespace Tests\Feature\Domain\Shop\Services;

use App\Domain\Common\Constants\ErrorCode;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Message\Models\Eloquent\UsrMessage;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstPack;
use App\Domain\Resource\Mst\Models\MstPackContent;
use App\Domain\Resource\Mst\Models\MstShopItem;
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
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\Enums\SaleCondition;
use App\Domain\Shop\Enums\ShopItemCostType;
use App\Domain\Shop\Enums\ShopType;
use App\Domain\Shop\Models\UsrConditionPack;
use App\Domain\Shop\Models\UsrShopItem;
use App\Domain\Shop\Models\UsrShopPass;
use App\Domain\Shop\Services\ShopService;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Models\UsrStoreAllowance;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Constants\ErrorCode as WpErrorCode;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyException;

class ShopServiceTest extends TestCase
{
    private ShopService $shopService;

    public function setUp(): void
    {
        parent::setUp();
        $this->shopService = $this->app->make(ShopService::class);
    }

    public function testTradeShopItem_交換回数エラーが発生する()
    {
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'coin' => fake()->numberBetween(100, 1000),
        ]);
        $mstShopItem = MstShopItem::factory()->create([
            'id' => fake()->uuid(),
            'shop_type' => ShopType::DAILY->value,
            'cost_type' => ShopItemCostType::COIN->value,
            'cost_amount' => 100,
            'is_first_time_free' => 0,
            'tradable_count' => fake()->numberBetween(1, 10),
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => null,
            'resource_amount' => 100,
            'start_date' => '2000-01-01 00:00:00',
            'end_date' => '2030-01-01 00:00:00'
        ])->toEntity();
        $usrShopItem = UsrShopItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_shop_item_id' => $mstShopItem->getId(),
            'trade_count' => $mstShopItem->getTradableCount(),
            'trade_total_count' => 1,
            'last_reset_at' => now()->format('Y-m-d H:i:s'),
        ]);

        // エラーが発生する
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::SHOP_TRADE_COUNT_LIMIT);

        // 実行
        $this->shopService->tradeShopItem($currentUser, $mstShopItem, $usrShopItem, CarbonImmutable::now());

        // コストが減っていないこと
        $actual = UsrUserParameter::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals($usrUserParameter->getCoin(), $actual->getCoin());

        // 交換物を獲得していないこと
        $diamond = $this->getDiamond($usrUser->getId());
        $this->assertEquals(0, $diamond->getFreeAmount());

        // 交換回数が増えていないこと
        $actual = UsrShopItem::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals($usrShopItem->getTradeCount(), $actual->getTradeCount());

    }

    public static function params_test_consumeCost_コスト不足エラーが発生する(): array
    {
        return [
            'コスト不足(コイン)' => [ShopItemCostType::COIN->value],
            'コスト不足(ダイヤ)' => [ShopItemCostType::DIAMOND->value]
        ];
    }

    #[DataProvider('params_test_consumeCost_コスト不足エラーが発生する')]
    public function test_consumeCost_コスト不足エラーが発生する(string $costType)
    {
        $usrUser = $this->createUsrUser();
        $now = $this->fixTime();
        $cost = fake()->numberBetween(100, 1000);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'coin' => $cost - 1,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10,
        ]);
        $this->createDiamond($usrUser->getId(), freeDiamond: $cost - 1);
        $mstShopItem = MstShopItem::factory()->create([
            'id' => fake()->uuid(),
            'shop_type' => ShopType::DAILY->value,
            'cost_type' => $costType,
            'cost_amount' => $cost,
            'is_first_time_free' => 0,
            'tradable_count' => 1,
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => null,
            'resource_amount' => 100,
            'start_date' => '2000-01-01 00:00:00',
            'end_date' => '2030-01-01 00:00:00'
        ])->toEntity();

        // エラーが発生する
        if ($costType === ShopItemCostType::COIN->value) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::LACK_OF_RESOURCES);
        } else {
            // ダイヤの場合はWpCurrencyException(NotEnoughCurrency)が発生する
            $this->expectException(WpCurrencyException::class);
            $this->expectExceptionCode(WpErrorCode::NOT_ENOUGH_CURRENCY);
        }

        // 実行
        $this->shopService->consumeCost($usrUser->getId(), 1, $mstShopItem, UserConstant::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, $now);
        $this->saveAll();

        // コストが減っていないこと、交換物を獲得していないこと
        $actual = UsrUserParameter::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals($usrUserParameter->getCoin(), $actual->getCoin());

        $diamond = $this->getDiamond($usrUser->getId());
        $this->assertEquals($cost - 1, $diamond->getFreeAmount());
    }

    // 放置BOXアイテムの仕様は消えているものの、将来的に復活する可能性がなくはないので、コメントアウトしとく
    // public function testTradeShopItem_放置収益連動コインが正常に交換できる()
    // {
    //     $usrUser = $this->createUsrUser();
    //     $currentUser = new CurrentUser($usrUser->getId());
    //     $usrUserParameter = UsrUserParameter::factory()->create([
    //         'usr_user_id' => $usrUser->getId(),
    //         'coin' => 0,
    //     ]);
    //     MstUserLevel::factory()->create([
    //         'level' => $usrUserParameter->getLevel(),
    //         'stamina' => 10,
    //     ]);
    //     $mstShopItem = MstShopItem::factory()->create([
    //         'id' => fake()->uuid(),
    //         'shop_type' => ShopType::COIN->value,
    //         'is_first_time_free' => 1,
    //         'resource_type' => 'IdleCoin',
    //         'resource_id' => null,
    //         'resource_amount' => 2,
    //         'start_date' => '2000-01-01 00:00:00',
    //         'end_date' => '2030-01-01 00:00:00'
    //     ])->toEntity();

    //     $mstStageIds = collect(['mstStage1', 'mstStage2']);
    //     $mstStageIds->map(function ($mstStageId, $index) {
    //         return MstStage::factory()->create([
    //             'id' => $mstStageId,
    //             'sort_order' => $index,
    //         ])->toEntity();
    //     });
    //     MstIdleIncentive::factory()->create([
    //         'initial_reward_receive_minutes' => 10,
    //         'reward_increase_interval_minutes' => 10,
    //     ])->toEntity();
    //     MstIdleIncentiveReward::factory()->create([
    //         'mst_stage_id' => 'mstStage2',
    //         'base_coin_amount' => 10,
    //     ]);
    //     $mstStageIds->map(function ($mstStageId) use ($usrUser) {
    //         return UsrStage::factory()->create([
    //             'usr_user_id' => $usrUser->getId(),
    //             'mst_stage_id' => $mstStageId,
    //             
    //         ]);
    //     });
    //     $usrShopItem = UsrShopItem::factory()->create([
    //         'usr_user_id' => $usrUser->getId(),
    //         'mst_shop_item_id' => $mstShopItem->getId(),
    //         'trade_count' => 0,
    //         'trade_total_count' => 0,
    //         'last_reset_at' => now()->format('Y-m-d H:i:s'),
    //     ]);

    //     // 実行
    //     $this->shopService->tradeShopItem($currentUser, $mstShopItem, $usrShopItem, CarbonImmutable::now());
    //     $this->saveAll();

    //     // コストの消費、交換回数の増加の検証はこのテスト関数以前で検証しているのでスキップ

    //     // 想定コインを獲得していること
    //     $actual = UsrUserParameter::query()->where('usr_user_id', $usrUser->getId())->first();
    //     $this->assertEquals(120, $actual->getCoin());
    // }

    public function testReleaseUserLevelPack_条件を満たすユーザーレベルパックが開放される()
    {
        $userLevel = 10;
        $now = CarbonImmutable::now();
        $startDate = $now->copy()->subDays(5)->format('Y-m-d H:i:s');
        $endDate = $now->copy()->addDays(5)->format('Y-m-d H:i:s');
        // 開放条件, 開放条件値, 開始日時, 終了日時
        $params = [
            [SaleCondition::USER_LEVEL->value, "5", $startDate, $endDate],
            [SaleCondition::USER_LEVEL->value, "10", $startDate, $endDate]
        ];
        $releaseMstPackIds = [];
        foreach ($params as [$saleCondition, $saleConditionValue, $startDate, $endDate]) {
            $oprProduct = OprProduct::factory()->create([
                'product_type' => ProductType::PACK->value,
                'start_date' => $startDate,
                'end_date' => $endDate
            ])->toEntity();

            $mstPack = MstPack::factory()->create([
                'product_sub_id' => $oprProduct->getId(),
                'pack_type'=> PackType::NORMAL->value,
                'sale_condition' => $saleCondition,
                'sale_condition_value' => $saleConditionValue
            ])->toEntity();

            $releaseMstPackIds[] = $mstPack->getId();
        }

        $usrUser = $this->createUsrUser();

        // テスト対象実行
        $this->shopService->releaseUserLevelPack($usrUser->getId(), $userLevel, $now);
        $this->saveAll();

        // 開放されていること
        $usrConditionPacks = UsrConditionPack::query()->where('usr_user_id', $usrUser->getId())->get();
        $this->assertCount(count($releaseMstPackIds), $usrConditionPacks);
        foreach ($usrConditionPacks as $usrConditionPack) {
            $this->assertContains($usrConditionPack->getMstPackId(), $releaseMstPackIds);
        }
    }

    public function testReleaseUserLevelPack_条件を満たさないユーザーレベルパックは開放されない()
    {
        $userLevel = 10;
        $now = CarbonImmutable::now();
        $startDate = $now->copy()->subDays(5)->format('Y-m-d H:i:s');
        $endDate = $now->copy()->addDays(5)->format('Y-m-d H:i:s');
        $outOfPeriodStartDate = $now->copy()->subDays(10)->format('Y-m-d H:i:s');
        $outOfPeriodEndDate = $now->copy()->subDays(5)->format('Y-m-d H:i:s');
        // 開放条件, 開放条件値, 開始日時, 終了日時
        $params = [
            // レベル条件未達
            [SaleCondition::USER_LEVEL->value, "15", $startDate, $endDate],
            // 開放条件には当てはまるが期間外
            [SaleCondition::USER_LEVEL->value, "10", $outOfPeriodStartDate, $outOfPeriodEndDate],
            // 開放条件には当てはまらず期間外
            [SaleCondition::USER_LEVEL->value, "15", $outOfPeriodStartDate, $outOfPeriodEndDate],
            // 無条件(対象外)
            [null, null, $startDate, $endDate],
            // 開放条件が違う
            [SaleCondition::STAGE_CLEAR->value, "stage_1", $startDate, $endDate],
        ];
        foreach ($params as [$saleCondition, $saleConditionValue, $startDate, $endDate]) {
            $oprProduct = OprProduct::factory()->create([
                'product_type' => ProductType::PACK->value,
                'start_date' => $startDate,
                'end_date' => $endDate
            ])->toEntity();

            MstPack::factory()->create([
                'product_sub_id' => $oprProduct->getId(),
                'pack_type'=> PackType::NORMAL->value,
                'sale_condition' => $saleCondition,
                'sale_condition_value' => $saleConditionValue
            ]);
        }

        $usrUser = $this->createUsrUser();

        // テスト対象実行
        $this->shopService->releaseUserLevelPack($usrUser->getId(), $userLevel, $now);
        $this->saveAll();

        // 開放されていないこと
        $usrConditionPacks = UsrConditionPack::query()->where('usr_user_id', $usrUser->getId())->get();
        $this->assertEmpty($usrConditionPacks);
    }

    public function testReleaseStageClearPack_条件を満たすステージクリアパックが開放される()
    {
        $now = CarbonImmutable::now();
        $startDate = $now->copy()->subDays(5)->format('Y-m-d H:i:s');
        $endDate = $now->copy()->addDays(5)->format('Y-m-d H:i:s');
        $stageId = fake()->uuid();
        $oprProduct = OprProduct::factory()->create([
            'product_type' => ProductType::PACK->value,
            'start_date' => $startDate,
            'end_date' => $endDate
        ])->toEntity();

        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $oprProduct->getId(),
            'pack_type'=> PackType::NORMAL->value,
            'sale_condition' => SaleCondition::STAGE_CLEAR->value,
            'sale_condition_value' => $stageId
        ])->toEntity();

        $usrUser = $this->createUsrUser();

        // テスト対象実行
        $this->shopService->releaseStageClearPack($usrUser->getId(), $stageId, $now);
        $this->saveAll();

        // 開放検証
        $usrConditionPack = UsrConditionPack::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertNotNull($usrConditionPack);
        $this->assertEquals($mstPack->getId(), $usrConditionPack->getMstPackId());
    }

    public function testReleaseStageClearPack_条件を満たさないステージクリアパックが開放されない()
    {
        $now = CarbonImmutable::now();
        $startDate = $now->copy()->subDays(5)->format('Y-m-d H:i:s');
        $endDate = $now->copy()->addDays(5)->format('Y-m-d H:i:s');
        $outOfPeriodStartDate = $now->copy()->subDays(10)->format('Y-m-d H:i:s');
        $outOfPeriodEndDate = $now->copy()->subDays(5)->format('Y-m-d H:i:s');
        $stageId = fake()->uuid();
        $stageId2 = fake()->uuid();
        // 開放条件, 開放条件値, 開始日時, 終了日時
        $params = [
            // ステージIDが違うので開放対象外
            [SaleCondition::STAGE_CLEAR->value, $stageId2, $startDate, $endDate],
            // 開放条件には当てはまるが期間外
            [SaleCondition::STAGE_CLEAR->value, $stageId, $outOfPeriodStartDate, $outOfPeriodEndDate],
            // 開放条件には当てはまらず期間外
            [SaleCondition::STAGE_CLEAR->value, $stageId2, $outOfPeriodStartDate, $outOfPeriodEndDate],
            // 無条件
            [null, null, $startDate, $endDate],
            // 開放条件が違う
            [SaleCondition::USER_LEVEL->value, "10", $startDate, $endDate],
        ];
        foreach ($params as [$saleCondition, $saleConditionValue, $startDate, $endDate]) {
            $oprProduct = OprProduct::factory()->create([
                'product_type' => ProductType::PACK->value,
                'start_date' => $startDate,
                'end_date' => $endDate
            ])->toEntity();

            MstPack::factory()->create([
                'product_sub_id' => $oprProduct->getId(),
                'pack_type'=> PackType::NORMAL->value,
                'sale_condition' => $saleCondition,
                'sale_condition_value' => $saleConditionValue
            ]);
        }

        $usrUser = $this->createUsrUser();

        // テスト対象実行
        $this->shopService->releaseStageClearPack($usrUser->getId(), $stageId, $now);
        $this->saveAll();

        // 開放されていないこと
        $usrConditionPacks = UsrConditionPack::query()->where('usr_user_id', $usrUser->getId())->get();
        $this->assertEmpty($usrConditionPacks);
    }

    public function testReleaseConditionPacks_条件を満たす条件パックがすべて開放される()
    {
        $stageId1 = fake()->uuid();
        $stageId2 = fake()->uuid();
        $now = CarbonImmutable::now();
        $startDate = $now->copy()->subDays(5)->format('Y-m-d H:i:s');
        $endDate = $now->copy()->addDays(5)->format('Y-m-d H:i:s');
        // 開放条件, 開放条件値, 開始日時, 終了日時
        $params = [
            // レベル条件
            [SaleCondition::USER_LEVEL->value, "5", $startDate, $endDate],
            [SaleCondition::USER_LEVEL->value, "10", $startDate, $endDate],
            // ステージクリア条件
            [SaleCondition::STAGE_CLEAR->value, $stageId1, $startDate, $endDate],
            [SaleCondition::STAGE_CLEAR->value, $stageId2, $startDate, $endDate]
        ];
        $releaseMstPackIds = [];
        foreach ($params as [$saleCondition, $saleConditionValue, $startDate, $endDate]) {
            $oprProduct = OprProduct::factory()->create([
                'product_type' => ProductType::PACK->value,
                'start_date' => $startDate,
                'end_date' => $endDate
            ])->toEntity();

            $mstPack = MstPack::factory()->create([
                'product_sub_id' => $oprProduct->getId(),
                'pack_type'=> PackType::NORMAL->value,
                'sale_condition' => $saleCondition,
                'sale_condition_value' => $saleConditionValue
            ])->toEntity();

            $releaseMstPackIds[] = $mstPack->getId();
        }

        $usrUser = $this->createUsrUser();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 10,
        ]);
        UsrStage::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $stageId1,
        ]);
        UsrStage::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $stageId2,
        ]);

        // テスト対象実行
        $this->shopService->releaseConditionPacks($usrUser->getId(), $usrUserParameter->getLevel(), $now);
        $this->saveAll();

        // 開放されていること
        $usrConditionPacks = UsrConditionPack::query()->where('usr_user_id', $usrUser->getId())->get();
        $this->assertCount(count($releaseMstPackIds), $usrConditionPacks);
        foreach ($usrConditionPacks as $usrConditionPack) {
            $this->assertContains($usrConditionPack->getMstPackId(), $releaseMstPackIds);
        }
    }

    public function testReleaseConditionPacks_条件を満たさない条件パックが開放されない()
    {
        $stageId1 = fake()->uuid();
        $stageId2 = fake()->uuid();
        $now = CarbonImmutable::now();
        $startDate = $now->copy()->subDays(5)->format('Y-m-d H:i:s');
        $endDate = $now->copy()->addDays(5)->format('Y-m-d H:i:s');
        $outOfPeriodStartDate = $now->copy()->subDays(10)->format('Y-m-d H:i:s');
        $outOfPeriodEndDate = $now->copy()->subDays(5)->format('Y-m-d H:i:s');
        // 開放条件, 開放条件値, 開始日時, 終了日時
        $params = [
            // レベル条件を達成していない
            [SaleCondition::USER_LEVEL->value, "10", $startDate, $endDate],
            // レベル条件を達成しているが期間外
            [SaleCondition::USER_LEVEL->value, "5", $outOfPeriodStartDate, $outOfPeriodEndDate],
            // ステージクリア条件を達成していない
            [SaleCondition::STAGE_CLEAR->value, $stageId1, $startDate, $endDate],
            // ステージクリア条件を達成しているが期間外
            [SaleCondition::STAGE_CLEAR->value, $stageId2, $outOfPeriodStartDate, $outOfPeriodEndDate],
            // 無条件(対象外)
            [null, null, $startDate, $endDate],
        ];
        foreach ($params as [$saleCondition, $saleConditionValue, $startDate, $endDate]) {
            $oprProduct = OprProduct::factory()->create([
                'product_type' => ProductType::PACK->value,
                'start_date' => $startDate,
                'end_date' => $endDate
            ])->toEntity();

            MstPack::factory()->create([
                'product_sub_id' => $oprProduct->getId(),
                'pack_type'=> PackType::NORMAL->value,
                'sale_condition' => $saleCondition,
                'sale_condition_value' => $saleConditionValue
            ]);
        }

        $usrUser = $this->createUsrUser();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 5,
        ]);
        UsrStage::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $stageId2,
        ]);

        // テスト対象実行
        $this->shopService->releaseConditionPacks($usrUser->getId(), $usrUserParameter->getLevel(), $now);
        $this->saveAll();

        // 開放されていないこと
        $usrConditionPacks = UsrConditionPack::query()->where('usr_user_id', $usrUser->getId())->get();
        $this->assertEmpty($usrConditionPacks);
    }

    public static function params_convertToRealResourceType_実際に配布されるリソースタイプが取得できること()
    {
        return [
            '無償一次通過' => ['FreeDiamond', 'FreeDiamond'],
            'コイン' => ['Coin', 'Coin'],
            '放置収益連動コイン' => ['IdleCoin', 'Coin'],
            'アイテム' => ['Item', 'Item'],
        ];
    }

    /**
     * @dataProvider params_convertToRealResourceType_実際に配布されるリソースタイプが取得できること
     */
    public function testConvertToRealResourceType_実際に配布されるリソースタイプが取得できること(string $source, string $expected)
    {
        $reflection = new \ReflectionClass($this->shopService);
        $method = $reflection->getMethod('convertToRealResourceType');
        $method->setAccessible(true);
        $actual = $method->invokeArgs($this->shopService, [$source]);
        $this->assertEquals($expected, $actual);
    }

    public static function params_consumeCost_コストが消費されること()
    {
        // コストタイプ, コスト, 想定コイン, 想定有償一次通貨, 想定無償一次通貨, 初回無料フラグ
        return [
            'コイン' => [ShopItemCostType::COIN->value, 100, 0, 100, 100, false],
            '有償一次通貨' => [ShopItemCostType::PAID_DIAMOND->value, 100, 100, 0, 100, false],
            '一次通貨' => [ShopItemCostType::DIAMOND->value, 100, 100, 100, 0, false],
            '広告' => [ShopItemCostType::AD->value, 100, 100, 100, 100, false],
            '無料' => [ShopItemCostType::FREE->value, 100, 100, 100, 100, false],
            '初回無料' => [ShopItemCostType::COIN->value, 100, 100, 100, 100, true],
        ];
    }

    /**
     * @dataProvider params_consumeCost_コストが消費されること
     */
    public function testConsumeCost_コストが消費されること(
        string $costType,
        int $costAmount,
        int $expectedCoin,
        int $expectedPaidDiamond,
        int $expectedFreeDiamond,
        bool $isFirstTimeFree
    ) {
        $usrUser = $this->createUsrUser();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'coin' => $costAmount,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10,
        ]);
        $this->createDiamond($usrUser->getId(), paidDiamondIos: $costAmount, freeDiamond: $costAmount);
        $mstShopItem = MstShopItem::factory()->create([
            'cost_type' => $costType,
            'cost_amount' => $costAmount,
            'is_first_time_free' => (int)$isFirstTimeFree,
            'start_date' => '2000-01-01 00:00:00',
            'end_date' => '2030-01-01 00:00:00'
        ])->toEntity();
        $platform = UserConstant::PLATFORM_IOS;

        $this->shopService->consumeCost(
            $usrUser->getId(),
            1,
            $mstShopItem,
            $platform,
            'AppStore',
            CarbonImmutable::now()
        );
        $this->saveAll();

        /** @var \App\Domain\User\Models\UsrUserParameter $usrUserParameter */
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUser->getId())->first();

        // 各リソースが想定通りの値になっていること
        $this->assertEquals($expectedCoin, $usrUserParameter->getCoin());

        $diamond = $this->getDiamond($usrUser->getId());
        $this->assertEquals($expectedPaidDiamond, $diamond->getPaidAmountApple());
        $this->assertEquals($expectedFreeDiamond, $diamond->getFreeAmount());

    }

    public static function params_validateTradeCount_交換回数検証()
    {
        return [
            '交換回数無制限' => ['tradableCount' => null, 'tradeCount' => 999, 'isExceptionThrown' => false],
            '交換回数内' => ['tradableCount' => 2, 'tradeCount' => 1, 'isExceptionThrown' => false],
            '交換回数内オーバー' => ['tradableCount' => 1, 'tradeCount' => 1, 'isExceptionThrown' => true],
        ];
    }

    /**
     * @dataProvider params_validateTradeCount_交換回数検証
     */
    public function testValidateTradeCount_交換回数検証(?int $tradableCount, int $tradeCount, bool $isExceptionThrown)
    {
        if ($isExceptionThrown) {
            // エラーが発生する
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::SHOP_TRADE_COUNT_LIMIT);
        }

        $reflection = new \ReflectionClass($this->shopService);
        $method = $reflection->getMethod('validateTradeCount');
        $method->setAccessible(true);
        $method->invokeArgs($this->shopService, [$tradableCount, $tradeCount]);

        // エラーが起きないテストはassertがないのでダミーでassertを入れる
        $this->assertTrue(true);
    }

    public static function params_validateConditionPack_条件パック検証()
    {
        $saleCondition = SaleCondition::USER_LEVEL->value;
        $now = CarbonImmutable::now();
        return [
            '条件パックではない' => [
                'saleCondition' => null,
                'conditionPackStartDate' => $now,
                'now' => $now,
                'errorCode' => null
            ],
            '条件パック未開放' => [
                'saleCondition' => $saleCondition,
                'conditionPackStartDate' => null,
                'now' => $now,
                'errorCode' => ErrorCode::SHOP_CONDITION_PACK_NOT_RELEASED
            ],
            '購入期限無制限' => [
                'saleCondition' => $saleCondition,
                'conditionPackStartDate' => $now,
                'now' => $now,
                'errorCode' => null
            ],
            '購入期限切れ' => [
                'saleCondition' => $saleCondition,
                'conditionPackStartDate' => $now->copy()->subDays(),
                'now' => $now,
                'errorCode' => ErrorCode::SHOP_CONDITION_PACK_EXPIRED
            ],
            '正常' => [
                'saleCondition' => $saleCondition,
                'conditionPackStartDate' => $now,
                'now' => $now,
                'errorCode' => null
            ],
        ];
    }

    /**
     * @dataProvider params_validateConditionPack_条件パック検証
     */
    public function testValidateConditionPack_条件パック検証(
        ?string $saleCondition,
        ?CarbonImmutable $conditionPackStartDate,
        CarbonImmutable $now,
        ?int $errorCode
    ) {
        $usrUser = UsrUser::factory()->create();
        $mstPack = MstPack::factory()->create([
            'pack_type'=> PackType::NORMAL->value,
            'sale_condition' => $saleCondition,
            'sale_hours' => 1,
        ])->toEntity();
        if (!is_null($conditionPackStartDate)) {
            UsrConditionPack::factory()->create([
                'usr_user_id' => $usrUser->getId(),
                'mst_pack_id' => $mstPack->getId(),
                'start_date' => $conditionPackStartDate->toDateTimeString()
            ]);
        }

        if (!is_null($errorCode)) {
            // エラーが発生する
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }

        $reflection = new \ReflectionClass($this->shopService);
        $method = $reflection->getMethod('validateConditionPack');
        $method->setAccessible(true);
        $method->invokeArgs($this->shopService, [$mstPack, $usrUser->getId(), $now]);

        // エラーが起きないテストはassertがないのでダミーでassertを入れる
        $this->assertTrue(true);
    }

    public static function params_test_resetUsrShopItem_商品リセット確認()
    {
        $now = CarbonImmutable::now();
        $yesterday = $now->copy()->subDay();
        $lastWeek = $now->copy()->subWeek();
        return [
            'コイン商品のコストダイヤモンドは日付をまたいでいてもリセットされない' => [
                'mstShopItemId' => 'coin_diamond_not_reset',
                'shopType' => ShopType::COIN->value,
                'costType' => ShopItemCostType::DIAMOND->value,
                'lastResetAt' => $yesterday->toDateTimeString(),
                'expectedTradeCount' => 1,
            ],
            'コイン商品のコスト広告で日付をまたいでいなければリセットされない' => [
                'mstShopItemId' => 'coin_ad_not_reset',
                'shopType' => ShopType::COIN->value,
                'costType' => ShopItemCostType::AD->value,
                'lastResetAt' => $now->toDateTimeString(),
                'expectedTradeCount' => 1,
            ],
            'コイン商品のコスト広告で日付をまたいでいればリセットされる' => [
                'mstShopItemId' => 'coin_ad_reset',
                'shopType' => ShopType::COIN->value,
                'costType' => ShopItemCostType::AD->value,
                'lastResetAt' => $yesterday->toDateTimeString(),
                'expectedTradeCount' => 0,
            ],
            'デイリー商品で日付をまたいでいなければリセットされない' => [
                'mstShopItemId' => 'daily_not_reset',
                'shopType' => ShopType::DAILY->value,
                'costType' => ShopItemCostType::DIAMOND->value,
                'lastResetAt' => $now->toDateTimeString(),
                'expectedTradeCount' => 1,
            ],
            'デイリー商品で日付をまたいでいればリセットされる' => [
                'mstShopItemId' => 'daily_reset',
                'shopType' => ShopType::DAILY->value,
                'costType' => ShopItemCostType::DIAMOND->value,
                'lastResetAt' => $yesterday->toDateTimeString(),
                'expectedTradeCount' => 0,
            ],
            'ウィークリー商品で週をまたいでいなければリセットされない' => [
                'mstShopItemId' => 'weekly_not_reset',
                'shopType' => ShopType::WEEKLY->value,
                'costType' => ShopItemCostType::DIAMOND->value,
                'lastResetAt' => $now->toDateTimeString(),
                'expectedTradeCount' => 1,
            ],
            'ウィークリー商品で週をまたいでいればリセットされる' => [
                'mstShopItemId' => 'weekly_reset',
                'shopType' => ShopType::WEEKLY->value,
                'costType' => ShopItemCostType::DIAMOND->value,
                'lastResetAt' => $lastWeek->toDateTimeString(),
                'expectedTradeCount' => 0,
            ],
        ];
    }

    /**
     * @dataProvider params_test_resetUsrShopItem_商品リセット確認
     */
    public function test_resetUsrShopItem_商品リセット確認(
        string $mstShopItemId,
        string $shopType,
        string $costType,
        string $lastResetAt,
        int $expectedTradeCount
    ) {
        $usrUser = $this->createUsrUser();
        $now = $this->fixTime();
        $mstShopItem = MstShopItem::factory()->create([
            'id' => $mstShopItemId,
            'shop_type' => $shopType,
            'cost_type' => $costType,
            'start_date' => '2000-01-01 00:00:00',
            'end_date' => '2030-01-01 00:00:00'
        ])->toEntity();
        $usrShopItem = UsrShopItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_shop_item_id' => $mstShopItem->getId(),
            'trade_count' => 1,
            'trade_total_count' => 1,
            'last_reset_at' => $lastResetAt,
        ]);

        $this->shopService->resetUsrShopItem($usrShopItem, $mstShopItem, $now);

        // リセット状況の検証
        $this->assertEquals($expectedTradeCount, $usrShopItem->getTradeCount());
        // totalはリセット対象外
        $this->assertEquals(1, $usrShopItem->getTradeTotalCount());
    }

    public function test_tradeShopPass_スタミナ自然回復上限増加のパスを3日前から再購入できる()
    {
        // Setup
        $now = $this->fixTime('2024-07-01 10:00:00');
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

        $productId = 'ios_pack_160_1_framework'; // mst_store_products.product_id_ios
        $storeProductId = 'pack_160_1_framework'; // mst_store_products.id
        $productSubId = 'pack_160_1_framework'; // opr_products.id

        UsrUserProfile::factory()->create([
           'usr_user_id' => $usrUserId,
           'birth_date' => '1980-07-01',
        ]);

        // 課金基盤関連
        // ダイヤ所持数を0で用意
        $this->createDiamond($usrUserId);
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
        $oprProduct = OprProduct::factory()->create([
           'id' => $productSubId,
           'mst_store_product_id' => $storeProductId,
           'paid_amount' => 100,
        ])->toEntity();
        MstStoreProduct::factory()->create([
           'id' => $storeProductId,
           'product_id_ios' => "ios_{$storeProductId}",
           'product_id_android' => "android_{$storeProductId}",
        ]);

        $mstPass = MstShopPass::factory()->create([
           'opr_product_id' => $productSubId,
           'pass_duration_days' => 7,
        ])->toEntity();
        MstShopPassI18n::factory()->create([
           'mst_shop_pass_id' => $mstPass->getId(),
           'name' => 'テストパス',
        ]);

        $usrShopPass = UsrShopPass::factory()->create(
            [
               'id' => 'test',
               'usr_user_id' => $usrUserId,
               'mst_shop_pass_id' => $mstPass->getId(),
               'daily_reward_received_count' => 1,
               'daily_latest_received_at' => '2025-03-19 00:00:00',
               'start_at' => $now->toDateTimeString(),
               'end_at' => $now->addDay(2)->toDateTimeString(),
            ],
        );

        MstShopPassEffect::factory()->createMany([
            // スタミナ回復上限アップ
            [
               'mst_shop_pass_id' => $mstPass->getId(),
               'effect_type' => PassEffectType::STAMINA_ADD_RECOVERY_LIMIT->value,
               'effect_value' => 10,
            ]
        ]);

        $this->shopService->tradeShopPass(
            $usrUserId,
            $oprProduct,
            $mstPass,
            $now
        );
        $this->saveAll();

        // Verify
        $usrShopPass->refresh();
        $this->assertEquals(0, $usrShopPass->getDailyRewardReceivedCount());
        $this->assertEquals('2024-07-09 18:59:59', $usrShopPass->end_at);
    }

    public function test_tradeShopPass_スタミナ自然回復上限増加のパスが３日以上期限が残っている場合再購入できない()
    {
        // Setup
        $now = $this->fixTime('2024-07-01 10:00:00');
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

        $productId = 'ios_pack_160_1_framework'; // mst_store_products.product_id_ios
        $storeProductId = 'pack_160_1_framework'; // mst_store_products.id
        $productSubId = 'pack_160_1_framework'; // opr_products.id

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '1980-07-01',
        ]);

        // 課金基盤関連
        // ダイヤ所持数を0で用意
        $this->createDiamond($usrUserId);
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
        $oprProduct = OprProduct::factory()->create([
            'id' => $productSubId,
            'mst_store_product_id' => $storeProductId,
            'paid_amount' => 100,
        ])->toEntity();
        MstStoreProduct::factory()->create([
            'id' => $storeProductId,
            'product_id_ios' => "ios_{$storeProductId}",
            'product_id_android' => "android_{$storeProductId}",
        ]);

        $mstPass = MstShopPass::factory()->create([
            'opr_product_id' => $productSubId,
            'pass_duration_days' => 7,
        ])->toEntity();
        MstShopPassI18n::factory()->create([
            'mst_shop_pass_id' => $mstPass->getId(),
            'name' => 'テストパス',
        ]);

        UsrShopPass::factory()->createMany([
            [
                'id' => 'test',
                'usr_user_id' => $usrUserId,
                'mst_shop_pass_id' => $mstPass->getId(),
                'daily_reward_received_count' => 1,
                'daily_latest_received_at' => '2025-03-19 00:00:00',
                'start_at' => $now->toDateTimeString(),
                'end_at' => $now->addDay(4)->toDateTimeString(),
            ],
        ]);

        MstShopPassEffect::factory()->createMany([
            // スタミナ回復上限アップ
            [
                'mst_shop_pass_id' => $mstPass->getId(),
                'effect_type' => PassEffectType::STAMINA_ADD_RECOVERY_LIMIT->value,
                'effect_value' => 10,
            ]
        ]);

        // App\Domain\Common\Exceptions\GameException: The pass in use. の例外が投げられることを確認する
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::SHOP_PASS_NOT_EXPIRED);
        $this->shopService->tradeShopPass(
            $usrUserId,
            $oprProduct,
            $mstPass,
            $now
        );
    }

    public function test_tradeShopPass_購入時即一日目の報酬を受け取る()
    {
        // Setup
        $now = $this->fixTime('2024-07-01 10:00:00');
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $this->createDiamond($usrUserId);
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

        $productId = 'ios_pack_160_1_framework'; // mst_store_products.product_id_ios
        $storeProductId = 'pack_160_1_framework'; // mst_store_products.id
        $productSubId = 'pack_160_1_framework'; // opr_products.id

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '1980-07-01',
        ]);

        // 課金基盤関連
        // ダイヤ所持数を0で用意
        $this->createDiamond($usrUserId);
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
        $oprProduct = OprProduct::factory()->create([
            'id' => $productSubId,
            'mst_store_product_id' => $storeProductId,
            'paid_amount' => 100,
        ])->toEntity();
        MstStoreProduct::factory()->create([
            'id' => $storeProductId,
            'product_id_ios' => "ios_{$storeProductId}",
            'product_id_android' => "android_{$storeProductId}",
        ]);

        $mstPass = MstShopPass::factory()->create([
            'opr_product_id' => $productSubId,
            'pass_duration_days' => 7,
        ])->toEntity();
        MstShopPassI18n::factory()->create([
            'mst_shop_pass_id' => $mstPass->getId(),
            'name' => 'テストパス',
        ]);
        MstShopPassReward::factory()->create([
            'mst_shop_pass_id' => $mstPass->getId(),
            'pass_reward_type' => 1, // 毎日報酬
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => null,
            'resource_amount' => 100,
        ]);
        MstShopPassEffect::factory()->createMany([
            // スタミナ回復上限アップ
            [
                'mst_shop_pass_id' => $mstPass->getId(),
                'effect_type' => PassEffectType::STAMINA_ADD_RECOVERY_LIMIT->value,
                'effect_value' => 10,
            ]
        ]);

        $this->shopService->tradeShopPass(
            $usrUserId,
            $oprProduct,
            $mstPass,
            $now
        );
        $this->saveAll();

        // Verify
        $usrShopPass = UsrShopPass::query()->where('usr_user_id', $usrUserId)
            ->where('mst_shop_pass_id', $mstPass->getId())
            ->get()->first();
        $this->assertEquals('2024-07-07 18:59:59', $usrShopPass->end_at);

        $usrMessage = UsrMessage::query()->where('usr_user_id', $usrUserId)->get()->first();
        $this->assertEquals(RewardType::FREE_DIAMOND->value, $usrMessage->getResourceType());
        $this->assertEquals(100, $usrMessage->getResourceAmount());
    }

    public function test_tradeShopPass_購入時パスデータをリセットした時一日目の報酬を受け取る()
    {
        // Setup
        $now = $this->fixTime('2024-07-01 10:00:00');
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $this->createDiamond($usrUserId);
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

        $productId = 'ios_pack_160_1_framework'; // mst_store_products.product_id_ios
        $storeProductId = 'pack_160_1_framework'; // mst_store_products.id
        $productSubId = 'pack_160_1_framework'; // opr_products.id

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '1980-07-01',
        ]);

        // 課金基盤関連
        // ダイヤ所持数を0で用意
        $this->createDiamond($usrUserId);
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
        $oprProduct = OprProduct::factory()->create([
            'id' => $productSubId,
            'mst_store_product_id' => $storeProductId,
            'paid_amount' => 100,
        ])->toEntity();
        MstStoreProduct::factory()->create([
            'id' => $storeProductId,
            'product_id_ios' => "ios_{$storeProductId}",
            'product_id_android' => "android_{$storeProductId}",
        ]);

        $mstPass = MstShopPass::factory()->create([
            'opr_product_id' => $productSubId,
            'pass_duration_days' => 7,
        ])->toEntity();
        MstShopPassI18n::factory()->create([
            'mst_shop_pass_id' => $mstPass->getId(),
            'name' => 'テストパス',
        ]);
        MstShopPassReward::factory()->create([
            'mst_shop_pass_id' => $mstPass->getId(),
            'pass_reward_type' => 1, // 毎日報酬
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => null,
            'resource_amount' => 100,
        ]);
        MstShopPassEffect::factory()->createMany([
            // スタミナ回復上限アップ
            [
                'mst_shop_pass_id' => $mstPass->getId(),
                'effect_type' => PassEffectType::STAMINA_ADD_RECOVERY_LIMIT->value,
                'effect_value' => 10,
            ]
        ]);
        $usrShopPass = UsrShopPass::factory()->create([
            'id' => 'test',
            'usr_user_id' => $usrUserId,
            'mst_shop_pass_id' => $mstPass->getId(),
            'daily_reward_received_count' => 6,
            'daily_latest_received_at' => '2025-03-19 00:00:00',
            'start_at' => $now->subDays(7)->toDateTimeString(),
            'end_at' => $now->subDay()->toDateTimeString(),
        ]);

        $this->shopService->tradeShopPass(
            $usrUserId,
            $oprProduct,
            $mstPass,
            $now
        );
        $this->saveAll();

        // Verify
        $usrShopPass->refresh();
        $this->assertEquals(1, $usrShopPass->getDailyRewardReceivedCount());
        $this->assertEquals('2024-07-07 18:59:59', $usrShopPass->end_at);

        $usrMessage = UsrMessage::query()->where('usr_user_id', $usrUserId)->get()->first();
        $this->assertEquals(RewardType::FREE_DIAMOND->value, $usrMessage->getResourceType());
        $this->assertEquals(100, $usrMessage->getResourceAmount());
    }

    public static function params_tradePack_Typeパラメータ()
    {
        return [
            'Free' => [
                'coin' => 10,
                'paidDiamond' => 0,
                'diamond' => 150,
                'costType' => MstPackCostType::FREE->value,
                'costAmount' => 0,
                'afterCoin' => 20,
                'afterPaidDiamond' => 0,
                'afterDiamond' => 250,
            ],
            'AD' => [
                'coin' => 10,
                'paidDiamond' => 0,
                'diamond' => 150,
                'costType' => MstPackCostType::AD->value,
                'costAmount' => 0,
                'afterCoin' => 20,
                'afterPaidDiamond' => 0,
                'afterDiamond' => 250,
            ],
            'Diamond' => [
                'coin' => 10,
                'paidDiamond' => 100,
                'diamond' => 30,
                'costType' => MstPackCostType::DIAMOND->value,
                'costAmount' => 50,
                'afterCoin' => 20,
                'afterPaidDiamond' => 80,
                'afterDiamond' => 100,
            ],
            'PaidDiamond' => [
                'coin' => 10,
                'paidDiamond' => 100,
                'diamond' => 30,
                'costType' => MstPackCostType::PAID_DIAMOND->value,
                'costAmount' => 50,
                'afterCoin' => 20,
                'afterPaidDiamond' => 50,
                'afterDiamond' => 130,
            ],
        ];
    }

    #[DataProvider('params_tradePack_Typeパラメータ')]
    public function test_tradePack_実行(
        int $coin,
        int $paidDiamond,
        int $diamond,
        string $costType,
        int $costAmount,
        int $afterCoin,
        int $afterPaidDiamond,
        int $afterDiamond
    )
    {
        // Setup
        $now = $this->fixTime('2024-07-01 10:00:00');

        $startDate = $now->subDays(5)->format('Y-m-d H:i:s');
        $endDate = $now->addDays(5)->format('Y-m-d H:i:s');

        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();

        $this->createDiamond($usrUserId, freeDiamond: $diamond, paidDiamondIos: $paidDiamond);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => $coin,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 100,
        ]);

        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $platform = UserConstant::PLATFORM_IOS;

        $oprProduct = OprProduct::factory()->create([
            'product_type' => ProductType::PACK->value,
            'start_date' => $startDate,
            'end_date' => $endDate
        ])->toEntity();

        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $oprProduct->getId(),
            'pack_type'=> PackType::NORMAL->value,
            'sale_condition' => SaleCondition::USER_LEVEL->value,
            'cost_type' => $costType,
            'cost_amount' => $costAmount,
            'sale_condition_value' => 1
        ])->toEntity();

        MstPackContent::factory()->createMany([
            [
                'mst_pack_id' => $mstPack->getId(),
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 10,
            ],
            [
                'mst_pack_id' => $mstPack->getId(),
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
        ]);

        $this->shopService->tradePack(
            $usrUserId,
            $mstPack,
            $platform,
            $billingPlatform,
            $now
        );
        // リワード反映のためにsaveAllを呼び出す
        $this->saveAll();
        // 更新後のUsrUserParameterを取得
        $afterUsrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUser->getId())->first();
        // 更新後のダイヤを取得
        $diamond = $this->getDiamond($usrUser->getId());

        // Verify
        // 無償ダイヤの増減、有償ダイヤの増減、コインの増加が正しいことを確認
        $this->assertEquals($afterDiamond, $diamond->getFreeAmount());
        $this->assertEquals($afterPaidDiamond, $diamond->getPaidAmountApple());
        $this->assertEquals($afterCoin, $afterUsrUserParameter->getCoin());
    }

    public function test_tradePack_PaidDiamond交換NG()
    {
        // Setup
        $now = $this->fixTime('2024-07-01 10:00:00');
        $startDate = $now->subDays(5)->format('Y-m-d H:i:s');
        $endDate = $now->addDays(5)->format('Y-m-d H:i:s');
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
         // ダイヤ所持数を30-100で用意
        $this->createDiamond($usrUserId, freeDiamond: 30, paidDiamondIos: 10);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 10,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 100,
        ]);

        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $platform = UserConstant::PLATFORM_IOS;

        $oprProduct = OprProduct::factory()->create([
            'product_type' => ProductType::PACK->value,
            'start_date' => $startDate,
            'end_date' => $endDate
        ])->toEntity();

        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $oprProduct->getId(),
            'pack_type'=> PackType::NORMAL->value,
            'sale_condition' => SaleCondition::USER_LEVEL->value,
            'cost_type' => MstPackCostType::PAID_DIAMOND->value,
            'cost_amount' => 50,
            'sale_condition_value' => 1
        ])->toEntity();

        MstPackContent::factory()->createMany([
            [
                'mst_pack_id' => $mstPack->getId(),
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 10,
            ],
            [
                'mst_pack_id' => $mstPack->getId(),
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
        ]);

        // Verify
        $this->expectException(WpCurrencyException::class);
        $this->expectExceptionCode(WpErrorCode::NOT_ENOUGH_PAID_CURRENCY);

        $this->shopService->tradePack(
            $usrUserId,
            $mstPack,
            $platform,
            $billingPlatform,
            $now
        );
    }

    public function test_tradePack_Cash交換NG()
    {
        // Setup
        $now = $this->fixTime('2024-07-01 10:00:00');
        $startDate = $now->subDays(5)->format('Y-m-d H:i:s');
        $endDate = $now->addDays(5)->format('Y-m-d H:i:s');
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
         // ダイヤ所持数を30-100で用意
        $this->createDiamond($usrUserId, freeDiamond: 0, paidDiamondIos: 0);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 10,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 100,
        ]);

        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $platform = UserConstant::PLATFORM_IOS;

        $oprProduct = OprProduct::factory()->create([
            'product_type' => ProductType::PACK->value,
            'start_date' => $startDate,
            'end_date' => $endDate
        ])->toEntity();

        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $oprProduct->getId(),
            'pack_type'=> PackType::NORMAL->value,
            'sale_condition' => SaleCondition::USER_LEVEL->value,
            'cost_type' => MstPackCostType::CASH->value,
            'cost_amount' => 100,
            'sale_condition_value' => 1
        ])->toEntity();

        MstPackContent::factory()->createMany([
            [
                'mst_pack_id' => $mstPack->getId(),
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 10,
            ],
            [
                'mst_pack_id' => $mstPack->getId(),
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
        ]);

        // Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_PARAMETER);

        $this->shopService->tradePack(
            $usrUserId,
            $mstPack,
            $platform,
            $billingPlatform,
            $now
        );
    }

    public static function params_updateDailyPassReward_期限切れテスト(): array
    {
        return [
            '期限内（JST 2024-07-04 03:59:00）で受け取れる' => [
                'nowUtc' => '2024-07-03 18:59:00', // JST 2024-07-04 03:59:00
                'expectedRewardCount' => 3,
                'shouldReceive' => true,
            ],
            '期限外（JST 2024-07-04 04:00:00）で受け取れない' => [
                'nowUtc' => '2024-07-03 19:00:00', // JST 2024-07-04 04:00:00
                'expectedRewardCount' => 2,
                'shouldReceive' => false,
            ],
        ];
    }

    /**
     * (JST)
     * 2024-07-01 00:00:00 パス購入
     * 2024-07-04 00:00:00 パス有効期間の終了日時
     * 2024-07-04 04:00:00 パス毎日報酬受取可期間 終了日時
     */
    #[DataProvider('params_updateDailyPassReward_期限切れテスト')]
    public function test_updateDailyPassReward_期限切れテスト(
        string $nowUtc,
        int $expectedRewardCount,
        bool $shouldReceive
    ) {
        $now = $this->fixTime($nowUtc);
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();

        // 3日間のパス設定（UTC基準）
        $startAt = CarbonImmutable::parse('2024-06-30 15:00:00'); // JST 2024-07-01 00:00:00
        $endAt = CarbonImmutable::parse('2024-07-02 20:00:00');

        $mstShopPass = MstShopPass::factory()->create([
            'pass_duration_days' => 3,
        ])->toEntity();

        MstShopPassI18n::factory()->create([
            'mst_shop_pass_id' => $mstShopPass->getId(),
            'name' => 'テスト3日間パス',
        ]);

        // パスを持っているユーザー（まだ今日の報酬は受け取っていない）
        $usrShopPass = UsrShopPass::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_shop_pass_id' => $mstShopPass->getId(),
            'daily_reward_received_count' => 2, // 2日分受け取り済み
            'daily_latest_received_at' => '2024-07-01 10:00:00', // 昨日に受け取り（UTC）
            'start_at' => $startAt->toDateTimeString(),
            'end_at' => $endAt->toDateTimeString(),
        ]);

        // 毎日報酬の設定
        $mstShopPassReward = MstShopPassReward::factory()->create([
            'mst_shop_pass_id' => $mstShopPass->getId(),
            'pass_reward_type' => 1, // 毎日報酬
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => null,
            'resource_amount' => 100,
        ]);

        // テスト実行
        $this->shopService->updateDailyPassReward($usrUserId, $now);
        $this->saveAll();

        // 検証: パスの報酬受取回数と最終受取日時
        $updatedUsrShopPass = UsrShopPass::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($expectedRewardCount, $updatedUsrShopPass->getDailyRewardReceivedCount());

        if ($shouldReceive) {
            // 受け取れる場合は最終受取日時が更新される
            $this->assertEquals($now->toDateTimeString(), $updatedUsrShopPass->getDailyLatestReceivedAt());
        } else {
            // 受け取れない場合は最終受取日時は変わらない
            $this->assertEquals('2024-07-01 10:00:00', $updatedUsrShopPass->getDailyLatestReceivedAt());
        }
    }

    public function test_consumePackCost_初回無料フラグが有効な場合1回目はコストを消費しない()
    {
        // Setup
        $now = $this->fixTime('2024-07-01 10:00:00');
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $initialDiamond = 100;
        $costAmount = 50;

        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 100,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 100,
        ]);

        // ダイヤモンドを作成
        $this->createDiamond($usrUserId, freeDiamond: $initialDiamond);

        $mstPack = MstPack::factory()->create([
            'cost_type' => MstPackCostType::DIAMOND->value,
            'pack_type'=> PackType::DAILY->value,
            'cost_amount' => $costAmount,
            'is_first_time_free' => 1, // 初回無料フラグ有効
        ])->toEntity();

        $platform = UserConstant::PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        // 1回目の購入（初回無料）
        $this->shopService->consumePackCost(
            $usrUserId,
            $mstPack,
            $platform,
            $billingPlatform,
            $now
        );
        $this->saveAll();

        // Verify: ダイヤモンドが消費されていないこと
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals($initialDiamond, $diamond->getFreeAmount());
    }

    public function test_consumePackCost_初回無料フラグが有効でも2回目はコストを消費する()
    {
        // Setup
        $now = $this->fixTime('2024-07-01 10:00:00');
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $initialDiamond = 100;
        $costAmount = 50;

        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 100,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 100,
        ]);

        // ダイヤモンドを作成
        $this->createDiamond($usrUserId, freeDiamond: $initialDiamond);

        $mstPack = MstPack::factory()->create([
            'cost_type' => MstPackCostType::DIAMOND->value,
            'pack_type'=> PackType::DAILY->value,
            'cost_amount' => $costAmount,
            'is_first_time_free' => 1, // 初回無料フラグ有効
        ])->toEntity();

        $platform = UserConstant::PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        // 1回目の購入（初回無料でコスト消費なし）
        $this->shopService->consumePackCost(
            $usrUserId,
            $mstPack,
            $platform,
            $billingPlatform,
            $now
        );

        // usrTradePackの交換回数を1回に設定（1回目の交換を模擬）
        $usrTradePackRepository = $this->app->make(\App\Domain\Shop\Repositories\UsrTradePackRepository::class);
        $usrTradePack = $usrTradePackRepository->findOrCreate($usrUserId, $mstPack->getId(), $now);
        $usrTradePack->incrementTradeCount();
        $usrTradePackRepository->syncModel($usrTradePack);
        $this->saveAll();

        // 2回目の購入（コスト消費）
        $this->shopService->consumePackCost(
            $usrUserId,
            $mstPack,
            $platform,
            $billingPlatform,
            $now
        );
        $this->saveAll();

        // Verify: ダイヤモンドが消費されていること
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals($initialDiamond - $costAmount, $diamond->getFreeAmount());
    }

    public function test_consumePackCost_初回無料フラグが無効な場合1回目でもコストを消費する()
    {
        // Setup
        $now = $this->fixTime('2024-07-01 10:00:00');
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $initialDiamond = 100;
        $costAmount = 50;

        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 100,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 100,
        ]);

        // ダイヤモンドを作成
        $this->createDiamond($usrUserId, freeDiamond: $initialDiamond);

        $mstPack = MstPack::factory()->create([
            'cost_type' => MstPackCostType::DIAMOND->value,
            'pack_type'=> PackType::DAILY->value,
            'cost_amount' => $costAmount,
            'is_first_time_free' => 0, // 初回無料フラグ無効
        ])->toEntity();

        $platform = UserConstant::PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        // 1回目の購入（コスト消費）
        $this->shopService->consumePackCost(
            $usrUserId,
            $mstPack,
            $platform,
            $billingPlatform,
            $now
        );
        $this->saveAll();

        // Verify: ダイヤモンドが消費されていること
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals($initialDiamond - $costAmount, $diamond->getFreeAmount());
    }
}
