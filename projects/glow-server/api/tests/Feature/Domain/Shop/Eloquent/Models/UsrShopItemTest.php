<?php

namespace Tests\Feature\Domain\Shop\Eloquent\Models;

use App\Domain\Shop\Models\UsrShopItem;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class UsrShopItemTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    private function generateUsrShopItem(string $lastResetAt): UsrShopItem
    {
        return UsrShopItem::factory()->create([
            'usr_user_id' => fake()->uuid(),
            'mst_shop_item_id' => fake()->uuid(),
            'trade_count' => mt_rand(1, 10),
            'trade_total_count' => mt_rand(1, 10),
            'last_reset_at' => $lastResetAt,
        ]);
    }

    /**
     * @test
     */
    public function reset_交換回数のリセット()
    {
        $usrShopItem = $this->generateUsrShopItem('2024-01-01 00:00:00');

        $now = new CarbonImmutable('now');
        $usrShopItem->reset($now);

        $this->assertEquals(0, $usrShopItem->getTradeCount());
        $this->assertEquals($now->format('Y-m-d H:i:s'), $usrShopItem->getLastResetAt());
    }

    /**
     * @test
     */
    public function incrementTradeCount_交換回数のインクリメント()
    {
        $usrShopItem = $this->generateUsrShopItem('2024-01-01 00:00:00');
        $beforeTradeCount = $usrShopItem->getTradeCount();
        $beforeTradeTotalCount = $usrShopItem->getTradeTotalCount();

        $usrShopItem->incrementTradeCount();
        $this->assertEquals($beforeTradeCount + 1, $usrShopItem->getTradeCount());
        $this->assertEquals($beforeTradeTotalCount + 1, $usrShopItem->getTradeTotalCount());
    }
}
