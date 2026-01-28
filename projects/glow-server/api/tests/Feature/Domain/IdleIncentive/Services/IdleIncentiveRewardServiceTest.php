<?php

namespace Tests\Feature\Domain\IdleIncentive\Services;

use App\Domain\IdleIncentive\Enums\IdleIncentiveExecMethod;
use App\Domain\IdleIncentive\Models\UsrIdleIncentive;
use App\Domain\IdleIncentive\Services\IdleIncentiveRewardService;
use App\Domain\Resource\Constants\MstConfigConstant;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstConfig;
use App\Domain\Resource\Mst\Models\MstIdleIncentive;
use App\Domain\Resource\Mst\Models\MstIdleIncentiveItem;
use App\Domain\Resource\Mst\Models\MstIdleIncentiveReward;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IdleIncentiveRewardServiceTest extends TestCase
{
    private IdleIncentiveRewardService $idleIncentiveRewardService;

    public function setUp(): void
    {
        parent::setUp();

        $this->idleIncentiveRewardService = app(IdleIncentiveRewardService::class);
    }

    public function test_calcRewards_経過時間で報酬が増えることを確認()
    {
        // Setup
        $minutes = 30;
        $usrUser = $this->createUsrUser();
        $now = $this->fixTime();
        $mstQuest = MstQuest::factory()->create([
            'id' => 'normalQuest1',
            'quest_type' => QuestType::NORMAL,
        ]);
        $mstStageIds = collect(['mstStage1', 'mstStage2', 'mstStage3']);
        $mstStageIds->map(function ($mstStageId, $index) {
            return MstStage::factory()->create([
                'id' => $mstStageId,
                'mst_quest_id' => 'normalQuest1',
                'sort_order' => $index,
            ])->toEntity();
        });
        MstIdleIncentive::factory()->create([
            'initial_reward_receive_minutes' => 10,
            'reward_increase_interval_minutes' => 10,
        ]);
        MstIdleIncentiveReward::factory()->create([
            'mst_stage_id' => 'mstStage3',
            'base_coin_amount' => 10,
            'base_exp_amount' => 20,
            'mst_idle_incentive_item_group_id' => 'itemGroup1',
        ]);
        MstIdleIncentiveItem::factory()->count(3)->sequence(
                ['mst_idle_incentive_item_group_id' => 'itemGroup1', 'mst_item_id' => 'item1', 'base_amount' => 30],
                ['mst_idle_incentive_item_group_id' => 'itemGroup1', 'mst_item_id' => 'item2', 'base_amount' => 40],
                ['mst_idle_incentive_item_group_id' => 'itemGroup1', 'mst_item_id' => 'item2', 'base_amount' => 10],
            )->create()
            ->map(function ($mstIdleIncentiveItem) {
            return $mstIdleIncentiveItem->toEntity();
        });

        $mstStageIds->map(function ($mstStageId) use ($usrUser) {
            return UsrStage::factory()->create([
                'usr_user_id' => $usrUser->getId(),
                'mst_stage_id' => $mstStageId,
                'clear_count' => 1,
            ]);
        });

        // usr_idle_incentivesにreward_mst_stage_idを設定
        UsrIdleIncentive::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'reward_mst_stage_id' => 'mstStage3',
        ]);

        // Exercise
        $result = $this->idleIncentiveRewardService->calcRewards(
            $usrUser->getId(),
            $now,
            $minutes,
            IdleIncentiveExecMethod::NORMAL,
            1,
        );

        // Verify
        $this->assertCount(5, $result);
        $rewards = $result->groupBy(function ($reward) {
            return $reward->getType();
        });
        $this->assertEquals(30, $rewards->get(RewardType::COIN->value)->first()->getAmount());
        $this->assertEquals(60, $rewards->get(RewardType::EXP->value)->first()->getAmount());

        $rewardItems = $rewards->get(RewardType::ITEM->value)->groupBy(function ($rewardItem) {
            return $rewardItem->getResourceId();
        })->map(function ($rewards) {
            return $rewards->sum(fn($reward) => $reward->getAmount());
        });
        $this->assertCount(2, $rewardItems);
        $this->assertEquals(90, $rewardItems->get('item1'));
        $this->assertEquals(150, $rewardItems->get('item2'));

    }

    #[DataProvider('params_test_getMstReward_ステージ進捗に応じて報酬情報を取得できる')]
    public function test_getMstReward_ステージ進捗に応じて報酬情報を取得できる(
        int $clearStageCount,
        ?int $expectedRewardIndex,
        ?string $rewardMstStageId,
    ) {
        // Setup
        $usrUser = $this->createUsrUser();
        $now = $this->fixTime();

        $mstIdleIncentiveRewards = collect();
        MstQuest::factory()->create([
            'id' => 'normalQuest1',
            'quest_type' => QuestType::NORMAL,
        ]);
        foreach ([1, 2, 3] as $i) {
            $mstStage = MstStage::factory()->create([
                'id' => "mstStage{$i}",
                'sort_order' => $i,
                'mst_quest_id' => "normalQuest1"
            ])->toEntity();

            // $clearStageCount番目のステージまでクリア済みにする
            UsrStage::factory()->create([
                'usr_user_id' => $usrUser->getId(),
                'mst_stage_id' => $mstStage->getId(),
                'clear_count' => $i <= $clearStageCount ? 1 : 0,
            ]);

            $mstIdleIncentiveRewards->put(
                $i,
                MstIdleIncentiveReward::factory()->create([
                    'mst_stage_id' => $mstStage->getId(),
                ])->toEntity()
            );
        }

        // 最低保証報酬の設定
        MstConfig::factory()->create([
            'key' => MstConfigConstant::IDLE_INCENTIVE_INITIAL_REWARD_MST_STAGE_ID,
            'value' => 'mstStage2',
        ]);

        // usr_idle_incentivesにreward_mst_stage_idを設定
        UsrIdleIncentive::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'reward_mst_stage_id' => $rewardMstStageId,
        ]);

        // Exercise
        $result = $this->idleIncentiveRewardService->getMstReward($usrUser->getId(), $now);

        // Verify
        if (is_null($expectedRewardIndex)) {
            $this->assertNull($result);
        } else {
            $this->assertEquals($mstIdleIncentiveRewards->get($expectedRewardIndex), $result);
        }
    }

    public static function params_test_getMstReward_ステージ進捗に応じて報酬情報を取得できる()
    {
        return [
            'ステージ1クリア済み ステージ1の報酬を取得' => [
                'clearStageCount' => 1,
                'expectedRewardIndex' => 1,
                'rewardMstStageId' => 'mstStage1',
            ],
            'ステージ2クリア済み ステージ2の報酬を取得' => [
                'clearStageCount' => 2,
                'expectedRewardIndex' => 2,
                'rewardMstStageId' => 'mstStage2',
            ],
            'ステージ3クリア済み ステージ3の報酬を取得' => [
                'clearStageCount' => 3,
                'expectedRewardIndex' => 3,
                'rewardMstStageId' => 'mstStage3',
            ],
            'ステージ未クリア mst_configsに最低保証報酬として設定したステージ2の報酬を取得' => [
                'clearStageCount' => 0,
                'expectedRewardIndex' => 2,
                'rewardMstStageId' => null,
            ],
        ];
    }

    public function test_calcCoinRewardAmounts_コインの報酬量が正常に計算できること()
    {
        // Setup
        $minutes = 30;
        $usrUser = $this->createUsrUser();
        $now = $this->fixTime();
        $mstQuest = MstQuest::factory()->create([
            'id' => 'normalQuest1',
            'quest_type' => QuestType::NORMAL,
        ]);
        $mstStageIds = collect(['mstStage1', 'mstStage2', 'mstStage3']);
        $mstStageIds->map(function ($mstStageId, $index) use ($mstQuest) {
            return MstStage::factory()->create([
                'id' => $mstStageId,
                'mst_quest_id' => 'normalQuest1',
                'sort_order' => $index,
            ])->toEntity();
        });
        $mstIdleIncentive = MstIdleIncentive::factory()->create([
            'initial_reward_receive_minutes' => 10,
            'reward_increase_interval_minutes' => 10,
        ])->toEntity();
        MstIdleIncentiveReward::factory()->create([
            'mst_stage_id' => 'mstStage3',
            'base_coin_amount' => 10,
        ]);
        $mstStageIds->map(function ($mstStageId) use ($usrUser) {
            return UsrStage::factory()->create([
                'usr_user_id' => $usrUser->getId(),
                'mst_stage_id' => $mstStageId,
                'clear_count' => 1,
            ]);
        });

        // usr_idle_incentivesにreward_mst_stage_idを設定
        UsrIdleIncentive::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'reward_mst_stage_id' => 'mstStage3',
        ]);

        // Exercise
        $result = $this->idleIncentiveRewardService->calcCoinRewardAmounts($usrUser->getId(), collect([$minutes]), $now);

        // Verify
        $this->assertEquals(30, $result->get($minutes));
    }

    // public function test_calcIdleBoxRewardAmounts_idleBoxアイテムの報酬量が正常に計算できること()
    // {
    //     // Setup
    //     $minutes = 30;
    //     $usrUser = $this->createUsrUser();
    //     $now = $this->fixTime();
    //     $mstQuest = MstQuest::factory()->create([
    //         'id' => 'normalQuest1',
    //         'quest_type' => QuestType::NORMAL,
    //     ]);
    //     $mstStageIds = collect(['mstStage1', 'mstStage2', 'mstStage3']);
    //     $mstStageIds->map(function ($mstStageId, $index) {
    //         return MstStage::factory()->create([
    //             'id' => $mstStageId,
    //             'mst_quest_id' => 'normalQuest1',
    //             'sort_order' => $index,
    //         ])->toEntity();
    //     });
    //     $mstIdleIncentive = MstIdleIncentive::factory()->create([
    //         'initial_reward_receive_minutes' => 10,
    //         'reward_increase_interval_minutes' => 10,
    //     ])->toEntity();
    //     MstIdleIncentiveReward::factory()->create([
    //         'mst_stage_id' => 'mstStage3',
    //         'base_coin_amount' => 20,
    //     ]);
    //     $mstStageIds->map(function ($mstStageId) use ($usrUser) {
    //         return UsrStage::factory()->create([
    //             'usr_user_id' => $usrUser->getId(),
    //             'mst_stage_id' => $mstStageId,
    //             'clear_count' => 1,
    //         ]);
    //     });

    //     $itemIdleBoxRewardExchangeList = collect([
    //         new ItemIdleBoxRewardExchange(
    //             new BaseReward(RewardType::ITEM->value, null, 0, new LogTriggerDto('test1', 'test1')),
    //             ItemType::IDLE_COIN_BOX->value,
    //             $minutes,
    //         ),
    //         new ItemIdleBoxRewardExchange(
    //             new BaseReward(RewardType::ITEM->value, 'rank_up_material_item_id', 0, new LogTriggerDto('test2', 'test2')),
    //             ItemType::IDLE_RANK_UP_MATERIAL_BOX->value,
    //             $minutes,
    //         ),
    //     ]);

    //     // Exercise
    //     $result = $this->idleIncentiveRewardService->calcIdleBoxRewardAmounts(
    //         $usrUser->getId(),
    //         $itemIdleBoxRewardExchangeList,
    //         $now,
    //     );

    //     // Verify
    //     $actual = $result->mapWithKeys(function ($itemIdleBoxRewardExchange) {
    //         return [$itemIdleBoxRewardExchange->getItemType() => $itemIdleBoxRewardExchange->getAfterAmount()];
    //     });
    //     $this->assertEquals(60, $actual->get(ItemType::IDLE_COIN_BOX->value));
    //     $this->assertEquals(90, $actual->get(ItemType::IDLE_RANK_UP_MATERIAL_BOX->value));
    // }

    #[DataProvider('params_test_calcRewards_rewardMultiplierが適用されて報酬量が正しく計算されること')]
    public function test_calcRewards_rewardMultiplierが適用されて報酬量が正しく計算されること(
        float $rewardMultiplier,
        float $expectedCoinAmount,
        float $expectedExpAmount,
        float $expectedItem1Amount,
        float $expectedItem2Amount
    ): void {
        // Setup
        $minutes = 30;
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $now = $this->fixTime();

        MstQuest::factory()->create([
            'id' => 'normalQuest1',
            'quest_type' => QuestType::NORMAL,
        ]);
        MstStage::factory()->create([
            'id' => 'mstStage1',
            'mst_quest_id' => 'normalQuest1',
            'sort_order' => 1,
        ]);

        MstIdleIncentive::factory()->create([
            'initial_reward_receive_minutes' => 10,
            'reward_increase_interval_minutes' => 10,
        ]);

        MstIdleIncentiveReward::factory()->create([
            'mst_stage_id' => 'mstStage1',
            'base_coin_amount' => 12.3456,
            'base_exp_amount' => 23.4567,
            'mst_idle_incentive_item_group_id' => 'itemGroup1',
        ]);

        MstIdleIncentiveItem::factory()->count(2)->sequence(
            ['mst_idle_incentive_item_group_id' => 'itemGroup1', 'mst_item_id' => 'item1', 'base_amount' => 34.5678],
            ['mst_idle_incentive_item_group_id' => 'itemGroup1', 'mst_item_id' => 'item2', 'base_amount' => 45.6789],
        )->create()
        ->map(function ($mstIdleIncentiveItem) {
            return $mstIdleIncentiveItem->toEntity();
        });

        UsrStage::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'mstStage1',
            'clear_count' => 1,
        ]);

        UsrIdleIncentive::factory()->create([
            'usr_user_id' => $usrUserId,
            'reward_mst_stage_id' => 'mstStage1',
        ]);

        // Exercise
        $result = $this->idleIncentiveRewardService->calcRewards(
            $usrUserId,
            $now,
            $minutes,
            IdleIncentiveExecMethod::NORMAL,
            $rewardMultiplier,
        );

        // Verify
        $this->assertCount(4, $result);
        $rewards = $result->groupBy(function ($reward) {
            return $reward->getType();
        });

        $this->assertEquals($expectedCoinAmount, $rewards->get(RewardType::COIN->value)->first()->getAmount());
        $this->assertEquals($expectedExpAmount, $rewards->get(RewardType::EXP->value)->first()->getAmount());

        $rewardItems = $rewards->get(RewardType::ITEM->value)->groupBy(function ($rewardItem) {
            return $rewardItem->getResourceId();
        })->map(function ($rewards) {
            return $rewards->sum(fn($reward) => $reward->getAmount());
        });
        $this->assertCount(2, $rewardItems);
        $this->assertEquals($expectedItem1Amount, $rewardItems->get('item1'));
        $this->assertEquals($expectedItem2Amount, $rewardItems->get('item2'));
    }

    public static function params_test_calcRewards_rewardMultiplierが適用されて報酬量が正しく計算されること(): array
    {
        return [
            'rewardMultiplier 1.000 (基本値)' => [
                'rewardMultiplier' => 1.000,
                'expectedCoinAmount' => 37,     // 37.0368 (12.3456 × 3) を切り捨て
                'expectedExpAmount' => 70,      // 70.3701 (23.4567 × 3) を切り捨て
                'expectedItem1Amount' => 103,   // 103.7034 (34.5678 × 3) を切り捨て
                'expectedItem2Amount' => 137,   // 137.0367 (45.6789 × 3) を切り捨て
            ],
            'rewardMultiplier 2' => [
                'rewardMultiplier' => 2,
                'expectedCoinAmount' => 74,     // 74.0736 (12.3456 × 3 × 2) を切り捨て
                'expectedExpAmount' => 140,     // 140.7402 (23.4567 × 3 × 2) を切り捨て
                'expectedItem1Amount' => 207,   // 207.4068 (34.5678 × 3 × 2) を切り捨て
                'expectedItem2Amount' => 274,   // 274.0734 (45.6789 × 3 × 2) を切り捨て
            ],
            'rewardMultiplier 3' => [
                'rewardMultiplier' => 3,
                'expectedCoinAmount' => 111,    // 111.1104 (12.3456 × 3 × 3) を切り捨て
                'expectedExpAmount' => 211,     // 211.1103 (23.4567 × 3 × 3) を切り捨て
                'expectedItem1Amount' => 311,   // 311.1102 (34.5678 × 3 × 3) を切り捨て
                'expectedItem2Amount' => 411,   // 411.1101 (45.6789 × 3 × 3) を切り捨て
            ],
        ];
    }
}
