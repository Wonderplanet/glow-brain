# キャッシュ戦略ガイド

momento/redisのキャッシュ実装における戦略、使い分け、TTL設定について説明します。

## momento vs redis の使い分け

### 環境による自動切り替え

CacheClientManagerが環境に応じて自動的に切り替えます。

**実装:**
```php
class CacheClientManager
{
    private CacheClientInterface $cacheClient;

    public function __construct()
    {
        $env = app()->environment();
        if (str_starts_with($env, 'local')) {
            $this->cacheClient = app()->make(RedisCacheClient::class);
        } else {
            $this->cacheClient = app()->make(MomentoCacheClient::class);
        }
    }
}
```

**環境とキャッシュの対応:**
- **local環境**: Redis（Docker上のRedisコンテナ）
- **テスト環境**: Redis（テスト用Redis）
- **本番環境**: Momento（マネージドキャッシュサービス）

### 実装上の注意点

**momento/redis共通のインターフェース:**

CacheClientInterfaceを使用することで、momento/redisの違いを意識せずに実装できます。

```php
// ✅ 正しい実装（インターフェース経由）
public function __construct(
    private CacheClientManager $cacheClientManager,
) {}

$this->cacheClientManager->getCacheClient()->set($key, $value, $ttl);

// ❌ 間違った実装（直接Redisを使用）
use Illuminate\Support\Facades\Redis;
Redis::connection()->set($key, $value);
```

**例外: テストコードでのRedis直接使用:**

テストコードでは、データのセットアップのために`Redis::connection()`を直接使用することがあります。

```php
// テストコードでは許容される
Redis::connection()->zadd($key, $members);
```

## TTL（Time To Live）設定の方針

### TTLの基本的な考え方

キャッシュの有効期限は、データの性質と更新頻度に応じて設定します。

**TTL設定の目安:**

| データの種類 | TTL | 理由 |
|------------|-----|------|
| マスターデータ | 1日（86400秒） | 更新頻度が低い |
| ユーザーデータ | リクエスト中のみ（TTL設定なし） | リクエスト毎に更新される |
| ランキングデータ | イベント期間中 | イベント終了後も参照可能にする |
| 一時データ（セッション） | 2週間（1209600秒） | 一定期間後は不要 |
| ロック用キャッシュ | 数秒～数分 | 短時間の排他制御 |

### TTLの実装パターン

**パターン1: デフォルトTTLを使用（マスターデータ）**

```php
class MngCacheRepository
{
    private const DEFAULT_TTL_SECONDS = 86400; // 1日

    public function getOrCreateCache(string $cacheKey, callable $closure): mixed
    {
        $cachedData = $this->cacheClientManager->getCacheClient()->get($cacheKey);

        if ($cachedData !== null) {
            return $cachedData;
        }

        $data = $closure();
        $this->cacheClientManager->getCacheClient()->set($cacheKey, $data, self::DEFAULT_TTL_SECONDS);

        return $data;
    }
}
```

**パターン2: カスタムTTLを指定（一時データ）**

```php
class PvpCacheService
{
    private const TWO_WEEKS_SECONDS = 14 * 24 * 60 * 60; // 2週間

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
}
```

**パターン3: TTLなし（momentoのデフォルトTTL）**

momentoはTTL無制限がサポートされていないため、デフォルトで5年のTTLが設定されます。

```php
// MomentoCacheClient.php
public function __construct()
{
    // MomentoはTTL無制限がないのでデフォルトのTTLを5年としておく
    $itemDefaultTtlSeconds = 60 * 60 * 24 * 365 * 5;
    $client = new CacheClient($configuration, $authProvider, $itemDefaultTtlSeconds);
}

// TTLを指定せずにset
$this->cacheClientManager->getCacheClient()->set($key, $value); // 5年のTTL
```

## キャッシュ適用の判断基準

### キャッシュを適用すべきケース

以下の条件に当てはまる場合はキャッシュの適用を検討します。

1. **読み取り頻度が高い**
   - 同じデータが頻繁に読み取られる
   - 例: マスターデータ、バンドルデータ

2. **計算コストが高い**
   - データ生成に時間がかかる
   - 例: ランキング計算、集計処理

3. **データの更新頻度が低い**
   - データがほとんど変更されない
   - 例: マスターデータ、リリースバージョン情報

4. **外部API呼び出しがある**
   - 外部サービスへのリクエストを減らしたい
   - 例: BNID認証トークン

### キャッシュを適用すべきでないケース

以下の条件に当てはまる場合はキャッシュの適用を避けます。

1. **データの整合性が厳密に求められる**
   - キャッシュのずれが許容されない
   - 例: 残高、課金情報

2. **更新頻度が非常に高い**
   - データが頻繁に更新される
   - 例: リアルタイムのバトル状態

3. **メモリ使用量が大きい**
   - データサイズが大きく、キャッシュ効率が悪い
   - 例: 大量の画像データ

## エラーハンドリング

### キャッシュ取得失敗時の対応

キャッシュ取得に失敗した場合は、DBから取得して処理を継続します。

```php
public function getOrCreateCache(string $cacheKey, callable $closure): mixed
{
    $cachedData = $this->cacheClientManager->getCacheClient()->get($cacheKey);

    if ($cachedData !== null) {
        return $cachedData; // キャッシュヒット
    }

    // キャッシュミス時はDBから取得
    $data = $closure();
    $this->cacheClientManager->getCacheClient()->set($cacheKey, $data, self::DEFAULT_TTL_SECONDS);

    return $data;
}
```

### キャッシュ設定失敗時の対応

キャッシュ設定に失敗しても、アプリケーションは継続動作します。

```php
try {
    $this->cacheClientManager->getCacheClient()->set($key, $value, $ttl);
} catch (\Throwable $e) {
    // キャッシュ設定失敗はログに記録するが、処理は継続
    Log::warning('Cache set failed', ['key' => $key, 'error' => $e->getMessage()]);
}
```

## デバッグ時間変更機能への対応

### 問題: 未来時刻でキャッシュが作られる

デバッグ時間変更機能（`Carbon::setTestNow()`）が有効な場合、未来時刻でキャッシュが作成され、現在時刻でデータが取得できなくなる問題があります。

### 解決策: 実時刻を基準にする

```php
public function getCacheBaseTime(CarbonImmutable $now): CarbonImmutable
{
    // 固定時間設定がなかったり、本番環境ならそのまま返す
    if (!CarbonImmutable::hasTestNow() || app()->isProduction()) {
        return $now;
    }

    // 現在時刻のデータが十分含まれる程度の過去の時間にする
    $realNow = new \DateTimeImmutable('now', new \DateTimeZone(config('app.timezone')));
    return CarbonImmutable::createFromTimestamp(
        $realNow->getTimestamp(),
        config('app.timezone'),
    )->subMonths(6);
}
```

この関数を使用することで、デバッグ時間が変更されていても、実時刻を基準にキャッシュを扱えます。
