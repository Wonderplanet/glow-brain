# キャッシュテスト実装ガイド

キャッシュ処理のテストコード実装について説明します。

## テストの基本方針

### 1. キャッシュの独立性

各テストケースは独立して実行できるようにします。

**実装のポイント:**
- テストごとに異なるキーを使用する
- setUp/tearDownでキャッシュをクリアしない（自動クリア）
- テスト用のユニークなIDを生成する

```php
public function test_increment_ranking_score_ランキングキャッシュへスコアを加算できる(): void
{
    // テストごとにユニークなIDを使用
    $sysPvpSeasonId = '2025001';
    $usrUserId = 'user123';
    $score = 100;

    // テスト実行
    $this->pvpCacheService->incrementRankingScore($sysPvpSeasonId, $usrUserId, $score);

    // 検証
    $cachedScore = $this->pvpCacheService->getRankingScore($sysPvpSeasonId, $usrUserId);
    $this->assertEquals($score, $cachedScore);
}
```

### 2. Redis直接操作の許容

テストコードでは、データのセットアップのために`Redis::connection()`を直接使用することが許容されます。

```php
use Illuminate\Support\Facades\Redis;

public function test_example(): void
{
    $key = CacheKeyUtil::getPvpRankingKey('2025001');
    $members = ['user1' => 100, 'user2' => 200];

    // テストデータのセットアップでRedis直接使用は許容される
    Redis::connection()->zadd($key, $members);

    // テスト実行と検証
    $result = $this->pvpCacheService->getTopRankedPlayerScoreMap('2025001', 2);
    $this->assertCount(2, $result);
}
```

### 3. 環境の確認

テストはlocal環境で実行され、Redisが使用されることを前提とします。

## テストパターン

### パターン1: 基本的なset/get/delete

```php
public function test_add_opponent_status_キャッシュへ対戦情報をセットして問題なく取得できる(): void
{
    // Arrange（準備）
    $sysPvpSeasonId = '2025001';
    $myId = 'user123';
    $opponentPvpStatusData = new OpponentPvpStatusData(/* ... */);

    // Act（実行）
    $this->pvpCacheService->addOpponentStatus($sysPvpSeasonId, $myId, $opponentPvpStatusData);

    // Assert（検証）
    $cachedData = $this->pvpCacheService->getOpponentStatus($sysPvpSeasonId, $myId);

    $this->assertNotNull($cachedData);
    $this->assertInstanceOf(OpponentPvpStatusData::class, $cachedData);
    $this->assertEquals($opponentPvpStatusData->getPvpUserProfile()->getMyId(), $cachedData->getPvpUserProfile()->getMyId());
}
```

### パターン2: インクリメント操作

```php
public function test_increment_ranking_score_ランキングキャッシュへスコアを加算できる(): void
{
    // Arrange
    $sysPvpSeasonId = '2025001';
    $usrUserId = 'user123';
    $score = 100;

    // Act & Assert（複数回実行して検証）
    for ($i = 1; $i <= 10; $i++) {
        $this->pvpCacheService->incrementRankingScore($sysPvpSeasonId, $usrUserId, $score);

        $cachedScore = $this->pvpCacheService->getRankingScore($sysPvpSeasonId, $usrUserId);
        $this->assertEquals($score * $i, $cachedScore);
    }
}
```

### パターン3: Sorted Set操作（zadd/zrem）

```php
public function test_delete_opponent_candidate_キャッシュから対戦候補を削除できる(): void
{
    // Arrange
    $sysPvpSeasonId = '2025001';
    $myId = 'user123';
    $myId2 = 'user456';
    $rankClassType = 'Bronze';
    $rankClassLevel = 1;
    $score = 10;

    // キャッシュへ追加
    $this->pvpCacheService->addOpponentCandidate($sysPvpSeasonId, $myId, $rankClassType, $rankClassLevel, $score);
    $this->pvpCacheService->addOpponentCandidate($sysPvpSeasonId, $myId2, $rankClassType, $rankClassLevel, $score);

    // 追加されたことを確認
    $cachedData = $this->pvpCacheService->getOpponentCandidateRangeList(
        $sysPvpSeasonId,
        $rankClassType,
        $rankClassLevel,
        0,
        $score * 10,
    );
    $this->assertCount(2, $cachedData);

    // Act（削除）
    $this->pvpCacheService->deleteOpponentCandidate($sysPvpSeasonId, $myId, $rankClassType, $rankClassLevel);

    // Assert（削除後の確認）
    $cachedData = $this->pvpCacheService->getOpponentCandidateRangeList(
        $sysPvpSeasonId,
        $rankClassType,
        $rankClassLevel,
        0,
        $score * 10,
    );
    $this->assertCount(1, $cachedData);
    $this->assertEquals($myId2, $cachedData[0]);
}
```

### パターン4: DataProviderを使用した複数ケース

```php
#[DataProvider('param_isViewableRanking_ランキングが開けるか確認')]
public function test_is_viewable_ranking(int $userCount, bool $expected): void
{
    // Arrange
    $sysPvpSeasonId = 'test_season_' . $userCount; // ユニークなIDを生成
    for ($i = 1; $i <= $userCount; $i++) {
        $this->pvpCacheService->addRankingScore($sysPvpSeasonId, "user{$i}", $i * 10);
    }

    // Act
    $result = $this->pvpCacheService->isViewableRanking($sysPvpSeasonId);

    // Assert
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
```

### パターン5: nullチェック

```php
public function test_increment_ranking_score_一度もスコアを加算していない場合はnullになる(): void
{
    // Arrange
    $sysPvpSeasonId = '2025001';
    $usrUserId = 'user123';

    // Act & Assert
    $cachedScore = $this->pvpCacheService->getRankingScore($sysPvpSeasonId, $usrUserId);
    $this->assertNull($cachedScore);
}
```

## テスト用のヘルパーメソッド

大量のテストデータをセットアップする場合は、ヘルパーメソッドを作成します。

```php
private function addCandidate(
    string $sysPvpSeasonId,
    string $rankClassType,
    int $rankClassLevel,
    int $minScore,
    int $maxScore,
    int $userCount
): void {
    $cacheKey = CacheKeyUtil::getPvpOpponentCandidateKey($sysPvpSeasonId, $rankClassType, $rankClassLevel);
    $candidates = [];
    foreach (range(1, $userCount) as $i) {
        $myId = fake()->uuid();
        $score = rand($minScore, $maxScore);
        $candidates[$myId] = $score;
    }
    Redis::connection()->zadd($cacheKey, $candidates);
}
```

**使用例:**
```php
public function testGetOpponentCandidateRangeList_指定範囲の対戦候補が取得できる(): void
{
    // ヘルパーメソッドでテストデータをセットアップ
    $this->addCandidate('2025001', 'Bronze', 1, 50, 100, 10);
    $this->addCandidate('2025001', 'Bronze', 1, 0, 49, 5);
    $this->addCandidate('2025001', 'Bronze', 1, 101, 150, 5);

    // テスト実行
    $cachedData = $this->pvpCacheService->getOpponentCandidateRangeList('2025001', 'Bronze', 1, 50, 100);

    // 検証
    $this->assertCount(10, $cachedData);
}
```

## CacheClientManagerを使用したテスト

CacheClientManagerを直接使用してテストする場合。

```php
public function test_is_viewable_ranking_ランキングキャッシュがある場合はtrue(): void
{
    // Arrange
    $sysPvpSeasonId = 'test_season_with_cache';
    $cacheKey = CacheKeyUtil::getPvpRankingCacheKey($sysPvpSeasonId);
    $cacheClientManager = $this->app->make(CacheClientManager::class);

    // キャッシュを直接設定
    $cacheClientManager->getCacheClient()->set($cacheKey, collect(), 10);

    // Act & Assert
    $result = $this->pvpCacheService->isViewableRanking($sysPvpSeasonId);
    $this->assertTrue($result);
}
```

## テストクラスの構成

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\{Domain};

use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Common\Utils\CacheKeyUtil;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class {Service}Test extends TestCase
{
    private {Service} ${service};

    protected function setUp(): void
    {
        parent::setUp();
        $this->{service} = $this->app->make({Service}::class);
    }

    // テストメソッド
}
```

## アサーションのベストプラクティス

### 基本的なアサーション

```php
// 等価性チェック
$this->assertEquals($expected, $actual);

// 型チェック
$this->assertInstanceOf(ClassName::class, $object);

// null/not nullチェック
$this->assertNull($value);
$this->assertNotNull($value);

// 真偽チェック
$this->assertTrue($condition);
$this->assertFalse($condition);
$this->assertSame(true, $condition); // 厳密な比較

// 配列/コレクションチェック
$this->assertCount(10, $array);
$this->assertNotEmpty($array);
$this->assertEmpty($array);
```

### エラーメッセージ付きアサーション

```php
$this->assertEquals(
    $expected,
    $actual,
    'キャッシュから取得した値が期待値と異なります'
);
```

## よくある問題と対処法

### 問題1: テスト間でキャッシュが干渉する

**原因:** 同じキーを使用している

**対処法:** テストごとにユニークなキーを生成する

```php
// ❌ 間違い
$sysPvpSeasonId = '2025001'; // 全テストで同じ

// ✅ 正しい
$sysPvpSeasonId = 'test_season_' . uniqid(); // ユニークなID
```

### 問題2: Redis接続エラー

**原因:** Redisが起動していない

**対処法:** Docker環境でRedisを起動する

```bash
./tools/bin/sail-wp up -d redis
```

### 問題3: シリアライズエラー

**原因:** 複雑なオブジェクトをキャッシュしている

**対処法:** キャッシュ可能なデータ構造に変換する

```php
// ❌ 間違い（クロージャはシリアライズできない）
$data = ['callback' => fn() => 'test'];
$this->cacheClientManager->getCacheClient()->set($key, $data);

// ✅ 正しい（プリミティブ型や配列、DTOのみ）
$data = ['value' => 'test'];
$this->cacheClientManager->getCacheClient()->set($key, $data);
```
