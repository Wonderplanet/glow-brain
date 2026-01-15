<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Item\UseCases;

use Tests\Support\Entities\CurrentUser;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Item\UseCases\ItemConsumeUseCase;
use App\Domain\Resource\Mst\Models\MstFragmentBox;
use App\Domain\Resource\Mst\Models\MstFragmentBoxGroup;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use Tests\TestCase;

class ItemConsumeUseCaseTest extends TestCase
{
    private ItemConsumeUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(ItemConsumeUseCase::class);
    }


    public function test_randomFragmentBox_アイテムの消費と付与がされることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 2,
            'stamina' => 4,
        ]);
        $this->createDiamond($usrUserId, 3);
        MstItem::factory()->createMany([
            [
                'id' => 'item1',
                'type' => ItemType::RANDOM_FRAGMENT_BOX->value,
            ],
            [
                'id' => 'item2',
                'type' => ItemType::CHARACTER_FRAGMENT->value,
            ],
            [
                'id' => 'item3',
                'type' => ItemType::CHARACTER_FRAGMENT->value,
            ],
            [
                'id' => 'item4',
                'type' => ItemType::CHARACTER_FRAGMENT->value,
            ],
            [
                'id' => 'item5',
                'type' => ItemType::CHARACTER_FRAGMENT->value,
            ],
        ]);
        MstFragmentBox::factory()->create([
            'id' => '1',
            'mst_item_id' => 'item1',
            'mst_fragment_box_group_id' => '1',
        ]);
        MstFragmentBoxGroup::factory()->createMany([
            [
                'id' => '1',
                'mst_fragment_box_group_id' => '1',
                'mst_item_id' => 'item2',
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2038-01-01 00:00:00',
            ],
            [
                'id' => '2',
                'mst_fragment_box_group_id' => '1',
                'mst_item_id' => 'item3',
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2038-01-01 00:00:00',
            ],
            [
                'id' => '3',
                'mst_fragment_box_group_id' => '1',
                'mst_item_id' => 'item4',
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2038-01-01 00:00:00',
            ],
            [
                'id' => '4',
                'mst_fragment_box_group_id' => '1',
                'mst_item_id' => 'item5',
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2038-01-01 00:00:00',
            ],
        ]);
        UsrItem::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_item_id' => 'item1',
                'amount' => 100,
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_item_id' => 'item2',
                'amount' => 5,
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_item_id' => 'item3',
                'amount' => 5,
            ],
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 100],
            ['level' => 3, 'stamina' => 10, 'exp' => 1000],
        ]);

        // Exercise
        $results = $this->useCase->exec($currentUser, UserConstant::PLATFORM_IOS, 'item1', 100);

        // Verify
        // DB確認
        $usrUserParameter->refresh();
        $this->assertEquals(1, $usrUserParameter->getLevel());
        $this->assertEquals(0, $usrUserParameter->getExp());
        $this->assertEquals(2, $usrUserParameter->getCoin());

        // アイテム消費の確認
        $usrItem = UsrItem::query()->where('usr_user_id', $usrUserId)->where('mst_item_id', 'item1')->first();
        $this->assertNotNull($usrItem);
        $this->assertEquals(0, $usrItem->getAmount());

        // アイテム付与の確認
        $amount = UsrItem::query()->where('usr_user_id', $usrUserId)->sum('amount');
        $this->assertEquals(110, $amount);

        // ResultData確認
        $this->assertEquals($usrUserParameter->getCoin(), $results->usrUserParameter->getCoin());
        $this->assertEquals($usrUserParameter->getExp(), $results->usrUserParameter->getExp());
        $this->assertEquals($usrUserParameter->getStamina(), $results->usrUserParameter->getStamina());
        $this->assertEquals($usrUserParameter->getLevel(), $results->usrUserParameter->getLevel());

        $amount = $results->usrItems->map(function ($usrItem) {
            return $usrItem->getAmount();
        })->sum();
        $this->assertEquals(110, $amount);

        $amount = $results->itemRewards->map(function ($usrItem) {
            return $usrItem->getAmount();
        })->sum();
        $this->assertEquals(100, $amount);
    }
}
