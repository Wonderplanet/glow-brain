<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp;

use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Outpost\Models\UsrOutpostEnhancement;
use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Pvp\Entities\PvpEncyclopediaEffect;
use App\Domain\Pvp\Services\PvpCacheService;
use App\Http\Responses\Data\OpponentPvpStatusData;
use App\Http\Responses\Data\OpponentSelectStatusData;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Http\Responses\Data\PvpUnitData;
use Tests\TestCase;

class PvpCacheServiceTest extends TestCase
{
    private PvpCacheService $pvpCacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pvpCacheService = $this->app->make(PvpCacheService::class);
    }

    public function test_increment_ranking_score_一度もスコアを加算していない場合はnullになる(): void
    {        
        $sysPvpSeasonId = '2025001';
        $usrUserId = 'user123';

        // １度も加算されてない場合はnullが返ることを確認
        $cachedScore = $this->pvpCacheService->getRankingScore($sysPvpSeasonId, $usrUserId);
        $this->assertNull($cachedScore);
    }

    public function test_increment_ranking_score_ランキングキャッシュへスコアを加算できる(): void
    {
        $sysPvpSeasonId = '2025001';
        $usrUserId = 'user123';
        $score = 100;

        // スコアをインクリメント
        for ($i = 1; $i <= 10; $i++) {
            
            $this->pvpCacheService->incrementRankingScore($sysPvpSeasonId, $usrUserId, $score);

            // キャッシュからスコアを取得して確認
            $cachedScore = $this->pvpCacheService->getRankingScore($sysPvpSeasonId, $usrUserId);
            $this->assertEquals($score * $i, $cachedScore);
        }
    }

    public function test_add_ranking_score_ランキングキャッシュへスコアを追加できる(): void
    {
        $sysPvpSeasonId = '2025001';
        $usrUserId = 'user123';
        $score = 100;

        // スコアをインクリメント
        for ($i = 1; $i <= 10; $i++) {
            
            $this->pvpCacheService->addRankingScore($sysPvpSeasonId, $usrUserId, $score * $i);

            // キャッシュからスコアを取得して確認
            $cachedScore = $this->pvpCacheService->getRankingScore($sysPvpSeasonId, $usrUserId);
            $this->assertEquals($score * $i, $cachedScore);
        }
    }

    public function test_add_opponent_status_キャッシュへ対戦情報をセットして問題なく取得できる(): void
    {
        $sysPvpSeasonId = '2025001';
        $myId = 'user123';
        $opponentSelectStatusData = new OpponentSelectStatusData(
            'opponent123',
            'Opponent Name',
            'mstUnitId123',
            'mstEmblemId123',
            1500,
            collect(['unit1', 'unit2', 'unit3']),
            50
        );
        $pvpUnit = new PvpUnitData(
            'mstUnitId123',
            1,
            12,
            123,
        );
        $usrOutpostEnhancement = UsrOutpostEnhancement::factory()->make([
            'usr_user_id' => $myId,
        ]);
        $pvpEncyclopediaEffect = new PvpEncyclopediaEffect('encyclopediaEffectId123');
        $opponentPvpStatusData = new OpponentPvpStatusData(
            $opponentSelectStatusData,
            collect([$pvpUnit]),
            collect([$usrOutpostEnhancement]), // UsrOutpostEnhancements
            collect([$pvpEncyclopediaEffect]), // UsrEncyclopediaEffects
            collect(['artwork1', 'artwork2']) // MstArtworkIds
        );

        // キャッシュへ追加
        $this->pvpCacheService->addOpponentStatus($sysPvpSeasonId, $myId, $opponentPvpStatusData);

        // キャッシュから取得
        $cachedData = $this->pvpCacheService->getOpponentStatus($sysPvpSeasonId, $myId);
        
        $this->assertNotNull($cachedData);
        $this->assertInstanceOf(OpponentPvpStatusData::class, $cachedData);
        $this->assertEquals($opponentSelectStatusData->getMyId(), $cachedData->getPvpUserProfile()->getMyId());
    }

    public function test_delete_opponent_candidate_キャッシュから対戦候補を削除できる(): void
    {
        $sysPvpSeasonId = '2025001';
        $myId = 'user123';
        $myId2 = 'user456';

        $rankClassType = 'dummyRankClassType';
        $rankClassLevel = 1;
        $score = 10;

        // キャッシュへ追加
        $this->pvpCacheService->addOpponentCandidate(
            $sysPvpSeasonId,
            $myId,
            $rankClassType,
            $rankClassLevel,
            $score
        );
        $this->pvpCacheService->addOpponentCandidate(
            $sysPvpSeasonId,
            $myId2,
            $rankClassType,
            $rankClassLevel,
            $score
        );

        // キャッシュから取得
        $cachedData = $this->pvpCacheService->getOpponentCandidateRangeList(
            $sysPvpSeasonId,
            $rankClassType,
            $rankClassLevel,
            0, // start
            $score * 10,
        );
        
        $this->assertNotEmpty($cachedData);
        $this->assertCount(2, $cachedData);

        $this->pvpCacheService->deleteOpponentCandidate(
            $sysPvpSeasonId,
            $myId,
            $rankClassType,
            $rankClassLevel
        );

        // キャッシュから削除後、再度取得して１人になることを確認
        $cachedData = $this->pvpCacheService->getOpponentCandidateRangeList(
            $sysPvpSeasonId,
            $rankClassType,
            $rankClassLevel,
            0, // start
            $score * 10,
        );
        $this->assertNotEmpty($cachedData);
        $this->assertCount(1, $cachedData);
        $this->assertEquals($myId2, $cachedData[0]);
    }
    

    #[DataProvider('param_isViewableRanking_ランキングが開けるか確認')]
    public function test_is_viewable_ranking(int $userCount, bool $expected): void
    {
        $sysPvpSeasonId = 'test_season_' . $userCount;
        for ($i = 1; $i <= $userCount; $i++) {
            $this->pvpCacheService->addRankingScore($sysPvpSeasonId, "user{$i}", $i * 10);
        }
        $result = $this->pvpCacheService->isViewableRanking($sysPvpSeasonId);
        $this->assertSame($expected, $result);
    }

    public static function param_isViewableRanking_ランキングが開けるか確認()
    {
        return [
            '0 users is not viewable' => [0, false],
            '1 users is viewable' => [1, true],
            '100 users is viewable' => [100, true],
        ];
    }

    public function test_is_viewable_ranking_ランキングキャッシュがある場合はtrue(): void
    {
        $sysPvpSeasonId = 'test_season_with_cache';
        $cacheKey = CacheKeyUtil::getPvpRankingCacheKey($sysPvpSeasonId);
        $cacheClientManager = $this->app->make(CacheClientManager::class);
        $cacheClientManager->getCacheClient()->set($cacheKey, collect(), 10);

        // キャッシュが存在する場合はランキング表示可能
        $result = $this->pvpCacheService->isViewableRanking($sysPvpSeasonId);
        $this->assertTrue($result);
    }



    public static function param_testGetTopRankedPlayerScoreMap_topNユーザーのスコアが取得できる()
    {
        return [
            '有効ユーザーが100未満' => [
                'normalUserCount' => 10,
                'cheaterUserCount' => 10,
                'topN' => 100,
                'score' => null,
                'expected' => 10,
            ],
            '有効ユーザーが100以上' => [
                'normalUserCount' => 110,
                'cheaterUserCount' => 10,
                'topN' => 100,
                'score' => null,
                'expected' => 100,
            ],
            '同率スコアで100人を超える' => [
                'normalUserCount' => 100 + PvpConstant::RANKING_FETCH_BUFFER + 10,
                'cheaterUserCount' => 10,
                'topN' => 100,
                'score' => 1000,
                'expected' => 100 + PvpConstant::RANKING_FETCH_BUFFER,
            ],
        ];
    }

    #[DataProvider('param_testGetTopRankedPlayerScoreMap_topNユーザーのスコアが取得できる')]
    public function testGetTopRankedPlayerScoreMap_topNユーザーのスコアが取得できる(
        int $normalUserCount,
        int $cheaterUserCount,
        int $topN,
        ?int $score,
        int $expected
    ) {
        // Setup
        $sysPvpSeasonId = '2025020';

        $key = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        $members = [];
        // 正規ユーザー登録
        foreach (range(1, $normalUserCount) as $i) {
            $members["user$i"] = $score ?? fake()->numberBetween(1, 100000);
        }
        // チートユーザー登録
        foreach (range($normalUserCount + 1, $normalUserCount + $cheaterUserCount) as $i) {
            $members["user$i"] = PvpConstant::RANKING_CHEATER_SCORE;
        }
        Redis::connection()->zadd($key, $members);

        // Exercise
        $actual = $this->pvpCacheService->getTopRankedPlayerScoreMap($sysPvpSeasonId, $topN);

        // Verify
        $this->assertCount($expected, $actual);
    }

    public static function params_testGetOpponentCandidateRangeList_指定範囲の対戦候補が取得できる()
    {
        return [
            '候補者が取得上限を超えていない' => [
                'targetUserCount' => PvpConstant::MATCHING_CACHE_GET_LIMIT,
                'minScore' => 50,
                'maxScore' => 100,
                'expectedCount' => PvpConstant::MATCHING_CACHE_GET_LIMIT,
            ],
            '候補者が取得上限を超えている場合は上限まで取得' => [
                'targetUserCount' => PvpConstant::MATCHING_CACHE_GET_LIMIT + 10,
                'minScore' => 50,
                'maxScore' => 100,
                'expectedCount' => PvpConstant::MATCHING_CACHE_GET_LIMIT,
            ],
        ];
    }

    #[DataProvider('params_testGetOpponentCandidateRangeList_指定範囲の対戦候補が取得できる')]
    public function testGetOpponentCandidateRangeList_指定範囲の対戦候補が取得できる(
        int $targetUserCount,
        int $minScore,
        int $maxScore,
        int $expectedCount
    ) {
        // Setup
        $sysPvpSeasonId = '2025001';
        $rankClassType = 'Bronze';
        $rankClassLevel = 1;

        // 抽選対象ユーザーを登録
        $this->addCandidate($sysPvpSeasonId, $rankClassType, $rankClassLevel, $minScore, $maxScore, $targetUserCount);

        // 抽選対象外ユーザーを登録
        $this->addCandidate($sysPvpSeasonId, $rankClassType, $rankClassLevel, 0, $minScore - 1, 5);
        $this->addCandidate($sysPvpSeasonId, $rankClassType, $rankClassLevel, $maxScore + 1, $maxScore + 50, 5);

        // Exercise
        $cachedData = $this->pvpCacheService->getOpponentCandidateRangeList(
            $sysPvpSeasonId,
            $rankClassType,
            $rankClassLevel,
            $minScore,
            $maxScore,
        );

        // Verify
        $this->assertCount($expectedCount, $cachedData);
    }

    private function addCandidate(string $sysPvpSeasonId, string $rankClassType, int $rankClassLevel, int $minScore, int $maxScore, int $userCount): void
    {
        $cacheKey = CacheKeyUtil::getPvpOpponentCandidateKey($sysPvpSeasonId, $rankClassType, $rankClassLevel);
        $candidates = [];
        foreach (range(1, $userCount) as $i) {
            $myId = fake()->uuid();
            $score = rand($minScore, $maxScore);
            $candidates[$myId] = $score;
        }
        Redis::connection()->zadd($cacheKey, $candidates);
    }
}
