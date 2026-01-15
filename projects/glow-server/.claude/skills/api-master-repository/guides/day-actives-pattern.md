# 期間指定マスタの実装ガイド

`start_at`/`end_at`（または`start_date`/`end_date`）カラムを持つマスタデータの実装パターンを解説します。

## getDayActives()の使い方

### 基本コンセプト

期間指定マスタは、現在日時によって有効/無効が変わります。`getDayActives()`は、**その日（JSTベース）で1秒でも有効になるデータ**を効率的にキャッシュします。

### メソッドシグネチャ

```php
public function getDayActives(
    string $modelClass,
    CarbonImmutable $nowUtc,
    string $startAtColumn = 'start_at',
    string $endAtColumn = 'end_at',
): Collection
```

### タイムゾーンの扱い

**重要**: MasterRepositoryは内部で以下のタイムゾーン変換を行います。

```php
private const CACHE_ACTIVES_TIMEZONE = 'Asia/Tokyo';  // キャッシュの日付判定
private const TIMEZONE_DB = 'UTC';                     // DB保存時のタイムゾーン
```

**処理フロー**:
1. 引数の`$nowUtc`（UTC）を`Asia/Tokyo`に変換
2. JSTでの日付（Ymd）をキャッシュキーに含める
3. その日のJST 00:00:00 ~ 23:59:59 をUTCに変換してクエリ

### 実装例

```php
class MstEventRepository
{
    use MstRepositoryTrait;

    public function __construct(
        private MasterRepository $masterRepository,
    ) {}

    /**
     * start_atとend_at内の対象レコードを全て取得
     */
    public function getAllActiveEvents(CarbonImmutable $now): Collection
    {
        return $this->masterRepository->getDayActives(MstEvent::class, $now)
            ->filter(function (Entity $entity) use ($now) {
                return $this->isActiveEntity($entity, $now);
            });
    }

    /**
     * 特定IDの有効なイベントを取得
     */
    public function getActiveEvent(string $id, CarbonImmutable $now, bool $isThrowError = false): ?Entity
    {
        $entity = $this->getAllActiveEvents($now)->get($id);

        $this->throwMstNotFoundException(
            $isThrowError,
            MstEvent::class,
            $entity,
            ['id' => $id],
        );

        return $entity;
    }
}
```

## MstRepositoryTraitの活用

期間指定マスタのRepositoryでは、`MstRepositoryTrait`を使うと便利です。

### isActiveEntity()メソッド

```php
protected function isActiveEntity(object $entity, CarbonImmutable $now): bool
{
    return $now->between(
        $entity->{$this->startGetterMethod}(),
        $entity->{$this->endGetterMethod}(),
    );
}
```

**デフォルト設定**:
- `$startGetterMethod = 'getStartAt'`
- `$endGetterMethod = 'getEndAt'`

**カスタマイズ例**（start_date/end_dateの場合）:
```php
class MstItemRepository
{
    use MstRepositoryTrait;

    public function __construct(
        private MasterRepository $masterRepository,
    ) {
        $this->setStartGetterMethod('getStartDate');
        $this->setEndGetterMethod('getEndDate');
    }
}
```

## キャッシュの日跨ぎ動作

### キャッシュキー生成

```php
$cacheKey = $this->createCacheKey(
    $modelClass,
    sprintf(
        '%s:%s',
        self::CACHE_KEY_SUFFIX_MST_DAY_ACTIVES,  // 'mst_day_actives'
        $now->format('Ymd'),                      // '20250128'
    ),
);
```

例: `:mst_MstEvent:hash(mst_day_actives:20250128)`

### 日跨ぎの挙動

| 時刻（JST） | キャッシュキー | 動作 |
|------------|--------------|------|
| 2025-01-28 00:00:00 | `mst_day_actives:20250128` | 新規作成 |
| 2025-01-28 12:00:00 | `mst_day_actives:20250128` | キャッシュヒット |
| 2025-01-28 23:59:59 | `mst_day_actives:20250128` | キャッシュヒット |
| 2025-01-29 00:00:00 | `mst_day_actives:20250129` | 新規作成（別キャッシュ） |

日跨ぎすると自動的に新しいキャッシュが作成されるため、手動でのキャッシュクリアは不要です。

## テストでの検証

```php
public function test_getDayActives_日跨ぎすればキャッシュを作り直してデータ取得する()
{
    apcu_clear_cache();

    // JST: 2025-01-28 00:00:00
    $now = $this->fixTime('2025-01-27 15:00:00');
    $result = $this->masterRepository->getDayActives(MstEvent::class, $now);
    $cache1 = apcu_cache_info()['cache_list'];

    // JST: 2025-01-29 00:00:00
    $now = $this->fixTime('2025-01-28 15:00:00');
    $result = $this->masterRepository->getDayActives(MstEvent::class, $now);
    $cache2 = apcu_cache_info()['cache_list'];

    $this->assertCount(1, $cache1);  // 1日目のキャッシュ
    $this->assertCount(2, $cache2);  // 1日目 + 2日目のキャッシュ
}
```

## 期間指定マスタの実装チェックリスト

- [ ] Modelに`start_at`/`end_at`（または`start_date`/`end_date`）カラムが存在
- [ ] Entityに対応するgetterメソッドが実装されている
- [ ] RepositoryでMstRepositoryTraitをuse
- [ ] `getDayActives()`を使ってキャッシュ取得
- [ ] `isActiveEntity()`でさらに厳密な期間チェック（必要に応じて）
- [ ] カラム名が標準と異なる場合は`setStartGetterMethod()`等でカスタマイズ

## getDayActives()を使うべきケース

### 使うべき

- イベントマスタ（MstEvent）
- ミッションマスタ（期間限定）
- ガチャマスタ（OprGacha）
- キャンペーンマスタ

### 使わなくても良い

- アイテムマスタ（MstItem）: 全件キャッシュ + アプリケーション層フィルタで十分
- 期間が非常に長い、またはほぼ永続的なマスタ

**判断基準**: 日次で有効データが大きく変わる場合は`getDayActives()`が有効。そうでなければ全件キャッシュで十分です。

## パフォーマンス比較

| 方式 | メモリ使用量 | クエリ頻度 | 使用例 |
|------|------------|----------|--------|
| 全件キャッシュ | 大 | 1日1回 | MstItem（全1000件） |
| getDayActives | 中 | 1日1回 | MstEvent（有効50件/全500件） |
| 毎回クエリ | 小 | 毎リクエスト | （非推奨） |

期間指定マスタで有効データが少ない場合、`getDayActives()`により**メモリ使用量を削減**しつつ、**クエリ頻度も最小化**できます。
