# Sorted Set実装パターン

Sorted Set（zAdd/zIncrBy/zScore等）を使ったランキングやスコアベースのキャッシュ実装パターンについて説明します。

## パターン概要

Sorted Setは、メンバー（文字列）とスコア（数値）の組み合わせでデータを管理する、Redis/momentoのデータ構造です。

**適用ケース:**
- ランキングシステム
- スコアベースのマッチング
- タイムスタンプ順のデータ管理
- 優先度付きキュー

## 基本操作

### zAdd（メンバー追加）

Sorted Setにメンバーとスコアを追加または更新します。

```php
public function addRankingScore(string $sysPvpSeasonId, string $usrUserId, int $score): void
{
    $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
    $this->cacheClientManager->getCacheClient()->zadd(
        $cacheKey,
        [$usrUserId => $score]
    );
}
```

**複数メンバー追加:**
```php
$members = [
    'user1' => 100,
    'user2' => 200,
    'user3' => 150,
];
$this->cacheClientManager->getCacheClient()->zadd($cacheKey, $members);
```

### zIncrBy（スコア加算）

既存メンバーのスコアをインクリメントします。

```php
public function incrementRankingScore(
    string $sysPvpSeasonId,
    string $usrUserId,
    int $deltaPoint
): void {
    $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
    $this->cacheClientManager->getCacheClient()->zincrby(
        $cacheKey,
        $deltaPoint,
        $usrUserId
    );
}
```

**使用例:**
```php
// 初回（スコア0から100を加算）
$this->cacheService->incrementRankingScore('2025001', 'user123', 100);
// スコア: 100

// 2回目（既存スコア100に200を加算）
$this->cacheService->incrementRankingScore('2025001', 'user123', 200);
// スコア: 300
```

### zScore（スコア取得）

特定メンバーのスコアを取得します。

```php
public function getRankingScore(string $sysPvpSeasonId, string $usrUserId): ?int
{
    $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
    $score = $this->cacheClientManager->getCacheClient()->zscore($cacheKey, $usrUserId);

    return $score !== false ? (int)$score : null;
}
```

**戻り値:**
- スコアが存在する場合: float型のスコア
- 存在しない場合: false

### zRem（メンバー削除）

Sorted Setからメンバーを削除します。

```php
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
```

### zCount（メンバー数カウント）

スコアの範囲内にいるメンバー数を取得します。

```php
public function getRankingCount(string $sysPvpSeasonId): int
{
    $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);

    // スコア0以上のユーザー数を取得
    return $this->cacheClientManager->getCacheClient()->zCount(
        $cacheKey,
        0,
        '+inf' // 正の無限大
    );
}
```

**スコア範囲指定:**
```php
// 100以上200以下のメンバー数
$count = $this->cacheClient->zCount($key, 100, 200);

// 0以上のメンバー数
$count = $this->cacheClient->zCount($key, 0, '+inf');

// すべてのメンバー数
$count = $this->cacheClient->zCount($key, '-inf', '+inf');
```

## ランキング取得操作

### zRevRange（降順で範囲取得）

スコアが高い順に指定範囲のメンバーを取得します。

```php
/**
 * ランキング上位N位のメンバーを取得
 */
public function getTopNMembers(string $key, int $topN): array
{
    return $this->cacheClientManager->getCacheClient()->zRevRange(
        $key,
        0,           // 開始位置（0が1位）
        $topN - 1,   // 終了位置
        true         // スコアも含める
    );
}
```

**使用例:**
```php
// 上位3位を取得
$topUsers = $this->cacheService->getTopNMembers($cacheKey, 3);
// 結果: ['user1' => 300.0, 'user2' => 200.0, 'user3' => 150.0]
```

### zRevRangeByScore（スコア範囲で降順取得）

指定スコア範囲のメンバーを降順で取得します。

```php
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
        $maxScore,  // 最大スコア
        $minScore,  // 最小スコア
        false,      // スコアを含めない
        100         // 取得上限
    );

    if ($result === false) {
        return [];
    }

    return $result;
}
```

**使用例:**
```php
// スコア50～100の範囲でマッチング候補を取得
$candidates = $this->cacheService->getOpponentCandidateRangeList(
    '2025001',
    'Bronze',
    3,
    50,  // minScore
    100  // maxScore
);
```

## 実装パターン

### パターン1: ランキングシステム

PVPランキングの実装例。

```php
class PvpCacheService
{
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
        $limit = $topN + 10; // バッファを持たせる

        return $cacheClient->zRevRangeByScore(
            $cacheKey,
            '+inf',
            $minScore,
            true,
            $limit
        );
    }
}
```

### パターン2: マッチングシステム

スコアベースの対戦相手抽選。

```php
class PvpCacheService
{
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
            100 // 取得上限
        );

        if ($result === false) {
            return [];
        }

        return $result;
    }
}
```

### パターン3: 累計スコア管理

レイドバトルの累計ダメージ管理。

```php
class AdventBattleCacheService
{
    /**
     * 累計ダメージをインクリメント
     */
    public function incrementRaidTotalScore(
        string $mstAdventBattleId,
        string $usrUserId,
        int $damage
    ): void {
        $cacheKey = CacheKeyUtil::getAdventBattleRaidTotalScoreKey($mstAdventBattleId);
        $this->cacheClientManager->getCacheClient()->zincrby($cacheKey, $damage, $usrUserId);
    }

    /**
     * 累計ダメージを取得
     */
    public function getRaidTotalScore(
        string $mstAdventBattleId,
        string $usrUserId
    ): int {
        $cacheKey = CacheKeyUtil::getAdventBattleRaidTotalScoreKey($mstAdventBattleId);
        $score = $this->cacheClientManager->getCacheClient()->zscore($cacheKey, $usrUserId);

        return $score !== false ? (int)$score : 0;
    }
}
```

## zUnionStore（集計操作）

複数のSorted Setを結合して新しいSorted Setを作成します。

```php
public function zUnionStore(
    string $destination,
    array $keys,
    array $weights,
    string $aggregateFunction
): int {
    return $this->cacheClientManager->getCacheClient()->zUnionStore(
        $destination,
        $keys,
        $weights,
        $aggregateFunction
    );
}
```

**使用例:**
```php
// 複数シーズンのスコアを合算
$destination = 'pvp:total_ranking';
$keys = ['pvp:2025001:ranking', 'pvp:2025002:ranking'];
$weights = [1, 1]; // 重み付け
$aggregateFunction = 'SUM'; // SUM, MIN, MAX

$this->cacheClient->zUnionStore($destination, $keys, $weights, $aggregateFunction);
```

## TTL管理

### expire（有効期限設定）

Sorted Set全体に有効期限を設定します。

```php
public function setRankingExpire(string $sysPvpSeasonId, int $ttl): void
{
    $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
    $this->cacheClientManager->getCacheClient()->expire($cacheKey, $ttl);
}
```

### ttl（残り時間取得）

```php
public function getPvpRankingCacheTtl(string $sysPvpSeasonId): int
{
    $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
    return $this->cacheClientManager->getCacheClient()->ttl($cacheKey);
}
```

**戻り値:**
- `-1`: 無制限
- `-2`: キーが存在しない
- 正の整数: 残り秒数

## ベストプラクティス

### DO（推奨）

✅ スコアは整数または浮動小数点数を使用
✅ メンバー名はユニークな識別子を使用
✅ スコア範囲を適切に設定（チーター除外など）
✅ 取得上限を設定（limit指定）

```php
// ✅ 正しい実装
public function getTopRanking(string $seasonId, int $limit): array
{
    $cacheKey = CacheKeyUtil::getPvpRankingKey($seasonId);

    // スコア0以上（チーター除外）で上限指定
    return $this->cacheClient->zRevRangeByScore(
        $cacheKey,
        '+inf',
        0,
        true,
        $limit
    );
}
```

### DON'T（非推奨）

❌ メンバー名に可変データを含める（タイムスタンプなど）
❌ スコアに文字列を使用する
❌ 取得上限を設定しない（全件取得）
❌ 同率スコア時の対策をしない

```php
// ❌ 間違った実装
public function getTopRanking(string $seasonId): array
{
    $cacheKey = CacheKeyUtil::getPvpRankingKey($seasonId);

    // 上限なしで全件取得（パフォーマンス問題）
    return $this->cacheClient->zRevRange($cacheKey, 0, -1, true);
}
```

## パフォーマンス考慮事項

### 1. 取得上限の設定

大量データを扱う場合は必ず上限を設定します。

```php
// 同率スコアで大量取得を防ぐ
$limit = $topN + 10; // バッファを持たせる
$result = $this->cacheClient->zRevRangeByScore($key, '+inf', $minScore, true, $limit);
```

### 2. スコアの正規化

スコアの桁数が大きくなりすぎないように正規化します。

```php
// ❌ 避けるべき（桁数が大きい）
$score = microtime(true) * 1000000;

// ✅ 推奨（適切な範囲）
$score = time(); // Unix timestamp
```

### 3. キーの分割

データが大きくなる場合はキーを分割します。

```php
// ランクごとにキーを分割
$cacheKey = CacheKeyUtil::getPvpOpponentCandidateKey(
    $sysPvpSeasonId,
    $rankClassType,  // Bronze, Silver, Gold...
    $rankClassLevel  // 1, 2, 3...
);
```
