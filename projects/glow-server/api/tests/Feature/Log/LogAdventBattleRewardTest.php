<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\AdventBattle\Enums\AdventBattleClearRewardCategory;
use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\AdventBattle\Enums\AdventBattleSessionStatus;
use App\Domain\AdventBattle\Enums\AdventBattleType;
use App\Domain\AdventBattle\Models\LogAdventBattleReward;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Models\UsrAdventBattleSession;
use App\Domain\AdventBattle\UseCases\AdventBattleEndUseCase;
use App\Domain\AdventBattle\UseCases\AdventBattleTopUseCase;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use App\Domain\Resource\Mst\Models\MstAdventBattleClearReward;
use App\Domain\Resource\Mst\Models\MstAdventBattleReward;
use App\Domain\Resource\Mst\Models\MstAdventBattleRewardGroup;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\User\Models\UsrUserParameter;
use Illuminate\Support\Facades\Redis;
use Tests\Support\Entities\CurrentUser;
use Tests\Support\Traits\TestLogTrait;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

class LogAdventBattleRewardTest extends BaseControllerTestCase
{
    use TestLogTrait;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_AdventBattleEndUseCase_exec_獲得した報酬ログが保存できている()
    {
        // 必要なマスタデータ作成
        $now = $this->fixTime();
        $mstAdventBattleId = 'advent_battle1';
        $mstItemId = 'item1';
        MstAdventBattle::factory()->create([
            'id' => $mstAdventBattleId,
            'advent_battle_type' => AdventBattleType::SCORE_CHALLENGE->value,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
            'exp' => 10,
            'coin' => 100,
        ]);

        MstAdventBattleClearReward::factory()->createMany([
            [
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleClearRewardCategory::FIRST_CLEAR->value,
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_amount' => 100,
            ],
            [
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleClearRewardCategory::ALWAYS->value,
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => $mstItemId,
                'resource_amount' => 1,
            ]
        ]);

        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 100],
        ]);

        MstItem::factory()->create(['id' => $mstItemId]);

        // ユーザデータ作成
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUserId]);
        $this->createDiamond($usrUserId, 1000);

        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'clear_count' => 0,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);
        UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'is_valid' => AdventBattleSessionStatus::STARTED->value,
            'party_no' => 1,
            'battle_start_at' => $now->subSeconds(200)->toDateTimeString(),
        ]);

        $useCase = $this->app->make(AdventBattleEndUseCase::class);
        $useCase->exec(
            new CurrentUser($usrUserId),
            $mstAdventBattleId,
            1,
            []
        );

        // log_advent_battle_rewardsテーブルに1件記録されていること
        $this->assertDatabaseCount('log_advent_battle_rewards', 1);
        $log = LogAdventBattleReward::query()->first();
        $receivedRewards = json_decode($log->received_reward, true);
        // exp(drop), coin(drop), first clear, always
        $this->assertCount(4, $receivedRewards);
        foreach ($receivedRewards as $row) {
            $rewardCategory = $row['rewardCategory'];
            $reward = $row['reward'];
            if ($rewardCategory === AdventBattleClearRewardCategory::FIRST_CLEAR->value) {
                $this->assertEquals(RewardType::FREE_DIAMOND->value, $reward['resourceType']);
                $this->assertEquals(100, $reward['resourceAmount']);
            } elseif ($rewardCategory === AdventBattleClearRewardCategory::ALWAYS->value) {
                $this->assertEquals(RewardType::ITEM->value, $reward['resourceType']);
                $this->assertEquals('item1', $reward['resourceId']);
                $this->assertEquals(1, $reward['resourceAmount']);
            } elseif ($rewardCategory === AdventBattleClearRewardCategory::DROP->value) {
                if ($reward['resourceType'] === RewardType::EXP->value) {
                    $this->assertEquals(10, $reward['resourceAmount']);
                } elseif ($reward['resourceType'] === RewardType::COIN->value) {
                    $this->assertEquals(100, $reward['resourceAmount']);
                }
            } else {
                $this->fail("Unexpected reward category: {$rewardCategory}");
            }
        }
    }


    public function test_AdventBattleTopUseCase_exec_獲得した報酬ログが保存できている()
    {
        // 必要なマスタデータ作成
        $now = $this->fixTime();
        $mstAdventBattleId = 'advent_battle1';
        MstAdventBattle::factory()->create([
            'id' => $mstAdventBattleId,
            'advent_battle_type' => AdventBattleType::RAID->value,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString()
        ]);

        MstAdventBattleRewardGroup::factory()->createMany([
            [
                'id' => 'max_score_group',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::MAX_SCORE->value,
                'condition_value' => '100',
            ],
            [
                'id' => 'raid_total_score_group',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RAID_TOTAL_SCORE->value,
                'condition_value' => '100',
            ],
        ]);
        MstAdventBattleReward::factory()->createMany([
            [
                'mst_advent_battle_reward_group_id' => 'max_score_group',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_amount' => 10,
            ],
            [
                'mst_advent_battle_reward_group_id' => 'raid_total_score_group',
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 100,
            ]
        ]);

        // ユーザデータ作成
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUserId]);
        $this->createDiamond($usrUserId, 1000);

        $cacheKey = CacheKeyUtil::getAdventBattleRaidTotalScoreKey($mstAdventBattleId);
        Redis::connection()->set($cacheKey, 1000);

        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'clear_count' => 0,
            'max_score' => 100,
            'max_received_max_score_reward' => 0,
            'is_ranking_reward_received' => 0,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);

        $useCase = $this->app->make(AdventBattleTopUseCase::class);
        $useCase->exec(
            new CurrentUser($usrUserId),
            $mstAdventBattleId,
            1
        );

        // log_advent_battle_rewardsテーブルに1件記録されていること
        $this->assertDatabaseCount('log_advent_battle_rewards', 1);
        $log = LogAdventBattleReward::query()->first();
        $receivedRewards = json_decode($log->received_reward, true);
        // max_score, raid_total_score
        $this->assertCount(2, $receivedRewards);
        foreach ($receivedRewards as $row) {
            $rewardCategory = $row['rewardCategory'];
            $reward = $row['reward'];
            if ($rewardCategory === AdventBattleRewardCategory::MAX_SCORE->value) {
                $this->assertEquals(RewardType::FREE_DIAMOND->value, $reward['resourceType']);
                $this->assertEquals(10, $reward['resourceAmount']);
            } elseif ($rewardCategory === AdventBattleRewardCategory::RAID_TOTAL_SCORE->value) {
                $this->assertEquals(RewardType::COIN->value, $reward['resourceType']);
                $this->assertEquals(100, $reward['resourceAmount']);
            } else {
                $this->fail("Unexpected reward category: {$rewardCategory}");
            }
        }
    }
}
