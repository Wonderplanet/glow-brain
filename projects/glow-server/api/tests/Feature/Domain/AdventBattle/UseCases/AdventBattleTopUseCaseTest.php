<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\AdventBattle\UseCases;

use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\AdventBattle\Enums\AdventBattleType;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\UseCases\AdventBattleTopUseCase;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use App\Domain\Resource\Mst\Models\MstAdventBattleReward;
use App\Domain\Resource\Mst\Models\MstAdventBattleRewardGroup;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Reward\Managers\RewardManager;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use App\Http\Responses\ResultData\AdventBattleTopResultData;
use Illuminate\Support\Facades\Redis;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

class AdventBattleTopUseCaseTest extends TestCase
{
    private AdventBattleTopUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(AdventBattleTopUseCase::class);
    }

    private function createMstTestData(string $mstAdventBattleId): void
    {
        MstAdventBattle::factory()->create([
            'id' => $mstAdventBattleId,
            'advent_battle_type' => AdventBattleType::RAID->value,
            'start_at' => now()->subDay()->toDateTimeString(),
            'end_at' => now()->addDay()->toDateTimeString(),
        ]);
        MstAdventBattleRewardGroup::factory()->createMany([
            [
                'id' => '3',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RAID_TOTAL_SCORE->value,
                'condition_value' => '3000',
            ],
            [
                'id' => '1',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RAID_TOTAL_SCORE->value,
                'condition_value' => '1000',
            ],
            [
                'id' => '2',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RAID_TOTAL_SCORE->value,
                'condition_value' => '2000',
            ],
            [
                'id' => '4',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::MAX_SCORE->value,
                'condition_value' => '10',
            ],
            [
                'id' => '5',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::MAX_SCORE->value,
                'condition_value' => '20',
            ],
            [
                'id' => '6',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::MAX_SCORE->value,
                'condition_value' => '30',
            ],
        ]);
        MstAdventBattleReward::factory()->createMany([
            [
                'id' => '1_1',
                'mst_advent_battle_reward_group_id' => '1',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '1_1_1',
                'resource_amount' => 5,
            ],
            [
                'id' => '2_1',
                'mst_advent_battle_reward_group_id' => '2',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '2_1_1',
                'resource_amount' => 10,
            ],
            [
                'id' => '2_2',
                'mst_advent_battle_reward_group_id' => '2',
                'resource_type' => RewardType::EMBLEM->value,
                'resource_id' => '2_2_1',
                'resource_amount' => 1,
            ],
            [
                'id' => '2_3',
                'mst_advent_battle_reward_group_id' => '2',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => '2_3_1',
                'resource_amount' => 15,
            ],
            [
                'id' => '2_4',
                'mst_advent_battle_reward_group_id' => '2',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => '2_4_1',
                'resource_amount' => 30,
            ],
            [
                'id' => '3_1',
                'mst_advent_battle_reward_group_id' => '3',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '3_1_1',
                'resource_amount' => 20,
            ],


            [
                'id' => '4_1',
                'mst_advent_battle_reward_group_id' => '4',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '4_1_1',
                'resource_amount' => 5,
            ],
            [
                'id' => '5_1',
                'mst_advent_battle_reward_group_id' => '5',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '5_1_1',
                'resource_amount' => 10,
            ],
            [
                'id' => '5_2',
                'mst_advent_battle_reward_group_id' => '5',
                'resource_type' => RewardType::EMBLEM->value,
                'resource_id' => '5_2_1',
                'resource_amount' => 1,
            ],
            [
                'id' => '5_3',
                'mst_advent_battle_reward_group_id' => '5',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => '5_3_1',
                'resource_amount' => 15,
            ],
            [
                'id' => '5_4',
                'mst_advent_battle_reward_group_id' => '5',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => '5_4_1',
                'resource_amount' => 30,
            ],
            [
                'id' => '6_1',
                'mst_advent_battle_reward_group_id' => '6',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '6_1_1',
                'resource_amount' => 20,
            ],
        ]);
        MstItem::factory()->createMany([
            [
                'id' => '1_1_1',
                'type' => ItemType::GACHA_TICKET->value,
            ],
            [
                'id' => '2_1_1',
                'type' => ItemType::CHARACTER_FRAGMENT->value,
            ],
            [
                'id' => '3_1_1',
                'type' => ItemType::IDLE_COIN_BOX->value,
            ],
            [
                'id' => '4_1_1',
                'type' => ItemType::GACHA_TICKET->value,
            ],
            [
                'id' => '5_1_1',
                'type' => ItemType::CHARACTER_FRAGMENT->value,
            ],
            [
                'id' => '6_1_1',
                'type' => ItemType::IDLE_COIN_BOX->value,
            ],
        ]);
        MstEmblem::factory()->createMany([
            [
                'id' => '2_2_1',
            ],
            [
                'id' => '5_2_1',
            ],
        ]);
    }

    public function test_exec_正常実行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $now = $this->fixTime();
        $mstAdventBattleId = 'advent_battle_1';
        $this->createMstTestData($mstAdventBattleId);
        $platform = UserConstant::PLATFORM_IOS;
        $maxScore = 25;

        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_advent_battle_id' => $mstAdventBattleId,
            'max_score' => $maxScore,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);

        $allUserTotalScore = 2000;
        $key = CacheKeyUtil::getAdventBattleRaidTotalScoreKey($mstAdventBattleId);
        Redis::connection()->set($key, $allUserTotalScore);

        // Exercise
        /** @var AdventBattleTopResultData $response */
        $response = $this->useCase->exec($currentUser, $mstAdventBattleId, $platform);

        $raidTotalScoreRewards = $response->sentRaidTotalScoreRewards;
        $this->assertEquals(5, $raidTotalScoreRewards->count());
        $expectedRewards = collect([
            ['groupId' => '1', 'rewardId' => '1_1'],
            ['groupId' => '2', 'rewardId' => '2_1'],
            ['groupId' => '2', 'rewardId' => '2_2'],
            ['groupId' => '2', 'rewardId' => '2_3'],
            ['groupId' => '2', 'rewardId' => '2_4'],
        ]);

        $expectedRewards->each(function ($expected, $index) use ($raidTotalScoreRewards, $mstAdventBattleId) {
            /** @var \App\Domain\Resource\Entities\Rewards\AdventBattleReward $actual */
            $actual = $raidTotalScoreRewards->get($index);
            $this->assertEquals($mstAdventBattleId, $actual->getMstAdventBattleId());
            $this->assertEquals($expected['groupId'], $actual->getMstAdventBattleRewardGroupId());
            $this->assertEquals($expected['rewardId'], $actual->getMstAdventBattleRewardId());
        });

        $msxScoreRewards = $response->sentMaxScoreRewards;
        $this->assertEquals(5, $msxScoreRewards->count());
        $expectedRewards = collect([
            ['groupId' => '4', 'rewardId' => '4_1'],
            ['groupId' => '5', 'rewardId' => '5_1'],
            ['groupId' => '5', 'rewardId' => '5_2'],
            ['groupId' => '5', 'rewardId' => '5_3'],
            ['groupId' => '5', 'rewardId' => '5_4'],
        ]);

        $expectedRewards->each(function ($expected, $index) use ($msxScoreRewards, $mstAdventBattleId) {
            /** @var \App\Domain\Resource\Entities\Rewards\AdventBattleReward $actual */
            $actual = $msxScoreRewards->get($index);
            $this->assertEquals($mstAdventBattleId, $actual->getMstAdventBattleId());
            $this->assertEquals($expected['groupId'], $actual->getMstAdventBattleRewardGroupId());
            $this->assertEquals($expected['rewardId'], $actual->getMstAdventBattleRewardId());
        });

        $this->assertEquals(30, $response->usrParameterData->getCoin());
        $this->assertEquals(60, $response->usrParameterData->getFreeDiamond());

        $rewardUsrItems = $response->usrItems->values();
        $this->assertCount(4, $rewardUsrItems);
        $this->assertEquals('1_1_1', $rewardUsrItems->get(0)->getMstItemId());
        $this->assertEquals('2_1_1', $rewardUsrItems->get(1)->getMstItemId());
        $this->assertEquals('4_1_1', $rewardUsrItems->get(2)->getMstItemId());
        $this->assertEquals('5_1_1', $rewardUsrItems->get(3)->getMstItemId());

        $rewardUsrEmblems = $response->usrEmblems->values();
        $this->assertCount(2, $rewardUsrEmblems);
        $this->assertEquals('2_2_1', $rewardUsrEmblems->get(0)->getMstEmblemId());
        $this->assertEquals('5_2_1', $rewardUsrEmblems->get(1)->getMstEmblemId());

        /** @var \Illuminate\Support\Collection<\App\Domain\Item\Models\Eloquent\UsrItem> $usrItems */
        $usrItems = UsrItem::where('usr_user_id', $usrUser->getId())->get()->keyBy(function ($usrItem) {
            return $usrItem->getMstItemId();
        });
        $this->assertCount(4, $usrItems);
        $this->assertEquals(5, $usrItems->get('1_1_1')->getAmount());
        $this->assertEquals(10, $usrItems->get('2_1_1')->getAmount());
        $this->assertEquals(5, $usrItems->get('4_1_1')->getAmount());
        $this->assertEquals(10, $usrItems->get('5_1_1')->getAmount());

        /** @var \Illuminate\Support\Collection<\App\Domain\Emblem\Models\UsrEmblem> $usrEmblems */
        $usrEmblems = UsrEmblem::where('usr_user_id', $usrUser->getId())->get()->keyBy(function ($usrEmblem) {
            return $usrEmblem->getMstEmblemId();
        });
        $this->assertCount(2, $usrEmblems);
        $this->assertEquals('2_2_1', $usrEmblems->get('2_2_1')->getMstEmblemId());
        $this->assertEquals('5_2_1', $usrEmblems->get('5_2_1')->getMstEmblemId());

        /** @var \App\Domain\AdventBattle\Models\UsrAdventBattle $usrAdventBattle */
        $usrAdventBattle = UsrAdventBattle::where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals('2', $usrAdventBattle->getReceivedRaidRewardGroupId());
    }

    public function test_exec_報酬受け取りまでスコアが足りない()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $now = $this->fixTime();
        $mstAdventBattleId = 'advent_battle_1';
        $this->createMstTestData($mstAdventBattleId);
        $platform = UserConstant::PLATFORM_IOS;

        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_advent_battle_id' => $mstAdventBattleId,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);

        $allUserTotalScore = 999;
        $key = CacheKeyUtil::getAdventBattleRaidTotalScoreKey($mstAdventBattleId);
        Redis::connection()->set($key, $allUserTotalScore);

        // Exercise
        /** @var AdventBattleTopResultData $response */
        $response = $this->useCase->exec($currentUser, $mstAdventBattleId, $platform);

        // 受け取った報酬なし
        $this->assertEmpty($response->sentRaidTotalScoreRewards);

        /** @var \App\Domain\AdventBattle\Models\UsrAdventBattle $usrAdventBattle */
        $usrAdventBattle = UsrAdventBattle::where('usr_user_id', $usrUser->getId())->first();
        $this->assertNull($usrAdventBattle->getReceivedRaidRewardGroupId());
    }
}
