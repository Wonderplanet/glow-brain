<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Shop\UseCases;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstShopItem;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Shop\Enums\ShopItemCostType;
use App\Domain\Shop\Enums\ShopType;
use App\Domain\Shop\Models\UsrShopItem;
use App\Domain\Shop\UseCases\ShopTradeShopItemUseCase;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class ShopTradeShopItemUseCaseTest extends TestCase
{
    private ShopTradeShopItemUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = $this->app->make(ShopTradeShopItemUseCase::class);
    }

    public static function params_tradeShopItem_正常に交換できる(): array
    {
        // 交換可能回数, ユーザーの交換回数, 初回無料フラグ
        return [
            '交換上限なし 1回目 初回無料なし' => [NULL, 0, false],
            '交換上限なし 2回目 初回無料なし' => [NULL, 1, false],
            '交換上限なし 1回目 初回無料あり' => [NULL, 0, true],
            '交換上限なし 2回目 初回無料あり' => [NULL, 1, true],
            '交換上限あり 1回目 初回無料なし' => [5, 0, false],
            '交換上限あり 上限値 初回無料なし' => [5, 4, false],
            '交換上限あり 1回目 初回無料あり' => [5, 0, true],
            '交換上限あり 上限値 初回無料あり' => [5, 4, true],

        ];
    }

    #[DataProvider('params_tradeShopItem_正常に交換できる')]
    public function test_exec_正常に交換できる(?int $tradableCount, int $tradeCount, bool $isFirstTimeFree)
    {
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $cost = fake()->numberBetween(100, 1000);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'coin' => $cost,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10,
        ]);
        $this->createDiamond($usrUser->getId(), freeDiamond: 0);
        $mstShopItem = MstShopItem::factory()->create([
            'id' => fake()->uuid(),
            'shop_type' => ShopType::DAILY->value,
            'cost_type' => ShopItemCostType::COIN->value,
            'cost_amount' => $cost,
            'is_first_time_free' => (int)$isFirstTimeFree,
            'tradable_count' => $tradableCount,
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => null,
            'resource_amount' => 100,
            'start_date' => '2000-01-01 00:00:00',
            'end_date' => '2030-01-01 00:00:00'
        ])->toEntity();
        $usrShopItem = UsrShopItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_shop_item_id' => $mstShopItem->getId(),
            'trade_count' => $tradeCount,
            'trade_total_count' => 0,
            'last_reset_at' => now()->format('Y-m-d H:i:s'),
        ]);

        // 実行
        $this->useCase->exec($currentUser, $mstShopItem->getId(), UserConstant::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE);

        // コストが減ってること
        $actual = UsrUserParameter::query()->where('usr_user_id', $usrUser->getId())->first();
        if ($isFirstTimeFree && $tradeCount === 0) {
            // 初回無料の場合はコストが減っていないこと
            $this->assertEquals($usrUserParameter->getCoin(), $actual->getCoin());
        } else {
            // コストが減っていること
            $this->assertEquals($usrUserParameter->getCoin() - $mstShopItem->getCostAmount(), $actual->getCoin());
        }

        // 交換物を獲得していること
        $diamond = $this->getDiamond($usrUser->getId());
        $this->assertEquals($mstShopItem->getResourceAmount(), $diamond->getFreeAmount());

        // 交換回数が増えていること
        $actual = UsrShopItem::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals($tradeCount + 1, $actual->getTradeCount());
    }
}
