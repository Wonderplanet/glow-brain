<?php

namespace Feature\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\AdventBattle\Enums\AdventBattleType;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Services\AdventBattleRewardRankService;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\MstAdventBattleRewardEntity;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use App\Domain\Resource\Mst\Models\MstAdventBattleRank;
use App\Domain\Resource\Mst\Models\MstAdventBattleReward;
use App\Domain\Resource\Mst\Models\MstAdventBattleRewardGroup;
use Tests\TestCase;

class AdventBattleRewardRankServiceTest extends TestCase
{
    private AdventBattleRewardRankService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AdventBattleRewardRankService::class);
    }

    private function createTestData(string $mstAdventBattleId): void
    {
        MstAdventBattle::factory()->create([
            'id' => $mstAdventBattleId,
            'advent_battle_type' => AdventBattleType::RAID->value,
            'start_at' => now()->subDay()->toDateTimeString(),
            'end_at' => now()->addDay()->toDateTimeString(),
        ]);
        MstAdventBattleRewardGroup::factory()->createMany([
            [
                'id' => '1',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANK->value,
                'condition_value' => 'rank_1',
            ],
            [
                'id' => '2',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANK->value,
                'condition_value' => 'rank_2',
            ],
            [
                'id' => '3',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANK->value,
                'condition_value' => 'rank_3',
            ],
            [
                'id' => '10',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANK->value,
                'condition_value' => 'rank_4',
            ],
            [
                'id' => '11',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANK->value,
                'condition_value' => 'rank_5',
            ],
            [
                'id' => '12',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANK->value,
                'condition_value' => 'rank_6',
            ],
            [
                'id' => '13',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANK->value,
                'condition_value' => 'rank_7',
            ],
            [
                'id' => '14',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANK->value,
                'condition_value' => 'rank_8',
            ],
            [
                'id' => '15',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANK->value,
                'condition_value' => 'rank_9',
            ],
            [
                'id' => '16',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANK->value,
                'condition_value' => 'rank_10',
            ],
            [
                'id' => '17',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANK->value,
                'condition_value' => 'rank_11',
            ],
            [
                'id' => '18',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANK->value,
                'condition_value' => 'rank_12',
            ],
            // データが絞れてるか確認する為に余分なデータを入れる
            [
                'id' => '4',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::MAX_SCORE->value,
                'condition_value' => 'rank_1',
            ],
            [
                'id' => '5',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANKING->value,
                'condition_value' => 'rank_2',
            ],
            [
                'id' => '6',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RAID_TOTAL_SCORE->value,
                'condition_value' => 'rank_3',
            ],
        ]);
        MstAdventBattleReward::factory()->createMany([
            [
                'id' => '3_1',
                'mst_advent_battle_reward_group_id' => '3',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '3_1_1',
            ],
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
                'id' => '10_1',
                'mst_advent_battle_reward_group_id' => '10',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '10_1_1',
            ],
            [
                'id' => '11_1',
                'mst_advent_battle_reward_group_id' => '11',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '11_1_1',
            ],
            [
                'id' => '12_1',
                'mst_advent_battle_reward_group_id' => '12',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '12_1_1',
            ],
            [
                'id' => '13_1',
                'mst_advent_battle_reward_group_id' => '13',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '13_1_1',
            ],
            [
                'id' => '14_1',
                'mst_advent_battle_reward_group_id' => '14',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '14_1_1',
            ],
            [
                'id' => '15_1',
                'mst_advent_battle_reward_group_id' => '15',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '15_1_1',
            ],
            [
                'id' => '16_1',
                'mst_advent_battle_reward_group_id' => '16',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '16_1_1',
            ],
            [
                'id' => '17_1',
                'mst_advent_battle_reward_group_id' => '17',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '17_1_1',
            ],
            [
                'id' => '18_1',
                'mst_advent_battle_reward_group_id' => '18',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '18_1_1',
            ],
        ]);
        MstAdventBattleRank::factory()->createMany([
            [
                'id' => 'rank_10',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'rank_type' => 'Gold',
                'rank_level' => 2,
                'required_lower_score' => 10000,
            ],
            [
                'id' => 'rank_11',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'rank_type' => 'Gold',
                'rank_level' => 3,
                'required_lower_score' => 11000,
            ],
            [
                'id' => 'rank_12',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'rank_type' => 'Silver',
                'rank_level' => 4,
                'required_lower_score' => 12000,
            ],
            [
                'id' => 'rank_1',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'rank_type' => 'Bronze',
                'rank_level' => 1,
                'required_lower_score' => 1000,
            ],
            [
                'id' => 'rank_2',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'rank_type' => 'Bronze',
                'rank_level' => 2,
                'required_lower_score' => 2000,
            ],
            [
                'id' => 'rank_3',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'rank_type' => 'Bronze',
                'rank_level' => 3,
                'required_lower_score' => 3000,
            ],
            [
                'id' => 'rank_4',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'rank_type' => 'Bronze',
                'rank_level' => 4,
                'required_lower_score' => 4000,
            ],
            [
                'id' => 'rank_5',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'rank_type' => 'Silver',
                'rank_level' => 1,
                'required_lower_score' => 5000,
            ],
            [
                'id' => 'rank_6',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'rank_type' => 'Silver',
                'rank_level' => 2,
                'required_lower_score' => 6000,
            ],
            [
                'id' => 'rank_7',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'rank_type' => 'Silver',
                'rank_level' => 3,
                'required_lower_score' => 7000,
            ],
            [
                'id' => 'rank_8',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'rank_type' => 'Silver',
                'rank_level' => 4,
                'required_lower_score' => 8000,
            ],
            [
                'id' => 'rank_9',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'rank_type' => 'Gold',
                'rank_level' => 1,
                'required_lower_score' => 9000,
            ],
        ]);
    }

    public function test_fetchAvailableRewards_正常実行()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $mstAdventBattleId = 'advent_battle_1';
        $this->createTestData($mstAdventBattleId);

        $usrAdventBattle = UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'total_score' => 2500,
        ]);

        // Exercise
        $adventBattleReceivableReward = $this->service->fetchAvailableRewards($usrAdventBattle);
        $mstAdventBattleRewards = $adventBattleReceivableReward->getMstAdventBattleRewards();

        // Verify
        /** @var MstAdventBattleRewardEntity $actual */
        $this->assertCount(3, $mstAdventBattleRewards);
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

        $this->assertEquals('2', $adventBattleReceivableReward->getLatestMstAdventBattleRewardGroupId());

        // 報酬受け取り済みtotal_scoreが3000を超えて、rank_3の報酬のみ取得できているか確認
        $usrAdventBattle->setReceivedRankRewardGroupId('2');
        $usrAdventBattle->setTotalScore(11000);
        $adventBattleReceivableReward = $this->service->fetchAvailableRewards($usrAdventBattle);
        $mstAdventBattleRewards = $adventBattleReceivableReward->getMstAdventBattleRewards();
        $this->assertCount(9, $mstAdventBattleRewards);
        $actual = $mstAdventBattleRewards->get(0);
        $this->assertEquals('3', $actual->getMstAdventBattleRewardGroupId());
        $this->assertEquals('3_1', $actual->getId());
        $this->assertEquals('3_1_1', $actual->getResourceId());
        $actual = $mstAdventBattleRewards->get(1);
        $this->assertEquals('10', $actual->getMstAdventBattleRewardGroupId());
        $this->assertEquals('10_1', $actual->getId());
        $this->assertEquals('10_1_1', $actual->getResourceId());
        $actual = $mstAdventBattleRewards->get(2);
        $this->assertEquals('11', $actual->getMstAdventBattleRewardGroupId());
        $this->assertEquals('11_1', $actual->getId());
        $this->assertEquals('11_1_1', $actual->getResourceId());
        $actual = $mstAdventBattleRewards->get(3);
        $this->assertEquals('12', $actual->getMstAdventBattleRewardGroupId());
        $this->assertEquals('12_1', $actual->getId());
        $this->assertEquals('12_1_1', $actual->getResourceId());
        $actual = $mstAdventBattleRewards->get(4);
        $this->assertEquals('13', $actual->getMstAdventBattleRewardGroupId());
        $this->assertEquals('13_1', $actual->getId());
        $this->assertEquals('13_1_1', $actual->getResourceId());
        $actual = $mstAdventBattleRewards->get(5);
        $this->assertEquals('14', $actual->getMstAdventBattleRewardGroupId());
        $this->assertEquals('14_1', $actual->getId());
        $this->assertEquals('14_1_1', $actual->getResourceId());
        $actual = $mstAdventBattleRewards->get(6);
        $this->assertEquals('15', $actual->getMstAdventBattleRewardGroupId());
        $this->assertEquals('15_1', $actual->getId());
        $this->assertEquals('15_1_1', $actual->getResourceId());
        $actual = $mstAdventBattleRewards->get(7);
        $this->assertEquals('16', $actual->getMstAdventBattleRewardGroupId());
        $this->assertEquals('16_1', $actual->getId());
        $this->assertEquals('16_1_1', $actual->getResourceId());
        $actual = $mstAdventBattleRewards->get(8);
        $this->assertEquals('17', $actual->getMstAdventBattleRewardGroupId());
        $this->assertEquals('17_1', $actual->getId());
        $this->assertEquals('17_1_1', $actual->getResourceId());

        $this->assertEquals('17', $adventBattleReceivableReward->getLatestMstAdventBattleRewardGroupId());
    }

    public function test_fetchAvailableRewards_受取済み報酬チェック()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $mstAdventBattleId = 'advent_battle_1';
        $this->createTestData($mstAdventBattleId);

        $usrAdventBattle = UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'total_score' => 2500,
            'is_ranking_reward_received' => true,
        ]);

        // Exercise
        $mstAdventBattleRewards = $this->service->fetchAvailableRewards(
            $usrAdventBattle
        )->getMstAdventBattleRewards();

        // Verify
        /** @var MstAdventBattleRewardEntity $actual */
        $this->assertTrue($mstAdventBattleRewards->isEmpty());
    }
}
