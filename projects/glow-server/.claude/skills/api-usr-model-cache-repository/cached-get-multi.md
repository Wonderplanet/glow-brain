# MultiCacheRepository - cachedGet系メソッド

## 概要

UsrModelMultiCacheRepositoryで提供される、2レコード以上のテーブル向けのキャッシュ取得メソッド群です。

**用途:**
- 1ユーザーあたり2レコード以上のテーブル（usr_unit, usr_item, usr_pvp等）
- 条件に応じてDBアクセスを最適化

## 提供メソッド一覧

| メソッド | 用途 | 戻り値 |
|---------|------|--------|
| `cachedGetAll()` | 全レコード取得 | Collection |
| `cachedGetMany()` | 複数レコード取得（条件付き） | Collection |
| `cachedGetOneWhere()` | 1レコード取得（条件付き） | Model or null |

## cachedGetAll - 全レコード取得

### メソッドシグネチャ

```php
protected function cachedGetAll(string $usrUserId): Collection
```

### 動作フロー

```
1. 他人のデータを取得する場合は、毎回DBから取得
2. すでに全取得済みであれば、キャッシュを返す
3. そうでない場合は、DBから全レコードを取得
   → markAllFetched($usrUserId)
   → syncModels($models)
   → キャッシュから取得して返す
```

### 実装例

```php
public function getList(string $usrUserId): Collection
{
    return $this->cachedGetAll($usrUserId);
}
```

### 使用ケース

- ✅ テーブル内の全レコードを取得したい場合
- ✅ レコード数が多くない場合（数百件程度）

**例:**
```php
// usr_artworkの全レコードを取得
$usrArtworks = $this->usrArtworkRepository->getList($usrUserId);
```

## cachedGetMany - 複数レコード取得（条件付き）

### メソッドシグネチャ

```php
protected function cachedGetMany(
    string $usrUserId,
    callable $cacheCallback,
    ?int $expectedCount,
    callable $dbCallback
): Collection
```

### 引数説明

| 引数 | 型 | 説明 |
|------|---|------|
| `$usrUserId` | string | 取得したいデータを所持しているユーザーのID |
| `$cacheCallback` | callable | キャッシュからデータを取得するフィルタリング関数 |
| `$expectedCount` | int\|null | 取得したいデータの数。指定した場合、その数分のデータがキャッシュにあればDBアクセスしない |
| `$dbCallback` | callable | DBクエリを実行する関数（戻り値はCollection） |

### 動作フロー

```
1. 他人のデータを取得する場合は、毎回DBから取得
2. cacheCallbackでキャッシュをフィルタリング
3. expectedCountが指定され、キャッシュに期待数のデータがあれば返す
4. そうでない場合は、dbCallbackでDBから取得
   → syncModels($models)
   → キャッシュから取得して返す
```

### 実装パターン

#### パターン1: expectedCountを指定（推奨）

取得するレコード数が明確な場合、expectedCountを指定することでDBアクセスを最適化できます。

```php
public function getByMstArtworkIds(string $usrUserId, Collection $mstArtworkIds): Collection
{
    if ($mstArtworkIds->isEmpty()) {
        return collect();
    }

    return $this->cachedGetMany(
        $usrUserId,
        cacheCallback: function (Collection $cache) use ($mstArtworkIds) {
            return $cache->filter(function (UsrArtworkInterface $model) use ($mstArtworkIds) {
                return $mstArtworkIds->contains($model->getMstArtworkId());
            });
        },
        expectedCount: $mstArtworkIds->count(),  // 期待する取得数
        dbCallback: function () use ($usrUserId, $mstArtworkIds) {
            return UsrArtwork::query()
                ->where('usr_user_id', $usrUserId)
                ->whereIn('mst_artwork_id', $mstArtworkIds)
                ->get();
        },
    );
}
```

**メリット:**
- キャッシュに期待数のデータがあれば、DBアクセスしない
- 効率的なキャッシュ利用

#### パターン2: expectedCountを未指定

取得するレコード数が不明な場合、expectedCountをnullに設定します。

```php
public function getListByMstExchangeIds(string $usrUserId, Collection $mstExchangeIds): Collection
{
    return $this->cachedGetMany(
        $usrUserId,
        expectedCount: null,  // 取得数が不明
        cacheCallback: function (Collection $cache) use ($mstExchangeIds) {
            return $cache->filter(
                function (UsrExchangeLineupInterface $model) use ($mstExchangeIds) {
                    return $mstExchangeIds->contains($model->getMstExchangeId());
                }
            );
        },
        dbCallback: function () use ($usrUserId, $mstExchangeIds) {
            return UsrExchangeLineup::query()
                ->where('usr_user_id', $usrUserId)
                ->whereIn('mst_exchange_id', $mstExchangeIds)
                ->get();
        }
    );
}
```

**注意:**
- expectedCountを未指定の場合、毎回DBアクセスする可能性がある
- 取得数が明確な場合は、expectedCountを指定する方が効率的

### 使用ケース

- ✅ 特定の条件でフィルタリングして複数レコードを取得したい
- ✅ 取得するレコード数が明確（expectedCount指定推奨）
- ✅ DBアクセスを最適化したい

## cachedGetOneWhere - 1レコード取得（条件付き）

### メソッドシグネチャ

```php
protected function cachedGetOneWhere(
    string $usrUserId,
    string $columnKey,
    mixed $columnValue,
    callable $dbCallback
): mixed
```

### 引数説明

| 引数 | 型 | 説明 |
|------|---|------|
| `$usrUserId` | string | 取得したいデータを所持しているユーザーのID |
| `$columnKey` | string | 検索対象のカラム名 |
| `$columnValue` | mixed | 検索対象のカラム値 |
| `$dbCallback` | callable | DBクエリを実行する関数（戻り値はModel or null） |

### 動作フロー

```
1. cachedGetManyを内部で呼び出し
   - cacheCallbackでcolumnKeyとcolumnValueでフィルタリング
   - expectedCountは1を指定
2. 取得結果が2個以上の場合はエラー
3. 1個以下の場合は、最初の要素を返す
```

### 実装例

#### 例1: mst_artwork_idで検索

```php
public function getByMstArtworkId(
    string $usrUserId,
    string $mstArtworkId,
): ?UsrArtworkInterface {
    return $this->cachedGetOneWhere(
        $usrUserId,
        'mst_artwork_id',  // カラム名
        $mstArtworkId,     // カラム値
        function () use ($usrUserId, $mstArtworkId) {
            return UsrArtwork::query()
                ->where('usr_user_id', $usrUserId)
                ->where('mst_artwork_id', $mstArtworkId)
                ->first();
        },
    );
}
```

#### 例2: sys_pvp_season_idで検索

```php
public function getBySysPvpSeasonId(
    string $usrUserId,
    string $sysPvpSeasonId,
    bool $isThrowError = false
): ?UsrPvpInterface {
    $model = $this->cachedGetOneWhere(
        $usrUserId,
        'sys_pvp_season_id',
        $sysPvpSeasonId,
        function () use ($usrUserId, $sysPvpSeasonId) {
            return UsrPvp::query()
                ->where('usr_user_id', $usrUserId)
                ->where('sys_pvp_season_id', $sysPvpSeasonId)
                ->first();
        },
    );

    if ($model === null && $isThrowError) {
        throw new GameException(
            ErrorCode::PVP_SESSION_NOT_FOUND,
            "User PVP information not found for user: {$usrUserId}, season: {$sysPvpSeasonId}"
        );
    }

    return $model;
}
```

### 使用ケース

- ✅ 特定のカラム値で1レコードのみ取得したい
- ✅ ユニークキーでの検索
- ✅ 取得結果が最大1レコードであることが保証されている

## dbSelectAllのオーバーライド

デフォルトでは、`usr_user_id`カラムでDB全レコード取得しますが、カスタマイズが必要な場合はオーバーライドしてください。

```php
protected function dbSelectAll(string $usrUserId): Collection
{
    return $this->modelClass::query()
        ->where('usr_user_id', $usrUserId)
        ->get();
}
```

## メソッド選択のフローチャート

```
取得したいレコード数は？
├── 全レコード → cachedGetAll()
├── 複数レコード（条件付き）
│   └── 取得数が明確？
│       ├── はい → cachedGetMany() + expectedCount指定
│       └── いいえ → cachedGetMany() + expectedCount未指定
└── 1レコード（条件付き）
    └── 特定カラムで検索？
        ├── はい → cachedGetOneWhere()
        └── いいえ → cachedGetMany() + expectedCount: 1
```

## パフォーマンス比較

| メソッド | expectedCount | キャッシュヒット時 | キャッシュミス時 |
|---------|--------------|------------------|-----------------|
| cachedGetAll() | - | 0回DBアクセス | 1回DBアクセス（全取得） |
| cachedGetMany() | 指定あり | 0回DBアクセス | 1回DBアクセス |
| cachedGetMany() | null | 毎回DBアクセス | 1回DBアクセス |
| cachedGetOneWhere() | 1 | 0回DBアクセス | 1回DBアクセス |

**推奨:**
- 取得数が明確な場合は、expectedCountを指定する
- 全レコード取得する場合は、cachedGetAll()を使用

## まとめ

- **cachedGetAll()**: 全レコード取得（全取得済みフラグで最適化）
- **cachedGetMany()**: 複数レコード取得（expectedCountで最適化）
- **cachedGetOneWhere()**: 1レコード取得（特定カラムで検索）
- **expectedCount**: 取得数が明確な場合は指定推奨
