<?php

namespace Feature\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Constants\AdventBattleConstant;
use App\Domain\AdventBattle\Entities\AdventBattleRankingItem;
use App\Domain\AdventBattle\Enums\AdventBattleRankType;
use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\AdventBattle\Enums\AdventBattleType;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Services\AdventBattleRankingService;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use App\Domain\Resource\Mst\Models\MstAdventBattleRank;
use App\Domain\Resource\Mst\Models\MstAdventBattleReward;
use App\Domain\Resource\Mst\Models\MstAdventBattleRewardGroup;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserProfile;
use App\Http\Responses\Data\AdventBattleRankingData;
use App\Http\Responses\Data\AdventBattleResultData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdventBattleRankingServiceTest extends TestCase
{
    private AdventBattleRankingService $adventBattleRankingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adventBattleRankingService = app(AdventBattleRankingService::class);
    }

    public function testGetRanking_ランキング情報が取得できる()
    {
        // Setup
        $mstAdventBattleId = MstAdventBattle::factory()->create(['id' => 'test_advent_battle_id'])->toEntity()->getId();
        $now = CarbonImmutable::now();

        $usrUserId = $this->createUsrUser()->getId();
        $usrUserIds = UsrUser::factory(5)->create()->map(fn($usrUser) => $usrUser->getId());
        $usrUserIds->push($usrUserId);
        $usrUserIds->each(function ($userId) use ($mstAdventBattleId) {
            UsrUserProfile::factory()->create(['usr_user_id' => $userId]);
            UsrAdventBattle::factory()->create([
                'usr_user_id' => $userId,
                'mst_advent_battle_id' => $mstAdventBattleId,
            ]);
            $score = fake()->numberBetween(1, 100000);
            $key = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
            Redis::connection()->zadd($key, [$userId => $score]);
        });

        // Exercise
        $actual = $this->adventBattleRankingService->getRanking($usrUserId, $mstAdventBattleId, $now);

        // Verify
        $this->assertInstanceOf(AdventBattleRankingData::class, $actual);
        $actualArray = $actual->formatToResponse();
        $this->assertArrayHasKey('ranking', $actualArray);
        $this->assertArrayHasKey('myRanking', $actualArray);

        // キャッシュが保存されていること
        $cache = Redis::connection()->get(CacheKeyUtil::getAdventBattleRankingCacheKey($mstAdventBattleId));
        $this->assertNotNull($cache);
    }

    public function testGenerateAdventBattleRankingItemDataList_ランキングデータを取得できる()
    {
        // Setup
        $testData = [
            'user1' => ['myId' => 'myId1' ,'rank' => 1, 'name' => 'user1', 'mstUnitId' => 'unit1', 'mstEmblemId' => 'emblem1', 'score' => 1000, 'totalScore' => 1000],
            'user2' => ['myId' => 'myId2' ,'rank' => 2, 'name' => 'user2', 'mstUnitId' => 'unit2', 'mstEmblemId' => 'emblem2', 'score' => 500, 'totalScore' => 500],
            'user3' => ['myId' => 'myId3' ,'rank' => 2, 'name' => 'user3', 'mstUnitId' => 'unit3', 'mstEmblemId' => 'emblem3', 'score' => 500, 'totalScore' => 500],
            'user4' => ['myId' => 'myId4' ,'rank' => 4, 'name' => 'user4', 'mstUnitId' => 'unit4', 'mstEmblemId' => 'emblem4', 'score' => 100, 'totalScore' => 100],
        ];

        $usrUserProfiles = collect();
        $totalScoreMap = collect();
        $usrUserIdScoreMap = [];
        foreach ($testData as $usrUserId => $data) {
            $usrUserProfiles->put(
                $usrUserId,
                UsrUserProfile::factory()->create([
                    'my_id' => $data['myId'],
                    'usr_user_id' => $usrUserId,
                    'name' => $data['name'],
                    'mst_unit_id' => $data['mstUnitId'],
                    'mst_emblem_id' => $data['mstEmblemId'],
                ])
            );
            $usrUserIdScoreMap[$usrUserId] = $data['score'];
            $totalScoreMap->put(
                $usrUserId,
                UsrAdventBattle::factory()->create([
                    'usr_user_id' => $usrUserId,
                    'total_score' => $data['totalScore'],
                ])->total_score
            );
        }

        // Exercise
        $actual = $this->execPrivateMethod(
            $this->adventBattleRankingService,
            'generateAdventBattleRankingItemDataList',
            [$usrUserIdScoreMap, $usrUserProfiles, $totalScoreMap]
        );

        // Verify
        $this->assertCount(4, $actual);
        $actual->each(function ($item) use (&$testData) {
            $this->assertInstanceOf(AdventBattleRankingItem::class, $item);
            $expected = current($testData);
            $this->assertEquals($expected, $item->formatToResponse());
            next($testData);
        });
    }

    public function testGetAdventBattleResultData_降臨バトル結果データが取得できる()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        $mstAdventBattleId = MstAdventBattle::factory()->create([
            'id' => 'advent1',
            'start_at' => $now->subWeeks(2),
            'end_at' => $now->subWeek()
        ])->toEntity()->getId();

        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'total_score' => 1000,
            'max_score' => 1000,
        ]);

        $key = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        Redis::connection()->zadd($key, [$usrUserId => 1000, 'user2' => 500, 'user3' => 500]);
        $totalDamage = 10000;
        $key = CacheKeyUtil::getAdventBattleRaidTotalScoreKey($mstAdventBattleId);
        Redis::connection()->set($key, $totalDamage);

        // Exercise
        $actual = $this->adventBattleRankingService->getAdventBattleResultData($usrUserId, $now);
        $this->saveAll();

        // Verify
        $this->assertInstanceOf(AdventBattleResultData::class, $actual);
        $this->assertEquals(1, $actual->getAdventBattleMyRankingData()->getRank());
        $this->assertEquals(1000, $actual->getAdventBattleMyRankingData()->getScore());
        $this->assertEquals($totalDamage, $actual->getTotalDamage());
    }

    public function testGetAdventBattleResultData_次の降臨バトルが開催されている()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        MstAdventBattle::factory()->createMany([
            ['id' => 'advent1', 'start_at' => $now->subMonth(), 'end_at' => $now->subWeek()],
            ['id' => 'advent2', 'start_at' => $now->subDay(), 'end_at' => $now->addWeek()],
        ]);

        UsrAdventBattle::factory()->create(['usr_user_id' => $usrUserId, 'mst_advent_battle_id' => 'advent1']);

        // Exercise
        $actual = $this->adventBattleRankingService->getAdventBattleResultData($usrUserId, $now);

        // Verify
        $this->assertNull($actual);
    }

    public function testGetAdventBattleResultData_終了している降臨バトルがない()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        // Exercise
        $actual = $this->adventBattleRankingService->getAdventBattleResultData($usrUserId, $now);

        // Verify
        $this->assertNull($actual);
    }

    public function testGetAdventBattleResultData_受取期日を過ぎている()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        MstAdventBattle::factory()->create([
            'id' => 'advent1',
            'start_at' => $now->subDays(AdventBattleConstant::SEASON_REWARD_LIMIT_DAYS + 2),
            'end_at' => $now->subDays(AdventBattleConstant::SEASON_REWARD_LIMIT_DAYS + 1)
        ]);

        // Exercise
        $actual = $this->adventBattleRankingService->getAdventBattleResultData($usrUserId, $now);

        // Verify
        $this->assertNull($actual);
    }

    public function testGetAdventBattleResultData_降臨バトルのタイプがスコアチャレンジでランキングに参加していない()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        MstAdventBattle::factory()->create([
            'id' => 'advent1',
            'advent_battle_type' => AdventBattleType::SCORE_CHALLENGE->value,
            'start_at' => $now->subWeeks(2),
            'end_at' => $now->subWeek()
        ]);

        // Exercise
        $actual = $this->adventBattleRankingService->getAdventBattleResultData($usrUserId, $now);

        // Verify
        $this->assertNull($actual);
    }

    public function testCalcAdventBattleRewards_順位報酬と協力バトル参加ユーザー累計ダメージ報酬のデータが計算できる()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        $mstAdventBattleId = 'advent1';
        $mstAdventBattle = MstAdventBattle::factory()->create([
            'id' => $mstAdventBattleId,
            'advent_battle_type' => AdventBattleType::RAID->value,
            'start_at' => $now->subWeeks(2),
            'end_at' => $now->subWeek()
        ])->toEntity();

        MstAdventBattleRewardGroup::factory()->createMany([
            [
                'id' => 'ranking1',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANKING->value,
                'condition_value' => '1'
            ],
            [
                'id' => 'ranking2',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANKING->value,
                'condition_value' => '300'
            ],
            [
                'id' => 'ranking3',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANKING->value,
                'condition_value' => '500'
            ],
            [
                'id' => 'ranking4',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANKING->value,
                'condition_value' => AdventBattleConstant::RANKING_REWARD_PARTICIPATION
            ],
            [
                'id' => 'raid_total_score1',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RAID_TOTAL_SCORE->value,
                'condition_value' => '10000'
            ],
            [
                'id' => 'raid_total_score2',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RAID_TOTAL_SCORE->value,
                'condition_value' => '5000'
            ],
            [
                'id' => 'raid_total_score3',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RAID_TOTAL_SCORE->value,
                'condition_value' => '1000'
            ],
        ]);

        MstAdventBattleReward::factory()->createMany([
            [
                'mst_advent_battle_reward_group_id' => 'ranking1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 10000,
            ],
            [
                'mst_advent_battle_reward_group_id' => 'ranking2',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 8000,
            ],
            [
                'mst_advent_battle_reward_group_id' => 'ranking3',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 6000,
            ],
            [
                'mst_advent_battle_reward_group_id' => 'ranking4',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
            [
                'mst_advent_battle_reward_group_id' => 'raid_total_score1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 4000,
            ],
            [
                'mst_advent_battle_reward_group_id' => 'raid_total_score2',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 2000,
            ],
            [
                'mst_advent_battle_reward_group_id' => 'raid_total_score3',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 1000,
            ],
        ]);

        $score = 75000;
        $usrAdventBattle = UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'total_score' => $score,
            'max_score' => $score,
        ]);

        // ランキングデータの登録
        $key = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        $dictionary = [];
        for ($i = 1; $i <= 1000; $i++) {
            $dictionary["user{$i}"] = $i * 100;
        }
        // 251位設定
        $dictionary[$usrUserId] = $score;
        Redis::connection()->zadd($key, $dictionary);

        // 協力バトル累積ダメージの登録
        $key = CacheKeyUtil::getAdventBattleRaidTotalScoreKey($mstAdventBattleId);
        Redis::connection()->set($key, 7500);

        // Exercise
        $actual = $this->adventBattleRankingService->calcAdventBattleRewards($mstAdventBattle, $usrAdventBattle, 251);
        $this->saveAll();

        // Verify
        $this->assertNotEmpty($actual);

        $actual = $actual->groupBy(fn($item) => $item->getAdventBattleRewardCategory());

        $this->assertEquals(1, $actual->get(AdventBattleRewardCategory::RANKING->value)->count());
        $this->assertEquals(8000, $actual->get(AdventBattleRewardCategory::RANKING->value)->first()->getAmount());
        $this->assertEquals(1000, $actual->get(AdventBattleRewardCategory::RAID_TOTAL_SCORE->value)->get(0)->getAmount());
        $this->assertEquals(2000, $actual->get(AdventBattleRewardCategory::RAID_TOTAL_SCORE->value)->get(1)->getAmount());
    }

    public function testCalcAdventBattleRewards_ランキング参加報酬と協力バトル参加ユーザー累計ダメージ報酬のデータが計算できる()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        $mstAdventBattleId = 'advent1';
        $mstAdventBattle = MstAdventBattle::factory()->create([
            'id' => $mstAdventBattleId,
            'advent_battle_type' => AdventBattleType::RAID->value,
            'start_at' => $now->subWeeks(2),
            'end_at' => $now->subWeek()
        ])->toEntity();

        MstAdventBattleRewardGroup::factory()->createMany([
            [
                'id' => 'ranking1',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANKING->value,
                'condition_value' => '1'
            ],
            [
                'id' => 'ranking2',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANKING->value,
                'condition_value' => '300'
            ],
            [
                'id' => 'ranking3',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANKING->value,
                'condition_value' => '500'
            ],
            [
                'id' => 'ranking4',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RANKING->value,
                'condition_value' => AdventBattleConstant::RANKING_REWARD_PARTICIPATION
            ],
            [
                'id' => 'raid_total_score1',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RAID_TOTAL_SCORE->value,
                'condition_value' => '10000'
            ],
            [
                'id' => 'raid_total_score2',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RAID_TOTAL_SCORE->value,
                'condition_value' => '5000'
            ],
            [
                'id' => 'raid_total_score3',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::RAID_TOTAL_SCORE->value,
                'condition_value' => '1000'
            ],
        ]);

        MstAdventBattleReward::factory()->createMany([
            [
                'mst_advent_battle_reward_group_id' => 'ranking1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 10000,
            ],
            [
                'mst_advent_battle_reward_group_id' => 'ranking2',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 8000,
            ],
            [
                'mst_advent_battle_reward_group_id' => 'ranking3',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 6000,
            ],
            [
                'mst_advent_battle_reward_group_id' => 'ranking4',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
            [
                'mst_advent_battle_reward_group_id' => 'raid_total_score1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 4000,
            ],
            [
                'mst_advent_battle_reward_group_id' => 'raid_total_score2',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 2000,
            ],
            [
                'mst_advent_battle_reward_group_id' => 'raid_total_score3',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 1000,
            ],
        ]);

        $score = 50000;
        $usrAdventBattle = UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'total_score' => $score,
            'max_score' => $score,
        ]);

        // ランキングデータの登録
        $key = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        $dictionary = [];
        for ($i = 1; $i <= 1000; $i++) {
            $dictionary["user{$i}"] = $i * 100;
        }
        // 501位設定
        $dictionary[$usrUserId] = $score;
        Redis::connection()->zadd($key, $dictionary);

        // 協力バトル累積ダメージの登録
        $key = CacheKeyUtil::getAdventBattleRaidTotalScoreKey($mstAdventBattleId);
        Redis::connection()->set($key, 7500);

        // Exercise
        $actual = $this->adventBattleRankingService->calcAdventBattleRewards($mstAdventBattle, $usrAdventBattle, 501);
        $this->saveAll();

        // Verify
        $this->assertNotEmpty($actual);

        $actual = $actual->groupBy(fn($item) => $item->getAdventBattleRewardCategory());

        $this->assertEquals(1, $actual->get(AdventBattleRewardCategory::RANKING->value)->count());
        $this->assertEquals(100, $actual->get(AdventBattleRewardCategory::RANKING->value)->first()->getAmount());
        $this->assertEquals(1000, $actual->get(AdventBattleRewardCategory::RAID_TOTAL_SCORE->value)->get(0)->getAmount());
        $this->assertEquals(2000, $actual->get(AdventBattleRewardCategory::RAID_TOTAL_SCORE->value)->get(1)->getAmount());
    }

    public function testCalcAdventBattleRewards_未受取のハイスコア報酬のデータが計算できる()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        $mstAdventBattleId = 'advent1';
        $mstAdventBattle = MstAdventBattle::factory()->create([
            'id' => $mstAdventBattleId,
            'advent_battle_type' => AdventBattleType::RAID->value,
            'start_at' => $now->subWeeks(2),
            'end_at' => $now->subWeek()
        ])->toEntity();

        MstAdventBattleRewardGroup::factory()->createMany([
            [
                'id' => '1',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::MAX_SCORE->value,
                'condition_value' => 10,
            ],
            [
                'id' => '2',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::MAX_SCORE->value,
                'condition_value' => 20,
            ],
            [
                'id' => '3',
                'mst_advent_battle_id' => $mstAdventBattleId,
                'reward_category' => AdventBattleRewardCategory::MAX_SCORE->value,
                'condition_value' => 30,
            ],
        ]);
        
        MstAdventBattleReward::factory()->createMany([
            [
                'id' => '1_1',
                'mst_advent_battle_reward_group_id' => '1',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '1_1_1',
                'resource_amount' => 1,
            ],
            [
                'id' => '2_1',
                'mst_advent_battle_reward_group_id' => '2',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '2_1_1',
                'resource_amount' => 2,
            ],
            [
                'id' => '3_1',
                'mst_advent_battle_reward_group_id' => '3',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => '3_1_1',
                'resource_amount' => 3,
            ],
        ]);

        $score = 50000;
        $usrAdventBattle = UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'total_score' => $score,
            'max_score' => $score,
            'max_received_max_score_reward' => 15,
        ]);

        // Exercise
        $actual = $this->adventBattleRankingService->calcAdventBattleRewards($mstAdventBattle, $usrAdventBattle, 501);
        $this->saveAll();

        // Verify
        $this->assertNotEmpty($actual);

        $actual = $actual->groupBy(fn($item) => $item->getAdventBattleRewardCategory());

        $this->assertEquals(2, $actual->get(AdventBattleRewardCategory::MAX_SCORE->value)->count());
        $this->assertEquals(2, $actual->get(AdventBattleRewardCategory::MAX_SCORE->value)->get(0)->getAmount());
        $this->assertEquals(3, $actual->get(AdventBattleRewardCategory::MAX_SCORE->value)->get(1)->getAmount());
    }

    public function testGetReceivableRewards_受け取り可能な報酬を取得できる()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        $mstAdventBattleId1 = 'advent1';
        $mstAdventBattleId2 = 'advent2';
        MstAdventBattle::factory()->createMany([
            [
                'id' => $mstAdventBattleId1,
                'advent_battle_type' => AdventBattleType::SCORE_CHALLENGE->value,
                'start_at' => $now->subWeeks(3),
                'end_at' => $now->subWeeks(2)
            ],
            [
                'id' => $mstAdventBattleId2,
                'advent_battle_type' => AdventBattleType::RAID->value,
                'start_at' => $now->subWeeks(2),
                'end_at' => $now->subWeek()
            ]
        ]);

        MstAdventBattleRewardGroup::factory()->createMany([
            [
                'id' => 'rank1',
                'mst_advent_battle_id' => $mstAdventBattleId1,
                'reward_category' => AdventBattleRewardCategory::RANK->value,
                'condition_value' => 'master1'
            ],
            [
                'id' => 'rank2',
                'mst_advent_battle_id' => $mstAdventBattleId2,
                'reward_category' => AdventBattleRewardCategory::RANKING->value,
                'condition_value' => '1'
            ],
            [
                'id' => 'rank3',
                'mst_advent_battle_id' => $mstAdventBattleId2,
                'reward_category' => AdventBattleRewardCategory::RAID_TOTAL_SCORE->value,
                'condition_value' => '1'
            ],
        ]);

        $mstAdventBattleRewards = MstAdventBattleReward::factory()->createMany([
            [
                'mst_advent_battle_reward_group_id' => 'rank1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 10000,
            ],
            [
                'mst_advent_battle_reward_group_id' => 'rank2',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 5000,
            ],
            [
                'mst_advent_battle_reward_group_id' => 'rank3',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 1000,
            ],
        ]);

        MstAdventBattleRank::factory()->createMany([
            [
                'id' => 'master1',
                'mst_advent_battle_id' => $mstAdventBattleId1,
                'rank_type' => AdventBattleRankType::MASTER->value,
                'rank_level' => 1,
                'required_lower_score' => 1000,
            ]
        ]);

        $score = 1;
        UsrAdventBattle::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_advent_battle_id' => $mstAdventBattleId1, 'total_score' => 1000, 'max_score' => $score],
            ['usr_user_id' => $usrUserId, 'mst_advent_battle_id' => $mstAdventBattleId2, 'total_score' => 1000, 'max_score' => $score],
        ]);

        // ランク報酬となるよう自身が201位になるランキングデータの登録
        $key = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId1);
        $members = [$usrUserId => $score];
        foreach (range(1, 200) as $i) {
            $members["user{$i}"] = mt_rand(2, 1000);
        }
        Redis::connection()->zadd($key, $members);

        $key = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId2);
        Redis::connection()->zadd($key, [$usrUserId => 1]);

        // Exercise
        $actual = $this->adventBattleRankingService->getReceivableRewards($usrUserId, $now);
        $this->saveAll();

        // Verify
        $this->assertNotEmpty($actual);

        $amounts = $mstAdventBattleRewards
            ->map(fn ($mstAdventBattleReward) => $mstAdventBattleReward->toEntity()->getResourceAmount())
            ->toArray();
        foreach ($actual as $adventBattleReward) {
            $this->assertContains($adventBattleReward->getAmount(), $amounts);
            $this->assertEquals(RewardType::COIN->value, $adventBattleReward->getType());
        }

        UsrAdventBattle::query()->where('usr_user_id', $usrUserId)->get()->each(function ($usrAdventBattle) {
            $this->assertTrue($usrAdventBattle->isRankingRewardReceived());
        });
    }

    public function testGetReceivableRewards_受け取り可能な報酬がない場合()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        MstAdventBattle::factory()->create([
            'advent_battle_type' => AdventBattleType::SCORE_CHALLENGE->value,
            'start_at' => $now->subMonths(3),
            'end_at' => $now->subMonths(2)
        ]);

        // Exercise
        $actual = $this->adventBattleRankingService->getReceivableRewards($usrUserId, $now);

        // Verify
        $this->assertEmpty($actual);
    }

    public static function params_validateAdventBattleActiveOrFinished(): array
    {
        return [
            '開催中の降臨バトル' => [
                'mstAdventBattleId' => 'advent2',
                'now' => CarbonImmutable::parse('2024-12-11 00:00:00'),
                'errorCode' => null,
            ],
            '終了した降臨バトル' => [
                'mstAdventBattleId' => 'advent1',
                'now' => CarbonImmutable::parse('2024-12-11 00:00:00'),
                'errorCode' => null,
            ],
            '開催が未来の降臨バトル' => [
                'mstAdventBattleId' => 'advent2',
                'now' => CarbonImmutable::parse('2024-12-02 00:00:00'),
                'errorCode' => ErrorCode::ADVENT_BATTLE_RANKING_OUT_PERIOD,
            ],
        ];
    }

    #[DataProvider('params_validateAdventBattleActiveOrFinished')]
    public function testValidateAdventBattleActiveOrFinished(
        string $mstAdventBattleId,
        CarbonImmutable $now,
        ?int $errorCode
    ) {
        // Setup
        MstAdventBattle::factory()->createMany([
            ['id' => 'advent1', 'start_at' => '2024-12-01 00:00:00', 'end_at' => '2024-12-05 00:00:00'],
            ['id' => 'advent2', 'start_at' => '2024-12-10 00:00:00', 'end_at' => '2024-12-15 00:00:00'],
        ]);

        // Exercise
        if (!is_null($errorCode)) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }

        $this->execPrivateMethod(
            $this->adventBattleRankingService,
            'validateAdventBattleActiveOrFinished',
            [$mstAdventBattleId, $now]
        );

        // Verify
        $this->assertTrue(true);
    }
}
