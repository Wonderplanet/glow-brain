# ランキングキャッシュ実装例

PvpCacheServiceを使ったランキングシステムのキャッシュ実装例を紹介します。

## 実装例: PvpCacheService

PVPのランキングとマッチングをSorted Setで管理するサービスです。

### ファイル: PvpCacheService.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Services;

use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Pvp\Models\UsrPvpInterface;
use App\Http\Responses\Data\OpponentPvpStatusData;
use App\Http\Responses\Data\PvpMyRankingData;
use Illuminate\Support\Collection;

class PvpCacheService
{
    private const TWO_WEEKS_SECONDS = 14 * 24 * 60 * 60; // 2週間

    public function __construct(
        private CacheClientManager $cacheClientManager,
    ) {
    }

    /**
     * ランキングにスコアを追加
     */
    public function addRankingScore(
        string $sysPvpSeasonId,
        string $usrUserId,
        int $score
    ): void {
        $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        $this->cacheClientManager->getCacheClient()->zadd($cacheKey, [$usrUserId => $score]);
    }

    /**
     * ランキングスコアをインクリメント
     */
    public function incrementRankingScore(
        string $sysPvpSeasonId,
        string $usrUserId,
        int $deltaPoint
    ): void {
        $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        $this->cacheClientManager->getCacheClient()->zincrby($cacheKey, $deltaPoint, $usrUserId);
    }

    /**
     * ランキングスコアを取得
     */
    public function getRankingScore(string $sysPvpSeasonId, string $usrUserId): ?int
    {
        $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        $score = $this->cacheClientManager->getCacheClient()->zscore($cacheKey, $usrUserId);

        return $score !== false ? (int)$score : null;
    }

    /**
     * ランキング参加人数を取得（スコア0以上）
     */
    public function getRankingCount(string $sysPvpSeasonId): int
    {
        $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        return $this->cacheClientManager->getCacheClient()->zCount($cacheKey, 0, '+inf');
    }

    /**
     * 自分の順位を取得
     */
    public function getMyRanking(string $usrUserId, string $sysPvpSeasonId): ?int
    {
        $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        $score = $this->cacheClientManager->getCacheClient()->zScore($cacheKey, $usrUserId);

        if ($score === false) {
            return null; // ランキングに存在しない
        }

        // 自分より上のユーザー数 + 1 = 順位
        return $this->cacheClientManager->getCacheClient()->zCount(
            $cacheKey,
            (int) $score + 1,
            "+inf"
        ) + 1;
    }

    /**
     * 上位N人のランキングを取得
     */
    public function getTopRankedPlayerScoreMap(string $sysPvpSeasonId, int $topN): array
    {
        $cacheClient = $this->cacheClientManager->getCacheClient();
        $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);

        // チーター以外（スコア0以上）のユーザー数を取得
        $nonCheaterUserCount = $cacheClient->zCount($cacheKey, 0, '+inf');

        if ($nonCheaterUserCount <= $topN) {
            // topN件以下なら全ユーザーが対象
            $minScore = 0;
        } else {
            // topN番目のスコアを取得
            $index = $topN - 1;
            $minScore = (int) current(
                $cacheClient->zRevRange($cacheKey, $index, $index, true)
            );
        }

        // 同率ユーザー対策で上限を設定
        $limit = $topN + PvpConstant::RANKING_FETCH_BUFFER;

        return $cacheClient->zRevRangeByScore(
            $cacheKey,
            '+inf',
            $minScore,
            true,
            $limit
        );
    }

    /**
     * ランキングAPIレスポンス全体をキャッシュ
     */
    public function getPvpRankingCache(string $sysPvpSeasonId): Collection|null
    {
        $cacheKey = CacheKeyUtil::getPvpRankingCacheKey($sysPvpSeasonId);
        return $this->cacheClientManager->getCacheClient()->get($cacheKey);
    }

    /**
     * ランキングAPIレスポンス全体を保存
     */
    public function setPvpRankingCache(
        string $sysPvpSeasonId,
        Collection $pvpRankingItems,
        int $ttl
    ): void {
        $cacheKey = CacheKeyUtil::getPvpRankingCacheKey($sysPvpSeasonId);
        $this->cacheClientManager->getCacheClient()->set($cacheKey, $pvpRankingItems, $ttl);
    }

    /**
     * マッチング候補を追加
     */
    public function addOpponentCandidate(
        string $sysPvpSeasonId,
        string $myId,
        string $rankClassType,
        int $rankClassLevel,
        int $score
    ): void {
        $cacheKey = CacheKeyUtil::getPvpOpponentCandidateKey(
            $sysPvpSeasonId,
            $rankClassType,
            $rankClassLevel
        );
        $this->cacheClientManager->getCacheClient()->zAdd($cacheKey, [$myId => $score]);
    }

    /**
     * マッチング候補を削除
     */
    public function deleteOpponentCandidate(
        string $sysPvpSeasonId,
        string $myId,
        string $rankClassType,
        int $rankClassLevel,
    ): void {
        $cacheKey = CacheKeyUtil::getPvpOpponentCandidateKey(
            $sysPvpSeasonId,
            $rankClassType,
            $rankClassLevel
        );
        $this->cacheClientManager->getCacheClient()->zRem($cacheKey, [$myId]);
    }

    /**
     * スコア範囲でマッチング候補を取得
     */
    public function getOpponentCandidateRangeList(
        string $sysPvpSeasonId,
        string $rankClassType,
        int $rankClassLevel,
        int $minScore,
        int $maxScore
    ): array {
        $cacheKey = CacheKeyUtil::getPvpOpponentCandidateKey(
            $sysPvpSeasonId,
            $rankClassType,
            $rankClassLevel
        );
        $result = $this->cacheClientManager->getCacheClient()->zRevRangeByScore(
            $cacheKey,
            $maxScore,
            $minScore,
            false,
            PvpConstant::MATCHING_CACHE_GET_LIMIT
        );

        if ($result === false) {
            return [];
        }

        return $result;
    }

    /**
     * 対戦相手の状態を保存
     */
    public function addOpponentStatus(
        string $sysPvpSeasonId,
        string $myId,
        OpponentPvpStatusData $opponentPvpStatusData,
    ): void {
        $cacheKey = CacheKeyUtil::getPvpOpponentStatusKey($sysPvpSeasonId, $myId);
        $this->cacheClientManager->getCacheClient()->set(
            $cacheKey,
            $opponentPvpStatusData,
            self::TWO_WEEKS_SECONDS
        );
    }

    /**
     * 対戦相手の状態を取得
     */
    public function getOpponentStatus(
        string $sysPvpSeasonId,
        string $myId,
    ): ?OpponentPvpStatusData {
        $cacheKey = CacheKeyUtil::getPvpOpponentStatusKey($sysPvpSeasonId, $myId);
        return $this->cacheClientManager->getCacheClient()->get($cacheKey);
    }
}
```

## 使用例

### 使用例1: バトル終了時のスコア更新

```php
class PvpEndService
{
    public function __construct(
        private PvpCacheService $pvpCacheService,
    ) {
    }

    public function endBattle(
        string $sysPvpSeasonId,
        string $usrUserId,
        int $deltaScore
    ): void {
        // ランキングスコアをインクリメント
        $this->pvpCacheService->incrementRankingScore(
            $sysPvpSeasonId,
            $usrUserId,
            $deltaScore
        );

        // マッチング候補に追加（更新後のスコアで）
        $newScore = $this->pvpCacheService->getRankingScore($sysPvpSeasonId, $usrUserId);
        $this->pvpCacheService->addOpponentCandidate(
            $sysPvpSeasonId,
            $usrUserId,
            'Bronze', // ランククラス
            3,        // ランクレベル
            $newScore
        );
    }
}
```

### 使用例2: ランキング取得

```php
class PvpRankingService
{
    public function __construct(
        private PvpCacheService $pvpCacheService,
    ) {
    }

    public function getRanking(string $sysPvpSeasonId, int $limit): Collection
    {
        // キャッシュされたレスポンスがあれば返す
        $cachedRanking = $this->pvpCacheService->getPvpRankingCache($sysPvpSeasonId);
        if ($cachedRanking !== null) {
            return $cachedRanking;
        }

        // 上位N人のスコアを取得
        $topScoreMap = $this->pvpCacheService->getTopRankedPlayerScoreMap($sysPvpSeasonId, $limit);

        // ユーザー情報を取得してレスポンスを作成
        $rankingItems = $this->buildRankingItems($topScoreMap);

        // 5分間キャッシュ
        $this->pvpCacheService->setPvpRankingCache($sysPvpSeasonId, $rankingItems, 300);

        return $rankingItems;
    }

    private function buildRankingItems(array $scoreMap): Collection
    {
        $rank = 1;
        $items = [];
        foreach ($scoreMap as $userId => $score) {
            $items[] = new PvpRankingItem($rank, $userId, (int)$score);
            $rank++;
        }
        return collect($items);
    }
}
```

### 使用例3: マイランキング取得

```php
class PvpTopUseCase
{
    public function __construct(
        private PvpCacheService $pvpCacheService,
        private UsrPvpRepository $usrPvpRepository,
    ) {
    }

    public function execute(string $usrUserId): ResultData
    {
        $usrPvp = $this->usrPvpRepository->getByUserId($usrUserId);

        if ($usrPvp === null) {
            // PVP未参加
            return new ResultData([
                'my_ranking' => null,
                'my_score' => null,
            ]);
        }

        $sysPvpSeasonId = $usrPvp->getSysPvpSeasonId();

        // 自分の順位を取得
        $myRanking = $this->pvpCacheService->getMyRanking($usrUserId, $sysPvpSeasonId);

        // 自分のスコアを取得
        $myScore = $this->pvpCacheService->getRankingScore($sysPvpSeasonId, $usrUserId);

        return new ResultData([
            'my_ranking' => $myRanking,
            'my_score' => $myScore,
        ]);
    }
}
```

### 使用例4: マッチング（対戦相手抽選）

```php
class PvpMatchingService
{
    public function __construct(
        private PvpCacheService $pvpCacheService,
    ) {
    }

    public function findOpponent(
        string $sysPvpSeasonId,
        string $usrUserId,
        int $myScore,
        string $rankClassType,
        int $rankClassLevel
    ): ?string {
        // 自分のスコア ±100 の範囲で候補を取得
        $minScore = max(0, $myScore - 100);
        $maxScore = $myScore + 100;

        $candidates = $this->pvpCacheService->getOpponentCandidateRangeList(
            $sysPvpSeasonId,
            $rankClassType,
            $rankClassLevel,
            $minScore,
            $maxScore
        );

        // 自分を除外
        $candidates = array_filter($candidates, fn($id) => $id !== $usrUserId);

        if (empty($candidates)) {
            return null;
        }

        // ランダムに1人選択
        return $candidates[array_rand($candidates)];
    }
}
```

## テスト実装例

### PvpCacheServiceTest.php

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp\Services;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Pvp\Services\PvpCacheService;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PvpCacheServiceTest extends TestCase
{
    private PvpCacheService $pvpCacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pvpCacheService = $this->app->make(PvpCacheService::class);
    }

    public function test_incrementRankingScore_ランキングキャッシュへスコアを加算できる(): void
    {
        $sysPvpSeasonId = '2025001';
        $usrUserId = 'user123';
        $score = 100;

        // 10回加算
        for ($i = 1; $i <= 10; $i++) {
            $this->pvpCacheService->incrementRankingScore($sysPvpSeasonId, $usrUserId, $score);

            $cachedScore = $this->pvpCacheService->getRankingScore($sysPvpSeasonId, $usrUserId);
            $this->assertEquals($score * $i, $cachedScore);
        }
    }

    public function test_getRankingScore_一度もスコアを加算していない場合はnullになる(): void
    {
        $sysPvpSeasonId = '2025001';
        $usrUserId = 'user123';

        $cachedScore = $this->pvpCacheService->getRankingScore($sysPvpSeasonId, $usrUserId);
        $this->assertNull($cachedScore);
    }

    public function test_getMyRanking_順位を正しく取得できる(): void
    {
        $sysPvpSeasonId = '2025001';

        // 3人のユーザーを登録
        $this->pvpCacheService->addRankingScore($sysPvpSeasonId, 'user1', 300); // 1位
        $this->pvpCacheService->addRankingScore($sysPvpSeasonId, 'user2', 200); // 2位
        $this->pvpCacheService->addRankingScore($sysPvpSeasonId, 'user3', 100); // 3位

        // 各ユーザーの順位を確認
        $this->assertEquals(1, $this->pvpCacheService->getMyRanking('user1', $sysPvpSeasonId));
        $this->assertEquals(2, $this->pvpCacheService->getMyRanking('user2', $sysPvpSeasonId));
        $this->assertEquals(3, $this->pvpCacheService->getMyRanking('user3', $sysPvpSeasonId));
    }

    public function test_getOpponentCandidateRangeList_指定範囲の対戦候補が取得できる(): void
    {
        $sysPvpSeasonId = '2025001';
        $rankClassType = 'Bronze';
        $rankClassLevel = 1;

        // 抽選対象ユーザーを登録（スコア50～100）
        $this->addCandidate($sysPvpSeasonId, $rankClassType, $rankClassLevel, 50, 100, 10);

        // 抽選対象外ユーザーを登録
        $this->addCandidate($sysPvpSeasonId, $rankClassType, $rankClassLevel, 0, 49, 5);
        $this->addCandidate($sysPvpSeasonId, $rankClassType, $rankClassLevel, 101, 150, 5);

        // スコア50～100の範囲で取得
        $cachedData = $this->pvpCacheService->getOpponentCandidateRangeList(
            $sysPvpSeasonId,
            $rankClassType,
            $rankClassLevel,
            50,
            100,
        );

        // 10人取得できることを確認
        $this->assertCount(10, $cachedData);
    }

    #[DataProvider('param_getTopRankedPlayerScoreMap')]
    public function test_getTopRankedPlayerScoreMap_上位Nユーザーのスコアが取得できる(
        int $normalUserCount,
        int $cheaterUserCount,
        int $topN,
        ?int $score,
        int $expected
    ): void {
        $sysPvpSeasonId = '2025020';
        $key = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        $members = [];

        // 正規ユーザー登録
        foreach (range(1, $normalUserCount) as $i) {
            $members["user$i"] = $score ?? fake()->numberBetween(1, 100000);
        }

        // チートユーザー登録（スコア負）
        foreach (range($normalUserCount + 1, $normalUserCount + $cheaterUserCount) as $i) {
            $members["user$i"] = PvpConstant::RANKING_CHEATER_SCORE;
        }

        Redis::connection()->zadd($key, $members);

        // 上位N人取得
        $actual = $this->pvpCacheService->getTopRankedPlayerScoreMap($sysPvpSeasonId, $topN);

        // 期待件数を確認
        $this->assertCount($expected, $actual);
    }

    public static function param_getTopRankedPlayerScoreMap(): array
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

    private function addCandidate(
        string $sysPvpSeasonId,
        string $rankClassType,
        int $rankClassLevel,
        int $minScore,
        int $maxScore,
        int $userCount
    ): void {
        $cacheKey = CacheKeyUtil::getPvpOpponentCandidateKey(
            $sysPvpSeasonId,
            $rankClassType,
            $rankClassLevel
        );
        $candidates = [];
        foreach (range(1, $userCount) as $i) {
            $myId = fake()->uuid();
            $score = rand($minScore, $maxScore);
            $candidates[$myId] = $score;
        }
        Redis::connection()->zadd($cacheKey, $candidates);
    }
}
```

## まとめ

PvpCacheServiceでは、Sorted Setを活用して以下を実現しています。

1. **ランキング管理**: zAdd/zIncrBy/zScoreでスコア管理
2. **順位計算**: zCountで自分より上のユーザー数をカウント
3. **上位取得**: zRevRangeByScoreで上位N人を効率的に取得
4. **マッチング**: スコア範囲でzRevRangeByScoreして候補を抽選
5. **レスポンスキャッシュ**: 計算済みランキングをset/getでキャッシュ

Sorted Setを使うことで、大量のユーザーがいてもO(log N)の計算量でランキング操作が可能になります。
