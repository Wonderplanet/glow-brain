<?php

namespace Tests\Feature\Domain\Shop\Repositories;

use App\Domain\Shop\Models\UsrShopItem;
use App\Domain\Shop\Repositories\UsrShopItemRepository;
use App\Domain\User\Models\UsrUser;
use Tests\TestCase;

class UsrShopItemRepositoryTest extends TestCase
{
    private UsrShopItemRepository $usrShopItemRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->usrShopItemRepository = app(UsrShopItemRepository::class);
    }

    /**
     * @test
     */
    public function getList_ユーザーに紐づくショップアイテムを取得()
    {
        $usrUserId = fake()->uuid();
        UsrUser::factory()->create([
            'id' => $usrUserId,
            'tutorial_status' => 0,
        ]);

        $count = mt_rand(1, 5);
        foreach (range(1, $count) as $_) {
            UsrShopItem::factory()->create([
                'usr_user_id' => $usrUserId,
                'mst_shop_item_id' => fake()->uuid(),
                'trade_count' => 1,
                'trade_total_count' => 1,
                'last_reset_at' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        $actual = $this->usrShopItemRepository->getList($usrUserId);
        $this->assertCount($count, $actual);
    }

    /**
     * @test
     */
    public function get_特定IDのショップアイテムを取得()
    {
        $usrShopItem = UsrShopItem::factory()->create([
            'usr_user_id' => fake()->uuid(),
            'mst_shop_item_id' => fake()->uuid(),
            'trade_count' => 1,
            'trade_total_count' => 1,
            'last_reset_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $actual = $this->usrShopItemRepository->get($usrShopItem->getUsrUserId(), $usrShopItem->getMstShopItemId());
        $this->assertNotNull($actual);
    }

    /**
     * @test
     */
    public function saveModels_新規登録及び更新の一括処理()
    {
        $usrUserId = fake()->uuid();
        $usrShopItems = collect();

        $newCount = mt_rand(1, 5);
        $updateCount = mt_rand(1, 5);
        // 新規登録用データ
        foreach (range(1, $newCount) as $_) {
            $usrShopItems->push(UsrShopItem::factory()->make([
                'usr_user_id' => $usrUserId,
                'mst_shop_item_id' => fake()->uuid(),
                'trade_count' => mt_rand(1, 10),
                'trade_total_count' => mt_rand(1, 10),
                'last_reset_at' => fake()->dateTime()->format('Y-m-d H:i:s'),
            ]));
        }
        // 更新用データ
        foreach (range(1, $updateCount) as $_) {
            $usrShopItems->push(UsrShopItem::factory()->create([
                'usr_user_id' => $usrUserId,
                'mst_shop_item_id' => fake()->uuid(),
                'trade_count' => mt_rand(1, 10),
                'trade_total_count' => mt_rand(1, 10),
                'last_reset_at' => fake()->dateTime()->format('Y-m-d H:i:s'),
            ]));
        }

        $this->execPrivateMethod($this->usrShopItemRepository, 'saveModels', [$usrShopItems]);

        // 新規登録及び更新の検証
        $actual = UsrShopItem::query()->where('usr_user_id', $usrUserId)->get();
        $expectedMap = $usrShopItems->keyBy(fn (UsrShopItem $usrShopItem) => $usrShopItem->getMstShopItemId())->all();
        foreach ($actual as $usrShopItem) {
            $expected = $expectedMap[$usrShopItem->getMstShopItemId()];
            $this->assertEquals($expected->getTradeCount(), $usrShopItem->getTradeCount());
            $this->assertEquals($expected->getTradeTotalCount(), $usrShopItem->getTradeTotalCount());
            $this->assertEquals($expected->getLastResetAt(), $usrShopItem->getLastResetAt());
        }
    }
}
