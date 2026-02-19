<?php

namespace Tests\Feature\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\AdventBattle\Enums\AdventBattleType;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Services\AdventBattleRewardMaxScoreService;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\MstAdventBattleRewardEntity;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use App\Domain\Resource\Mst\Models\MstAdventBattleReward;
use App\Domain\Resource\Mst\Models\MstAdventBattleRewardGroup;
use Tests\TestCase;

class AdventBattleRewardMaxScoreServiceTest extends TestCase
{
    private AdventBattleRewardMaxScoreService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AdventBattleRewardMaxScoreService::class);
    }

    private function createTestData(string $mstAdventBattleId): void
    {
        MstAdventBattle::factory()->create([
            'id' => $mstAdventBattleId,
            'advent_battle_type' => AdventBattleType::SCORE_CHALLENGE->value,
            'start_at' => now()->subDay()->toDateTimeString(),
            'end_at' => now()->addDay()->toDateTimeString(),
        ]);
        MstAdventBattleRewardGroup::factory()->createMany([
            [
                'id' => '1',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::MAX_SCORE->value,
                'condition_value' => '10',
            ],
            [
                'id' => '2',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::MAX_SCORE->value,
                'condition_value' => '20',
            ],
            [
                'id' => '3',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::MAX_SCORE->value,
                'condition_value' => '30',
            ],
            // データが絞れてるか確認する為に余分なデータを入れる
            [
                'id' => '4',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RAID_TOTAL_SCORE->value,
                'condition_value' => '12',
            ],
            [
                'id' => '5',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANKING->value,
                'condition_value' => '14',
            ],
            [
                'id' => '6',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANK->value,
                'condition_value' => '16',
            ],
        ]);
        MstAdventBattleReward::factory()->createMany([
            [
                'id' => '1_1',
                'mst_advent_battle_reward_group_id' => '1',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '1_1_1',
            ],
            [
                'id' => '2_1',
                'mst_advent_battle_reward_group_id' => '2',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '2_1_1',
            ],
            [
                'id' => '2_2',
                'mst_advent_battle_reward_group_id' => '2',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '2_2_2',
            ],
            [
                'id' => '3_1',
                'mst_advent_battle_reward_group_id' => '3',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '3_1_1',
            ],
        ]);
    }

    public function test_fetchAvailableRewards_正常実行()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $this->fixTime();
        $mstAdventBattleId = 'advent_battle_1';
        $this->createTestData($mstAdventBattleId);

        $usrAdventBattle = UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'max_score' => 25,
        ]);

        // Exercise
        $adventBattleReceivableReward = $this->service->fetchAvailableRewards($usrAdventBattle);
        $mstAdventBattleRewards = $adventBattleReceivableReward->getMstAdventBattleRewards();

        // Verify
        /** @var MstAdventBattleRewardEntity $actual */
        $actual = $mstAdventBattleRewards->get(0);
        $this->assertEquals('1', $actual->getMstAdventBattleRewardGroupId());
        $this->assertEquals('1_1', $actual->getId());
        $this->assertEquals('1_1_1', $actual->getResourceId());
        $actual = $mstAdventBattleRewards->get(1);
        $this->assertEquals('2', $actual->getMstAdventBattleRewardGroupId());
        $this->assertEquals('2_1', $actual->getId());
        $this->assertEquals('2_1_1', $actual->getResourceId());
        $actual = $mstAdventBattleRewards->get(2);
        $this->assertEquals('2', $actual->getMstAdventBattleRewardGroupId());
        $this->assertEquals('2_2', $actual->getId());
        $this->assertEquals('2_2_2', $actual->getResourceId());
    }

    public function test_fetchAvailableRewards_受取済み報酬チェック()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $this->fixTime();
        $mstAdventBattleId = 'advent_battle_1';
        $this->createTestData($mstAdventBattleId);

        $usrAdventBattle = UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'max_score' => 25,
            'max_received_max_score_reward' => 10
        ]);

        // Exercise
        $adventBattleReceivableReward = $this->service->fetchAvailableRewards($usrAdventBattle);
        $mstAdventBattleRewards = $adventBattleReceivableReward->getMstAdventBattleRewards()->values();

        // Verify
        /** @var MstAdventBattleRewardEntity $actual */
        $actual = $mstAdventBattleRewards->get(0);
        $this->assertEquals('2', $actual->getMstAdventBattleRewardGroupId());
        $this->assertEquals('2_1', $actual->getId());
        $this->assertEquals('2_1_1', $actual->getResourceId());
        $actual = $mstAdventBattleRewards->get(1);
        $this->assertEquals('2', $actual->getMstAdventBattleRewardGroupId());
        $this->assertEquals('2_2', $actual->getId());
        $this->assertEquals('2_2_2', $actual->getResourceId());
    }
}
