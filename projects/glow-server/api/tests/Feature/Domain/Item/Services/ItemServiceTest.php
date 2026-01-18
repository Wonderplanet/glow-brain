<?php

namespace Tests\Feature\Domain\Item\Services;

use App\Domain\Common\Constants\ErrorCode;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Item\Constants\ItemConstant;
use App\Domain\Item\Enums\ItemTradeResetType;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Item\Models\UsrItemTrade;
use App\Domain\Item\Services\ItemService;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Mst\Models\MstFragmentBox;
use App\Domain\Resource\Mst\Models\MstFragmentBoxGroup;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstItemRarityTrade;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Traits\TestRewardTrait;
use Tests\TestCase;

class ItemServiceTest extends TestCase
{
    use TestRewardTrait;

    private ItemService $itemService;

    public function setUp(): void
    {
        parent::setUp();
        $this->itemService = app(ItemService::class);
    }

    public function test_apply_ランダムかけらボックスの場合()
    {
        // Setup
        $now = CarbonImmutable::now();
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 2,
            'stamina' => 4,
        ]);

        $mstItem = MstItem::factory()->create([
            'id' => 'item1',
            'type' => ItemType::RANDOM_FRAGMENT_BOX->value,
        ])->toEntity();

        $this->createTestData($usrUserId);

        // Exercise
        $this->itemService->apply(
            $usrUserId,
            UserConstant::PLATFORM_IOS,
            $mstItem,
            ItemConstant::FRAGMENT_BOX_MAX_EXCHANGE,
            $now,
        );
        $this->sendRewards(
            $usrUserId,
            UserConstant::PLATFORM_IOS,
            $now,
        );

        // Verify
        $usrItems = UsrItem::query()->where('usr_user_id', $usrUserId)->get()->keyBy('mst_item_id');
        $totalAmount = $usrItems->sum('amount');
        $this->assertEquals(0, $usrItems['item1']->getAmount());
        $this->assertEquals(0 + 5 + 5 + 100, $totalAmount);
    }

    public function test_apply_ランダムかけらボックス_交換上限を超えた場合()
    {
        // Setup
        $now = CarbonImmutable::now();
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 2,
            'stamina' => 4,
        ]);
        $mstItem = MstItem::factory()->create([
            'id' => 'item1',
            'type' => ItemType::RANDOM_FRAGMENT_BOX->value,
        ])->toEntity();

        $this->createTestData($usrUserId);

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_PARAMETER);
        $this->itemService->apply(
            $usrUserId,
            1,
            $mstItem,
            ItemConstant::FRAGMENT_BOX_MAX_EXCHANGE + 1,
            $now,
        );
    }


    public function test_applySelectionFragmentBox_選択かけらボックスの場合()
    {
        // Setup
        $now = CarbonImmutable::now();
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $mstItem = MstItem::factory()->create([
            'id' => 'item1',
            'type' => ItemType::SELECTION_FRAGMENT_BOX->value,
        ])->toEntity();

        $this->createTestData($usrUserId);
        $selectMstItem = MstItem::query()->where('id', 'item2')->first()->toEntity();

        // Exercise
        $this->itemService->applyWithSelectItem(
            $usrUserId,
            UserConstant::PLATFORM_IOS,
            $mstItem,
            $selectMstItem,
            ItemConstant::FRAGMENT_BOX_MAX_EXCHANGE,
            $now,
        );
        $this->sendRewards(
            $usrUserId,
            UserConstant::PLATFORM_IOS,
            $now,
        );

        $dbSelectMstItem = UsrItem::query()->where('usr_user_id', $usrUserId)->where('mst_item_id', $selectMstItem->getId())->first();
        // 既存の個数に交換上限分追加されることを確認
        $this->assertEquals(5 + ItemConstant::FRAGMENT_BOX_MAX_EXCHANGE, $dbSelectMstItem->getAmount());
    }

    public function test_applySelectionFragmentBox_選択かけらボックスで交換上限を超えた場合()
    {
        // Setup
        $now = CarbonImmutable::now();
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $mstItem = MstItem::factory()->create([
            'id' => 'item1',
            'type' => ItemType::SELECTION_FRAGMENT_BOX->value,
        ])->toEntity();

        $this->createTestData($usrUserId);
        $selectMstItem = MstItem::query()->where('id', 'item2')->first()->toEntity();

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_PARAMETER);
        $this->itemService->applyWithSelectItem(
            $usrUserId,
            1,
            $mstItem,
            $selectMstItem,
            ItemConstant::FRAGMENT_BOX_MAX_EXCHANGE + 1,
            $now,
        );
    }

    public function test_applySelectionFragmentBox_選択かけらボックスで選択アイテムがかけらではない場合()
    {
        // Setup
        $now = CarbonImmutable::now();
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $mstItem = MstItem::factory()->create([
            'id' => 'item1',
            'type' => ItemType::SELECTION_FRAGMENT_BOX->value,
        ])->toEntity();
        $selectMstItem = MstItem::factory()->create([
            'id' => '100',
            'type' => ItemType::ETC->value,
        ])->toEntity();

        $this->createTestData($usrUserId);

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_PARAMETER);
        $this->itemService->applyWithSelectItem(
            $usrUserId,
            1,
            $mstItem,
            $selectMstItem,
            ItemConstant::FRAGMENT_BOX_MAX_EXCHANGE,
            $now,
        );
    }

    public function test_applySelectionFragmentBox_選択かけらボックスで選択アイテムが期間外の場合()
    {
        // Setup
        $now = CarbonImmutable::now();
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $mstItem = MstItem::factory()->create([
            'id' => 'item1',
            'type' => ItemType::SELECTION_FRAGMENT_BOX->value,
        ])->toEntity();

        $this->createTestData($usrUserId);
        $selectMstItem = MstItem::query()->where('id', 'item6')->first()->toEntity();

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_PARAMETER);
        $this->itemService->applyWithSelectItem(
            $usrUserId,
            1,
            $mstItem,
            $selectMstItem,
            ItemConstant::FRAGMENT_BOX_MAX_EXCHANGE,
            $now,
        );
    }

    private function createTestData(string $usrUserId)
    {
        MstItem::factory()->createMany([
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
            [
                'id' => 'item6',
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
            [
                'id' => '5',
                'mst_fragment_box_group_id' => '1',
                'mst_item_id' => 'item6',
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2020-01-01 00:00:00',
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
    }

    public static function params_test_apply_キャラかけらから選択かけらBOXへ交換できる()
    {
        return [
            '上限なし 初交換' => [null, 0, 5],
            '上限なし 交換N回目' => [null, 2, 3],
            '上限あり 上限未満の数を交換' => [2, 0, 1],
            '上限あり 上限ちょうどの数を交換 初交換' => [2, 0, 2],
            '上限あり 上限ちょうどの数を交換 交換N回目' => [2, 1, 1],
        ];
    }

    #[DataProvider('params_test_apply_キャラかけらから選択かけらBOXへ交換できる')]
    public function test_apply_キャラかけらから選択かけらBOXへ交換できる(
        ?int $maxTradableAmount,
        int $beforeTradeAmount,
        int $requestAcquireAmount,
    ) {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 2,
            'stamina' => 4,
        ]);

        // mst
        $mstItems = MstItem::factory()->createMany([
            ['id' => 'consumeItem', 'type' => ItemType::CHARACTER_FRAGMENT->value, 'rarity' => RarityType::SSR->value],
            ['id' => 'acquireItem', 'type' => ItemType::SELECTION_FRAGMENT_BOX->value, 'rarity' => RarityType::SSR->value],
        ])->map->toEntity()->keyBy->getId();
        MstItemRarityTrade::factory()->create([
            'rarity' => RarityType::SSR->value,
            'cost_amount' => 5,
            'max_tradable_amount' => $maxTradableAmount,
        ]);

        // usr
        UsrItem::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'consumeItem', 'amount' => 100,],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'acquireItem', 'amount' => $beforeTradeAmount,],
        ]);
        if ($maxTradableAmount !== null && $beforeTradeAmount > 0) {
            UsrItemTrade::factory()->create([
                'usr_user_id' => $usrUserId,
                'mst_item_id' => 'acquireItem',
                'trade_amount' => $beforeTradeAmount,
                'reset_trade_amount' => $beforeTradeAmount,
                'trade_amount_reset_at' => $now->toDateTimeString(), // リセットされない日時
            ]);
        }

        // Exercise
        $this->itemService->apply(
            $usrUserId,
            UserConstant::PLATFORM_IOS,
            $mstItems['consumeItem'],
            $requestAcquireAmount,
            $now,
        );
        $this->sendRewards(
            $usrUserId,
            UserConstant::PLATFORM_IOS,
            $now,
        );

        // Verify

        $tradedAmount = $beforeTradeAmount + $requestAcquireAmount;

        // DB確認
        $usrItems = UsrItem::query()->where('usr_user_id', $usrUserId)->get()->keyBy('mst_item_id');
        $this->assertEquals(100 - (5 * $requestAcquireAmount), $usrItems['consumeItem']->getAmount());
        $this->assertEquals($tradedAmount, $usrItems['acquireItem']->getAmount());

        $usrItemTrades = UsrItemTrade::query()->where('usr_user_id', $usrUserId)->get()->keyBy('mst_item_id');
        if (is_null($maxTradableAmount)) {
            $this->assertCount(0, $usrItemTrades);
        } else {
            $this->assertEquals($tradedAmount, $usrItemTrades['acquireItem']->getTradeAmount());
        }
    }

    public static function params_test_apply_キャラかけらから選択かけらBOXへ交換時の交換上限に引っかかる()
    {
        // 最大交換個数を2でマスタ設定したテスト
        return [
            '交換したことがないが、上限に引っかかる' => [0, 3],
            '過去の交換数に加算した結果、上限に引っかかる' => [2, 1],
        ];
    }

    #[DataProvider('params_test_apply_キャラかけらから選択かけらBOXへ交換時の交換上限に引っかかる')]
    public function test_apply_キャラかけらから選択かけらBOXへ交換時の交換上限に引っかかる(
        int $beforeTradeAmount,
        int $requestAcquireAmount,
    ) {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 2,
            'stamina' => 4,
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ITEM_TRADE_AMOUNT_LIMIT_EXCEEDED);

        // mst
        $mstItems = MstItem::factory()->createMany([
            ['id' => 'consumeItem', 'type' => ItemType::CHARACTER_FRAGMENT->value, 'rarity' => RarityType::SSR->value],
            ['id' => 'acquireItem', 'type' => ItemType::SELECTION_FRAGMENT_BOX->value, 'rarity' => RarityType::SSR->value],
        ])->map->toEntity()->keyBy->getId();
        MstItemRarityTrade::factory()->create([
            'rarity' => RarityType::SSR->value,
            'cost_amount' => 5,
            'max_tradable_amount' => 2,
        ]);

        // usr
        UsrItem::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_item_id' => 'consumeItem',
                'amount' => 100,
            ],
        ]);
        if ($beforeTradeAmount > 0) {
            UsrItemTrade::factory()->create([
                'usr_user_id' => $usrUserId,
                'mst_item_id' => 'acquireItem',
                'trade_amount' => $beforeTradeAmount,
                'reset_trade_amount' => $beforeTradeAmount,
                'trade_amount_reset_at' => $now->toDateTimeString(), // リセットされない日時
            ]);
        }

        // Exercise
        $this->itemService->apply(
            $usrUserId,
            UserConstant::PLATFORM_IOS,
            $mstItems['consumeItem'],
            $requestAcquireAmount,
            $now,
        );
        $this->saveAll();

        // Verify
        $this->assertTrue(true);
    }

    public static function params_test_apply_キャラかけらから選択かけらBOXへリセットを考慮して交換できる()
    {
        // テスト実行日時を「2024-11-12 04:00:00(火)(JST)」としてケース作成
        return [
            'リセットなし 上限エラー' => [ItemTradeResetType::NONE, 3, 1, ErrorCode::ITEM_TRADE_AMOUNT_LIMIT_EXCEEDED, '2024-11-11 19:00:00'],
            'デイリーリセット' => [ItemTradeResetType::DAILY, 3, 1, null, '2024-11-10 19:00:00'],
            'ウィークリーリセット' => [ItemTradeResetType::WEEKLY, 3, 1, null, '2024-11-09 19:00:00'], // 月曜が週始まりとして日曜を設定
            'マンスリーリセット' => [ItemTradeResetType::MONTHLY, 3, 1, null, '2024-09-30 19:00:00'],
            // リセットされず上限エラー
            'デイリーリセット リセットされず上限エラー' => [ItemTradeResetType::DAILY, 3, 1, ErrorCode::ITEM_TRADE_AMOUNT_LIMIT_EXCEEDED, '2024-11-11 19:00:00'],
            'ウィークリーリセット リセットされず上限エラー' => [ItemTradeResetType::WEEKLY, 3, 1, ErrorCode::ITEM_TRADE_AMOUNT_LIMIT_EXCEEDED, '2024-11-10 19:00:00'], // 月曜が週始まりとして同週の月曜を設定
            'マンスリーリセット リセットされず上限エラー' => [ItemTradeResetType::MONTHLY, 3, 1, ErrorCode::ITEM_TRADE_AMOUNT_LIMIT_EXCEEDED, '2024-10-31 19:00:00'],
        ];
    }

    #[DataProvider('params_test_apply_キャラかけらから選択かけらBOXへリセットを考慮して交換できる')]
    public function test_apply_キャラかけらから選択かけらBOXへリセットを考慮して交換できる(
        ItemTradeResetType $resetType,
        int $beforeTradeAmount,
        int $requestAcquireAmount,
        ?int $errorCode,
        string $beforeResetAt,
    ) {
        // Setup
        $now = $this->fixTime('2024-11-11 19:00:00');
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 2,
            'stamina' => 4,
        ]);

        // mst
        $mstItems = MstItem::factory()->createMany([
            ['id' => 'consumeItem', 'type' => ItemType::CHARACTER_FRAGMENT->value, 'rarity' => RarityType::SSR->value],
            ['id' => 'acquireItem', 'type' => ItemType::SELECTION_FRAGMENT_BOX->value, 'rarity' => RarityType::SSR->value],
        ])->map->toEntity()->keyBy->getId();
        MstItemRarityTrade::factory()->create([
            'rarity' => RarityType::SSR->value,
            'cost_amount' => 5,
            'max_tradable_amount' => 3,
            'reset_type' => $resetType->value,
        ]);

        // usr
        UsrItem::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'consumeItem', 'amount' => 100,],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'acquireItem', 'amount' => $beforeTradeAmount,],
        ]);
        UsrItemTrade::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'acquireItem',
            'trade_amount' => $beforeTradeAmount,
            'reset_trade_amount' => $beforeTradeAmount,
            'trade_amount_reset_at' => $beforeResetAt,
        ]);

        if ($errorCode !== null) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }

        // Exercise
        $this->itemService->apply(
            $usrUserId,
            UserConstant::PLATFORM_IOS,
            $mstItems['consumeItem'],
            $requestAcquireAmount,
            $now,
        );
        $this->sendRewards(
            $usrUserId,
            UserConstant::PLATFORM_IOS,
            $now,
        );

        // Verify

        // DB確認
        $usrItems = UsrItem::query()->where('usr_user_id', $usrUserId)->get()->keyBy('mst_item_id');
        $this->assertEquals(100 - (5 * $requestAcquireAmount), $usrItems['consumeItem']->getAmount());
        $this->assertEquals($beforeTradeAmount + $requestAcquireAmount, $usrItems['acquireItem']->getAmount());

        $usrItemTrades = UsrItemTrade::query()->where('usr_user_id', $usrUserId)->get()->keyBy('mst_item_id');
        $this->assertEquals($requestAcquireAmount, $usrItemTrades['acquireItem']->getResetTradeAmount());
        $this->assertEquals($beforeTradeAmount + $requestAcquireAmount, $usrItemTrades['acquireItem']->getTradeAmount());
    }
}
