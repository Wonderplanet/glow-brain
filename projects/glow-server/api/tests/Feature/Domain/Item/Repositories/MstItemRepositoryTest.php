<?php

namespace Feature\Domain\Item\Repositories;

use App\Domain\Resource\Mst\Entities\MstItemEntity;
use App\Domain\Resource\Mst\Repositories\MstItemRepository;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Unit\Enums\UnitColorType;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class MstItemRepositoryTest extends TestCase
{
    private MstItemRepository $mstItemRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mstItemRepository = app(MstItemRepository::class);
    }

    /**
     * @test
     */
    public function アクティブなアイテムをID指定で取得()
    {
        $mstItemId = 'test_item_1';
        MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::CHARACTER_FRAGMENT->value,
            'rarity' => 'SSR',
            'start_date' => '2023-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ]);

        $result = $this->mstItemRepository->getActiveItemById($mstItemId, CarbonImmutable::now());

        $this->assertEquals($mstItemId, $result->getId());
    }

    /**
     * @test
     */
    public function 非アクティブなアイテムをID指定で取得()
    {
        $mstItemId = 'test_item_2';
        MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::CHARACTER_FRAGMENT->value,
            'rarity' => 'SSR',
            'start_date' => '2020-01-01 00:00:00',
            'end_date' => '2021-01-01 00:00:00',
        ]);

        $result = $this->mstItemRepository->getActiveItemById($mstItemId, CarbonImmutable::now());

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function 境界値テスト()
    {
        $mstItemId = 'test_item_3';
        MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::CHARACTER_FRAGMENT->value,
            'rarity' => 'SSR',
            'start_date' => '2023-01-01 00:00:00',
            'end_date' => '2024-01-01 00:00:00',
        ]);

        $now = new CarbonImmutable('2022-12-31 23:59:59');
        $result = $this->mstItemRepository->getActiveItemById($mstItemId, $now);
        $this->assertNull($result);

        $now = new CarbonImmutable('2023-01-01 00:00:00');
        $result = $this->mstItemRepository->getActiveItemById($mstItemId, $now);
        $this->assertEquals($mstItemId, $result->getId());

        $now = new CarbonImmutable('2024-01-01 00:00:00');
        $result = $this->mstItemRepository->getActiveItemById($mstItemId, $now);
        $this->assertEquals($mstItemId, $result->getId());

        $now = new CarbonImmutable('2024-01-01 00:00:01');
        $result = $this->mstItemRepository->getActiveItemById($mstItemId, $now);
        $this->assertNull($result);
    }

    public function test_getRankUpMaterials_全色のランクアップ素材を取得できる()
    {
        // Setup
        $now = CarbonImmutable::now();

        $mstItemParams = [];
        foreach (UnitColorType::cases() as $unitColorType) {
            $mstItemId = "rank_up_material_{$unitColorType->value}";
            $mstItemParams[$mstItemId] = [
                'id' => $mstItemId,
                'type' => ItemType::RANK_UP_MATERIAL->value,
                'effect_value' => $unitColorType->value,
            ];
        }
        $expectedMstItems = MstItem::factory()->createMany(
            $mstItemParams
        )->map(fn(MstItem $mstItem) => $mstItem->toEntity())
        ->keyBy(fn(MstItemEntity $mstItemEntity) => $mstItemEntity->getId());

        // Exercise
        $result = $this->mstItemRepository->getRankUpMaterials($now);

        // Verify
        $this->assertCount($expectedMstItems->count(), $result);

        foreach ($result as $actualMstItemEntity) {
            $this->assertArrayHasKey($actualMstItemEntity->getId(), $expectedMstItems);
        }


    }
}
