<?php

namespace Feature\Domain\Item\Services;


use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Item\Services\UsrItemService;
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Enums\UnreceivedRewardReason;
use App\Domain\Resource\Mst\Models\MstItem;
use Tests\Support\Entities\TestLogTrigger;
use Tests\TestCase;

class UsrItemServiceTest extends TestCase
{
    private UsrItemService $usrItemService;

    public function setUp(): void
    {
        parent::setUp();
        $this->usrItemService = app(UsrItemService::class);
    }

    public function test_consume_アイテムを消費する()
    {
        $usrId = $this->createUsrUser()->getId();

        // 別のテストと同じIDで採番するとDuplicate Entryするので一旦別名（RefreshDatabaseしたら大丈夫かと思ったけど）
        $mstItemId = 'test_item_consume_1';
        MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::CHARACTER_FRAGMENT->value,
            'rarity' => 'SSR',
            'start_date' => '2022-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ]);

        UsrItem::factory()->create([
            'id' => 'test_usr_item_1',
            'usr_user_id' => $usrId,
            'mst_item_id' => $mstItemId,
            'amount' => 10,
        ]);

        $result = $this->usrItemService->consumeItem($usrId, $mstItemId, 5, new TestLogTrigger());

        $this->assertNotNull($result);
        $this->assertEquals($mstItemId, $result->getMstItemId());
        $this->assertEquals($usrId, $result->getUsrUserId());
        $this->assertEquals(5, $result->getAmount());
    }

    public function test_consume_消費数量0の場合は消費されない()
    {
        $usrId = $this->createUsrUser()->getId();

        // 別のテストと同じIDで採番するとDuplicate Entryするので一旦別名（RefreshDatabaseしたら大丈夫かと思ったけど）
        $mstItemId = 'test_item_consume_2';
        MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::CHARACTER_FRAGMENT->value,
            'rarity' => 'SSR',
            'start_date' => '2022-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ]);

        UsrItem::factory()->create([
            'id' => 'test_usr_item_2',
            'usr_user_id' => $usrId,
            'mst_item_id' => $mstItemId,
            'amount' => 10,
        ]);

        $this->usrItemService->consumeItem($usrId, $mstItemId, 0, new TestLogTrigger());

        $usrItem = UsrItem::where('usr_user_id', $usrId)
            ->where('mst_item_id', $mstItemId)
            ->first();
        $this->assertEquals(10, $usrItem->getAmount());
    }

    public function test_consume_消費対象アイテムのレコードがなく消費数量0の場合は消費されない()
    {
        $usrId = $this->createUsrUser()->getId();

        // 別のテストと同じIDで採番するとDuplicate Entryするので一旦別名（RefreshDatabaseしたら大丈夫かと思ったけど）
        $mstItemId = 'test_item_consume_2';
        MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::CHARACTER_FRAGMENT->value,
            'rarity' => 'SSR',
            'start_date' => '2022-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ]);

        $result = $this->usrItemService->consumeItem($usrId, $mstItemId, 0, new TestLogTrigger());
        $this->assertNull($result);
    }

    public function test_consume_所持していないアイテムを消費する()
    {
        $usrId = $this->createUsrUser()->getId();

        // 別のテストと同じIDで採番するとDuplicate Entryするので一旦別名（RefreshDatabaseしたら大丈夫かと思ったけど）
        $mstItemId = 'test_item_consume_3';
        $unknownMstItemId = 'test_unknown_item_3';
        MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::CHARACTER_FRAGMENT->value,
            'rarity' => 'SSR',
            'start_date' => '2022-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ITEM_NOT_OWNED);

        $this->usrItemService->consumeItem($usrId, $unknownMstItemId, 5, new TestLogTrigger());
    }

    public function test_consume_アイテムを所持している以上に消費する()
    {
        $usrId = $this->createUsrUser()->getId();

        // 別のテストと同じIDで採番するとDuplicate Entryするので一旦別名（RefreshDatabaseしたら大丈夫かと思ったけど）
        $mstItemId = 'test_item_consume_4';
        MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::CHARACTER_FRAGMENT->value,
            'rarity' => 'SSR',
            'start_date' => '2022-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ]);

        UsrItem::factory()->create([
            'id' => 'test_usr_item_1',
            'usr_user_id' => $usrId,
            'mst_item_id' => $mstItemId,
            'amount' => 10,
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ITEM_AMOUNT_IS_NOT_ENOUGH);
        $this->usrItemService->consumeItem($usrId, $mstItemId, 15, new TestLogTrigger());
    }

    public function test_consumeItems_複数アイテムを消費できる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2025-04-14 00:00:00');

        UsrItem::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'item1', 'amount' => 100,],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'item2', 'amount' => 100,],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'item3', 'amount' => 100,],
        ]);

        // Exercise
        $this->usrItemService->consumeItems(
            $usrUserId,
            collect([
                'item1' => 10,
                'item2' => 20,
                'item3' => 30,
            ]),
            new TestLogTrigger(),
        );
        $this->saveAll();

        // Verify
        $usrItems = UsrItem::where('usr_user_id', $usrUserId)->get()->keyBy->getMstItemId();
        $this->assertCount(3, $usrItems);
        $this->assertEquals(90, $usrItems['item1']->getAmount());
        $this->assertEquals(80, $usrItems['item2']->getAmount());
        $this->assertEquals(70, $usrItems['item3']->getAmount());
    }

    public function test_consumeItems_一部未所持でエラー()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2025-04-14 00:00:00');

        UsrItem::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'item1', 'amount' => 100,],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'item2', 'amount' => 1,],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'item3', 'amount' => 100,],
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ITEM_AMOUNT_IS_NOT_ENOUGH);

        // Exercise
        $this->usrItemService->consumeItems(
            $usrUserId,
            collect([
                'item1' => 10,
                'item2' => 20,
                'item3' => 30,
            ]),
            new TestLogTrigger(),
        );

        // Verify
        $usrItems = UsrItem::where('usr_user_id', $usrUserId)->get()->keyBy->getMstItemId();
    }

    public function test_addItemByRewards_複数アイテム配布と上限超過切り捨て()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2025-04-14 00:00:00');

        // アイテムマスターデータ作成
        MstItem::factory()->createMany([
            ['id' => 'item1', 'type' => ItemType::CHARACTER_FRAGMENT->value, 'rarity' => 'SSR'],
            ['id' => 'item2', 'type' => ItemType::CHARACTER_FRAGMENT->value, 'rarity' => 'SSR'],
            ['id' => 'item3', 'type' => ItemType::CHARACTER_FRAGMENT->value, 'rarity' => 'SSR'],
        ]);

        // 配布前の所持量設定（異なる量）
        UsrItem::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'item1', 'amount' => 50],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'item2', 'amount' => 999999995], // 上限ギリギリ（上限は999999999）
            // item3は未所持
        ]);

        // 報酬データ作成（重複あり）
        $rewards = collect([
            new BaseReward(
                RewardType::ITEM->value,
                'item1',
                100,
                new LogTriggerDto('test', 'test', 'test')
            ),
            new BaseReward(
                RewardType::ITEM->value,
                'item1',
                50, // 重複
                new LogTriggerDto('test', 'test', 'test')
            ),
            new BaseReward(
                RewardType::ITEM->value,
                'item2',
                10, // 上限超過する
                new LogTriggerDto('test', 'test', 'test')
            ),
            new BaseReward(
                RewardType::ITEM->value,
                'item3',
                200, // 新規
                new LogTriggerDto('test', 'test', 'test')
            ),
        ]);

        // Exercise
        $this->usrItemService->addItemByRewards($usrUserId, $rewards, $now);
        $this->saveAll();

        // Verify
        $usrItems = UsrItem::where('usr_user_id', $usrUserId)->get()->keyBy->getMstItemId();

        // item1: 50 + 100 + 50 = 200
        $this->assertEquals(200, $usrItems['item1']->getAmount());

        // item2: 999999995 + 10 = 999999999（上限）
        $this->assertEquals(999999999, $usrItems['item2']->getAmount());

        // item3: 0 + 200 = 200（新規作成）
        $this->assertEquals(200, $usrItems['item3']->getAmount());

        // 報酬の状態確認
        $rewardsList = $rewards->values();

        // item1の報酬（正常配布）
        $this->assertEquals(UnreceivedRewardReason::NONE, $rewardsList[0]->getUnreceivedRewardReason());
        $this->assertEquals(UnreceivedRewardReason::NONE, $rewardsList[1]->getUnreceivedRewardReason());

        // item2の報酬（上限超過で切り捨て）
        $this->assertEquals(UnreceivedRewardReason::RESOURCE_OVERFLOW_DISCARDED, $rewardsList[2]->getUnreceivedRewardReason());

        // item3の報酬（正常配布）
        $this->assertEquals(UnreceivedRewardReason::NONE, $rewardsList[3]->getUnreceivedRewardReason());

        // 配布前後の量確認
        $this->assertEquals(50, $rewardsList[0]->getBeforeAmount());
        $this->assertEquals(150, $rewardsList[0]->getAfterAmount());

        $this->assertEquals(150, $rewardsList[1]->getBeforeAmount());
        $this->assertEquals(200, $rewardsList[1]->getAfterAmount());

        $this->assertEquals(999999995, $rewardsList[2]->getBeforeAmount());
        $this->assertEquals(999999999, $rewardsList[2]->getAfterAmount());

        $this->assertEquals(0, $rewardsList[3]->getBeforeAmount());
        $this->assertEquals(200, $rewardsList[3]->getAfterAmount());
    }
}
