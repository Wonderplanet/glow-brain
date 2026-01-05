# 基本キャッシュ実装パターン

set/get/delete操作を使った基本的なキャッシュ実装パターンについて説明します。

## パターン概要

最もシンプルなキャッシュパターンで、キー・バリュー形式でデータを保存・取得・削除します。

**適用ケース:**
- マスターデータのキャッシュ
- APIレスポンス全体のキャッシュ
- 単一オブジェクトのキャッシュ
- 外部API呼び出し結果のキャッシュ

## 基本構造

### サービスクラスの構成

```php
<?php

declare(strict_types=1);

namespace App\Domain\{Domain}\Services;

use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Common\Utils\CacheKeyUtil;

class {Feature}CacheService
{
    private const DEFAULT_TTL_SECONDS = 86400; // 1日

    public function __construct(
        private CacheClientManager $cacheClientManager,
    ) {
    }

    // キャッシュ操作メソッド
}
```

## 実装パターン

### パターン1: getOrCreateパターン（最も一般的）

キャッシュが存在すれば取得、なければ生成してキャッシュに保存します。

```php
public function getOrCreateCache(string $cacheKey, callable $closure): mixed
{
    // キャッシュから取得を試みる
    $cachedData = $this->cacheClientManager->getCacheClient()->get($cacheKey);

    if ($cachedData !== null) {
        return $cachedData; // キャッシュヒット
    }

    // キャッシュミス時はDBから取得
    $data = $closure();

    // キャッシュに保存
    $this->cacheClientManager->getCacheClient()->set(
        $cacheKey,
        $data,
        self::DEFAULT_TTL_SECONDS
    );

    return $data;
}
```

**使用例:**
```php
$cacheKey = CacheKeyUtil::getMngMessageBundleKey('ja');
$messages = $this->cacheService->getOrCreateCache(
    $cacheKey,
    fn() => $this->repository->getAllMessages('ja')
);
```

### パターン2: 明示的なset/get（個別管理）

set/getを明示的に呼び出す場合。

```php
public function setData(string $key, mixed $data, ?int $ttl = null): void
{
    $this->cacheClientManager->getCacheClient()->set(
        $key,
        $data,
        $ttl ?? self::DEFAULT_TTL_SECONDS
    );
}

public function getData(string $key): mixed
{
    return $this->cacheClientManager->getCacheClient()->get($key);
}
```

**使用例:**
```php
// 保存
$cacheKey = CacheKeyUtil::getPvpOpponentStatusKey($seasonId, $userId);
$this->cacheService->setData($cacheKey, $opponentData, 1209600); // 2週間

// 取得
$cachedData = $this->cacheService->getData($cacheKey);
```

### パターン3: delete（キャッシュ削除）

キャッシュを削除する場合。

```php
public function deleteCache(string $cacheKey): void
{
    $this->cacheClientManager->getCacheClient()->del($cacheKey);

    // ログ出力（オプション）
    Log::debug('Cache deleted', ['cacheKey' => $cacheKey]);
}
```

**使用例:**
```php
$cacheKey = CacheKeyUtil::getMngMessageBundleKey('ja');
$this->cacheService->deleteCache($cacheKey);
```

### パターン4: exists（キャッシュ存在チェック）

キャッシュが存在するかチェックする場合。

```php
public function existsCache(string $cacheKey): bool
{
    return $this->cacheClientManager->getCacheClient()->exists($cacheKey);
}
```

**使用例:**
```php
$cacheKey = CacheKeyUtil::getPvpRankingCacheKey($seasonId);
if ($this->cacheService->existsCache($cacheKey)) {
    // キャッシュが存在する場合の処理
}
```

### パターン5: setIfNotExists（排他制御用）

キーが存在しない場合にのみキャッシュを設定します（排他制御用）。

```php
public function lock(string $lockKey, int $ttl = 10): bool
{
    return $this->cacheClientManager->getCacheClient()->setIfNotExists(
        $lockKey,
        true,
        $ttl
    );
}
```

**使用例:**
```php
$lockKey = "lock:process:{$userId}";
if ($this->cacheService->lock($lockKey, 30)) {
    try {
        // ロック取得成功、処理を実行
        $this->executeProcess();
    } finally {
        // ロック解除
        $this->cacheService->deleteCache($lockKey);
    }
} else {
    // ロック取得失敗、別プロセスが実行中
    throw new GameException(ErrorCode::PROCESS_LOCKED);
}
```

## データ型とシリアライズ

### サポートされるデータ型

CacheClientは以下のデータ型をサポートします。

```php
// プリミティブ型
$this->cacheClient->set($key, 'string');
$this->cacheClient->set($key, 123);
$this->cacheClient->set($key, 12.34);
$this->cacheClient->set($key, true);

// 配列
$this->cacheClient->set($key, ['key1' => 'value1', 'key2' => 'value2']);

// オブジェクト（DTOやEntity）
$this->cacheClient->set($key, new OpponentPvpStatusData(/* ... */));

// コレクション
$this->cacheClient->set($key, collect([/* ... */]));
```

### シリアライズの動作

**自動シリアライズ:**
- `set()`メソッドは自動的に`serialize()`を実行
- `get()`メソッドは自動的に`unserialize()`を実行

```php
// 内部実装（RedisCacheClient）
public function set(string $key, mixed $value, $ttl = null): void
{
    if (is_null($ttl)) {
        $connection->set($key, serialize($value)); // 自動シリアライズ
    } else {
        $connection->set($key, serialize($value), 'EX', $ttl);
    }
}

public function get(string $key): mixed
{
    $value = Redis::connection()->get($key);
    if ($value === null) {
        return null;
    }
    try {
        return unserialize($value); // 自動デシリアライズ
    } catch (\Throwable $e) {
        return $value; // シリアライズされていない場合はそのまま返す
    }
}
```

## TTL（有効期限）の扱い

### TTLの指定方法

```php
// TTLなし（momentoのデフォルト5年）
$this->cacheClient->set($key, $value);

// TTL指定（秒単位）
$this->cacheClient->set($key, $value, 86400); // 1日
$this->cacheClient->set($key, $value, 3600);  // 1時間
$this->cacheClient->set($key, $value, 60);    // 1分
```

### TTL関連操作

**TTLの確認:**
```php
$ttl = $this->cacheClient->ttl($key);
// -1: 無制限
// -2: キーが存在しない
// 正の整数: 残り秒数
```

**TTLの更新:**
```php
// 既存キャッシュのTTLを延長
$this->cacheClient->expire($key, 86400);
```

## エラーハンドリング

### キャッシュ取得失敗時

```php
public function getDataWithFallback(string $key, callable $fallback): mixed
{
    try {
        $cachedData = $this->cacheClientManager->getCacheClient()->get($key);
        if ($cachedData !== null) {
            return $cachedData;
        }
    } catch (\Throwable $e) {
        // キャッシュ取得失敗時はログに記録
        Log::warning('Cache get failed', ['key' => $key, 'error' => $e->getMessage()]);
    }

    // フォールバック処理
    return $fallback();
}
```

### キャッシュ設定失敗時

```php
public function setDataSafely(string $key, mixed $data, ?int $ttl = null): void
{
    try {
        $this->cacheClientManager->getCacheClient()->set($key, $data, $ttl);
    } catch (\Throwable $e) {
        // キャッシュ設定失敗はログに記録するが、処理は継続
        Log::warning('Cache set failed', ['key' => $key, 'error' => $e->getMessage()]);
    }
}
```

## ベストプラクティス

### DO（推奨）

✅ CacheClientManagerを使用する
✅ CacheKeyUtilでキーを一元管理する
✅ TTLを適切に設定する
✅ nullチェックを行う
✅ エラーハンドリングを実装する

```php
// ✅ 正しい実装
public function getData(string $userId): mixed
{
    $cacheKey = CacheKeyUtil::getUserDataKey($userId);
    $cachedData = $this->cacheClientManager->getCacheClient()->get($cacheKey);

    if ($cachedData !== null) {
        return $cachedData;
    }

    $data = $this->repository->getData($userId);
    $this->cacheClientManager->getCacheClient()->set($cacheKey, $data, 86400);

    return $data;
}
```

### DON'T（非推奨）

❌ Redis Facadeを直接使用する（テスト以外）
❌ ハードコードでキーを指定する
❌ TTLを考慮しない
❌ エラーハンドリングをしない

```php
// ❌ 間違った実装
use Illuminate\Support\Facades\Redis;

public function getData(string $userId): mixed
{
    $cacheKey = "user:{$userId}:data"; // ハードコード
    $cachedData = Redis::connection()->get($cacheKey); // Redis直接使用

    if ($cachedData === null) {
        $data = $this->repository->getData($userId);
        Redis::connection()->set($cacheKey, serialize($data)); // TTL未設定
        return $data;
    }

    return unserialize($cachedData);
}
```

## よくあるユースケース

### ユースケース1: APIレスポンス全体をキャッシュ

```php
public function getCachedRankingResponse(string $seasonId): Collection
{
    $cacheKey = CacheKeyUtil::getPvpRankingCacheKey($seasonId);
    $cachedData = $this->cacheClientManager->getCacheClient()->get($cacheKey);

    if ($cachedData !== null) {
        return $cachedData;
    }

    // ランキング計算（重い処理）
    $rankingData = $this->calculateRanking($seasonId);

    // 5分間キャッシュ
    $this->cacheClientManager->getCacheClient()->set($cacheKey, $rankingData, 300);

    return $rankingData;
}
```

### ユースケース2: 外部API呼び出し結果をキャッシュ

```php
public function getBnidUserId(string $code): string
{
    $cacheKey = CacheKeyUtil::getBnidUserIdKey($code);
    $cachedUserId = $this->cacheClientManager->getCacheClient()->get($cacheKey);

    if ($cachedUserId !== null) {
        return $cachedUserId;
    }

    // 外部API呼び出し
    $userId = $this->bnidApiClient->getUserId($code);

    // 1時間キャッシュ
    $this->cacheClientManager->getCacheClient()->set($cacheKey, $userId, 3600);

    return $userId;
}
```

### ユースケース3: マスターデータをキャッシュ

```php
public function getMessageBundle(string $language): array
{
    $cacheKey = CacheKeyUtil::getMngMessageBundleKey($language);

    return $this->getOrCreateCache(
        $cacheKey,
        fn() => $this->repository->getMessageBundle($language)
    );
}
```
