<?php

namespace Feature\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Constants\AdventBattleConstant;
use App\Domain\AdventBattle\Entities\AdventBattleRankingItem;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Services\AdventBattleCacheService;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Http\Responses\Data\AdventBattleMyRankingData;
use Database\Factories\UsrAdventBattleFactory;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdventBattleCacheServiceTest extends TestCase
{
    private AdventBattleCacheService $adventBattleCacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adventBattleCacheService = app(AdventBattleCacheService::class);
    }

    public function testGetAdventBattleRankingCache_キャッシュデータが取得できる()
    {
        // Setup
        $mstAdventBattleId = 'advent1';
        $key = CacheKeyUtil::getAdventBattleRankingCacheKey($mstAdventBattleId);
        $value = collect([
            new AdventBattleRankingItem('myId1', 1, 'user1', 'mst_unit_id', 'mst_emblem_id', 100, 1500),
            new AdventBattleRankingItem('myId2', 2, 'user2', 'mst_unit_id', 'mst_emblem_id', 200, 3000),
        ]);
        Redis::connection()->set($key, serialize($value));

        // Exercise
        $actual = $this->adventBattleCacheService->getAdventBattleRankingCache($mstAdventBattleId);

        // Verify
        $this->assertCount(2, $actual);
        foreach ($actual as $item) {
            $this->assertInstanceOf(AdventBattleRankingItem::class, $item);
        }
    }

    public function testSetAdventBattleRankingCache_キャッシュデータが設定される()
    {
        // Setup
        $mstAdventBattleId = 'advent1';
        $value = collect([
            new AdventBattleRankingItem('myId1', 1, 'user1', 'mst_unit_id', 'mst_emblem_id', 100, 1500),
            new AdventBattleRankingItem('myId2', 2, 'user2', 'mst_unit_id', 'mst_emblem_id', 200, 3000),
        ]);

        // Exercise
        $this->adventBattleCacheService->setAdventBattleRankingCache($mstAdventBattleId, $value, 100);

        // Verify
        $key = CacheKeyUtil::getAdventBattleRankingCacheKey($mstAdventBattleId);
        $actual = Redis::connection()->get($key);
        $this->assertNotNull($actual);
    }

    public function testAddRankingScore_ランキングにスコアが追加される()
    {
        // Setup
        $usrUserId = 'user1';
        $mstAdventBattleId = 'advent1';
        $score = 100;

        // Exercise
        $this->adventBattleCacheService->addRankingScore($mstAdventBattleId, $usrUserId, $score);

        // Verify
        $key = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        $actual = Redis::connection()->zscore($key, $usrUserId);
        $this->assertNotFalse($actual);
        $this->assertEquals($score, (int)$actual);
    }

    public function testIncrementRankingScore_ランキングのスコアに加算される()
    {
        // Setup
        $usrUserId = 'user1';
        $mstAdventBattleId = 'advent1';
        $score = 100;

        $key = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        Redis::connection()->zadd($key, [$usrUserId => 50]);

        // Exercise
        $this->adventBattleCacheService->incrementRankingScore($mstAdventBattleId, $usrUserId, $score);

        // Verify
        $actual = Redis::connection()->zscore($key, $usrUserId);
        $this->assertNotFalse($actual);
        $this->assertEquals($score + 50, (int)$actual);
    }

    public static function params_testGetTopRankedPlayerScoreMap_上位100までのスコアを取得()
    {
        return [
            '有効ユーザーが100未満' => [
                'normalUserCount' => 10,
                'cheaterUserCount' => 10,
                'score' => null,
                'expected' => 10,
            ],
            '有効ユーザーが100以上' => [
                'normalUserCount' => 110,
                'cheaterUserCount' => 10,
                'score' => null,
                'expected' => 100,
            ],
            '同率スコアで100人を超える' => [
                'normalUserCount' => AdventBattleConstant::RANKING_DISPLAY_LIMIT + AdventBattleConstant::RANKING_FETCH_BUFFER + 10,
                'cheaterUserCount' => 10,
                'score' => 1000,
                'expected' => AdventBattleConstant::RANKING_DISPLAY_LIMIT + AdventBattleConstant::RANKING_FETCH_BUFFER,
            ],
        ];
    }

    /**
     * @dataProvider params_testGetTopRankedPlayerScoreMap_上位100までのスコアを取得
     */
    public function testGetTopRankedPlayerScoreMap_上位100までのスコアを取得(
        int $normalUserCount,
        int $cheaterUserCount,
        ?int $score,
        int $expected
    ) {
        // Setup
        $mstAdventBattleId = 'advent1';

        $key = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        $members = [];
        // 正規ユーザー登録
        foreach (range(1, $normalUserCount) as $i) {
            $members["user$i"] = $score ?? fake()->numberBetween(1, 100000);
        }
        // チートユーザー登録
        foreach (range($normalUserCount + 1, $normalUserCount + $cheaterUserCount) as $i) {
            $members["user$i"] = AdventBattleConstant::RANKING_CHEATER_SCORE;
        }
        Redis::connection()->zadd($key, $members);

        // Exercise
        $actual = $this->adventBattleCacheService->getTopRankedPlayerScoreMap($mstAdventBattleId);

        // Verify
        $this->assertCount($expected, $actual);
    }

    public static function params_testGenerateAdventBattleMyRankingData_自身のランキングデータオブジェクトを取得できる()
    {
        return [
            'ランキング除外されていない' => [
                'isExcludeRanking' => false,
            ],
            'ランキング除外されている' => [
                'isExcludeRanking' => true,
            ],
        ];
    }

    #[DataProvider('params_testGenerateAdventBattleMyRankingData_自身のランキングデータオブジェクトを取得できる')]
    public function testGenerateAdventBattleMyRankingData_自身のランキングデータオブジェクトを取得できる(bool $isExcludeRanking)
    {
        // Setup
        $usrUserId = 'user1';
        $userScore = 200;
        $mstAdventBattleId = 'advent1';
        $key = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        $members = [
            'user2' => 300,
            $usrUserId => 200,
            'user3' => 100,
        ];
        Redis::connection()->zadd($key, $members);

        $usrAdventBattle = UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'max_score' => $userScore,
            'total_score' => $userScore,
            'is_excluded_ranking' => $isExcludeRanking,
        ]);

        // Exercise
        /** @var AdventBattleMyRankingData $actual */
        $actual = $this->adventBattleCacheService->generateAdventBattleMyRankingData($usrAdventBattle);

        // Verify
        $this->assertInstanceOf(AdventBattleMyRankingData::class, $actual);
        $this->assertEquals(
            $actual->formatToResponse(),
            ['rank' => 2, 'score' => $userScore, 'totalScore' => $userScore, 'isExcludeRanking' => $isExcludeRanking]
        );
    }
}
