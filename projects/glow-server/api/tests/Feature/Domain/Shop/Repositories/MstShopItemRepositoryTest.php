<?php

namespace Feature\Domain\Shop\Repositories;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstShopItem;
use App\Domain\Resource\Mst\Repositories\MstShopItemRepository;
use App\Domain\Shop\Enums\ShopItemCostType;
use App\Domain\Shop\Enums\ShopType;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class MstShopItemRepositoryTest extends TestCase
{
    private MstShopItemRepository $mstShopItemRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mstShopItemRepository = app(MstShopItemRepository::class);
    }

    private function generateMstShopItem(string $mstShopItemId, string $startDate, string $endDate): void
    {
        MstShopItem::factory()->create([
            'id' => $mstShopItemId,
            'shop_type' => ShopType::COIN,
            'cost_type' => ShopItemCostType::DIAMOND,
            'cost_amount' => 10,
            'is_first_time_free' => 0,
            'tradable_count' => 1,
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 100,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * @test
     */
    public function getActiveShopItems_アクティブなアイテムを取得()
    {
        $startDate = '2024-01-01 00:00:00';
        $endDate = '2024-01-31 00:00:00';
        $this->generateMstShopItem(fake()->uuid(), $startDate, $endDate);

        $params = [
            // 境界値(開始と同時刻)
            new CarbonImmutable($startDate),
            // 中間
            (new CarbonImmutable($startDate))->addDays(10),
            // 境界値(終了と同時刻)
            new CarbonImmutable($endDate),
        ];
        foreach ($params as $now) {
            $actual = $this->mstShopItemRepository->getActiveShopItems($now);
            $this->assertCount(1, $actual);
        }

    }

    /**
     * @test
     */
    public function getActiveShopItems_アクティブなアイテムがない()
    {
        $startDate = '2024-01-01 00:00:00';
        $endDate = '2024-01-31 00:00:00';
        $this->generateMstShopItem(fake()->uuid(), $startDate, $endDate);

        $params = [
            // 境界値(開始と同時刻)
            (new CarbonImmutable($startDate))->subSeconds(),
            // 境界値(終了と同時刻)
            (new CarbonImmutable($endDate))->addSeconds(),
        ];
        foreach ($params as $now) {
            $actual = $this->mstShopItemRepository->getActiveShopItems($now);
            $this->assertCount(0, $actual);
        }
    }

    /**
     * @test
     */
    public function getActiveShopItemById_IDでアイテムを取得()
    {
        $mstShopItemId = fake()->uuid();
        $startDate = '2024-01-01 00:00:00';
        $endDate = '2024-01-31 00:00:00';
        $this->generateMstShopItem($mstShopItemId, $startDate, $endDate);

        $params = [
            // データが存在するID
            [$mstShopItemId, false],
            // データが存在しないID
            ['aaa', true]
        ];
        $now = new CarbonImmutable('2024-01-15 00:00:00');
        foreach ($params as [$id, $isNull]) {
            $actual = $this->mstShopItemRepository->getActiveShopItemById($id, $now);
            $this->assertEquals($isNull, is_null($actual));
        }
    }
}
