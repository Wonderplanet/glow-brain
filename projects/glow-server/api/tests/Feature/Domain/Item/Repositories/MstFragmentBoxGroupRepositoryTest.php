<?php

namespace Tests\Feature\Domain\Item\Repositories;

use App\Domain\Resource\Mst\Repositories\MstFragmentBoxGroupRepository;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Resource\Mst\Models\MstFragmentBoxGroup;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class MstFragmentBoxGroupRepositoryTest extends TestCase
{
    private MstFragmentBoxGroupRepository $mstFragmentBoxGroupRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mstItemRepository = app(MstFragmentBoxGroupRepository::class);
    }

    public function test_アクティブなキャラのかけらをグループID指定で取得()
    {
        $mstGroupId = 'test_1';
        MstFragmentBoxGroup::factory()->createMany([
            [
                'id' => '1',
                'mst_fragment_box_group_id' => $mstGroupId,
                'mst_item_id' => '2',
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2038-01-01 00:00:00',
            ],
            [
                'id' => '2',
                'mst_fragment_box_group_id' => $mstGroupId,
                'mst_item_id' => '3',
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2038-01-01 00:00:00',
            ],
            [
                'id' => '3',
                'mst_fragment_box_group_id' => 'other',
                'mst_item_id' => '4',
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2038-01-01 00:00:00',
            ],
        ]);

        $results = $this->mstItemRepository->getActiveFragmentByGroupId($mstGroupId, CarbonImmutable::now());
        $this->assertCount(2, $results);
        foreach ($results as $result) {
            $this->assertEquals($mstGroupId, $result->getMstFragmentBoxGroupId());
        }
    }

    public function test_非アクティブなキャラのかけらをグループID指定で取得()
    {
        $mstGroupId = 'test_2';
        MstFragmentBoxGroup::factory()->createMany([
            [
                'id' => '1',
                'mst_fragment_box_group_id' => $mstGroupId,
                'mst_item_id' => '2',
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2021-01-01 00:00:00',
            ],
            [
                'id' => '2',
                'mst_fragment_box_group_id' => $mstGroupId,
                'mst_item_id' => '3',
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2021-01-01 00:00:00',
            ],
            [
                'id' => '3',
                'mst_fragment_box_group_id' => 'other',
                'mst_item_id' => '4',
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2038-01-01 00:00:00',
            ],
        ]);

        $results = $this->mstItemRepository->getActiveFragmentByGroupId($mstGroupId, CarbonImmutable::now());
        $this->assertEquals(collect(), $results);
    }
}
