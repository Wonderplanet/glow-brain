# キャッシュパフォーマンスガイド

MasterRepositoryのキャッシュ戦略とパフォーマンス最適化のベストプラクティスを解説します。

## パフォーマンス特性

### キャッシュヒット時のレスポンス

| 操作 | 時間 | 説明 |
|------|------|------|
| APCu読み込み | ~0.1ms | メモリアクセス |
| Collection操作 | 1-10ms | PHPのフィルタリング |
| **合計** | **~10ms以下** | キャッシュヒット時 |

### キャッシュミス時のレスポンス

| 操作 | 時間 | 説明 |
|------|------|------|
| DB接続 | 1-5ms | MySQL/TiDB接続 |
| クエリ実行 | 5-50ms | データ量に依存 |
| Entity変換 | 5-20ms | toEntity()の実行 |
| キャッシュ保存 | 1-5ms | APCuへの書き込み |
| **合計** | **10-100ms** | キャッシュミス時 |

## ベンチマーク例

### 全件キャッシュ vs 条件付きクエリ

```php
// ベンチマーク: MstItem全件（1000件）
$start = microtime(true);

// パターン1: 全件キャッシュからフィルタ（推奨）
$items = $this->repository->getAll()
    ->filter(fn($e) => $e->getItemType() === 'TYPE_A');

$time1 = (microtime(true) - $start) * 1000; // ms

// パターン2: getByColumnで個別クエリ
$start = microtime(true);
$items = $this->repository->getByColumn('type', 'TYPE_A');
$time2 = (microtime(true) - $start) * 1000; // ms
```

**結果**:

| 実行回数 | パターン1（全件キャッシュ） | パターン2（個別クエリ） |
|---------|------------------------|---------------------|
| 1回目 | 50ms（キャッシュミス） | 30ms（クエリ実行） |
| 2回目 | 5ms（キャッシュヒット） | 5ms（キャッシュヒット） |
| 3回目 | 5ms（キャッシュヒット） | 5ms（キャッシュヒット） |

**考察**:
- 初回のみパターン2が速い（SQLでフィルタリング）
- 2回目以降はパターン1が有利（複数条件で使い回せる）
- 総合的にはパターン1が推奨（メモリ効率＋柔軟性）

## キャッシュヒット率の最大化

### パターン1: 全件キャッシュの使い回し

```php
class MstItemRepository
{
    // 全件キャッシュを作成
    public function getAll(): Collection
    {
        return $this->masterRepository->get(MstItem::class);
    }

    // 全件キャッシュから絞り込み
    public function getByType(string $type, CarbonImmutable $now): Collection
    {
        return $this->getAll()->filter(/* ... */);
    }

    // 同じ全件キャッシュを使い回す
    public function getByRarity(string $rarity, CarbonImmutable $now): Collection
    {
        return $this->getAll()->filter(/* ... */);
    }

    // 同じ全件キャッシュを使い回す
    public function getByIds(Collection $ids): Collection
    {
        return $this->getAll()->only($ids->toArray());
    }
}
```

**効果**:
- キャッシュは1つだけ（メモリ節約）
- 複数の検索条件に対応
- キャッシュヒット率 = 100%（2回目以降）

### パターン2: getDayActives()の使い回し

```php
class MstEventRepository
{
    // 当日有効なイベントをキャッシュ
    public function getAllActiveEvents(CarbonImmutable $now): Collection
    {
        return $this->masterRepository->getDayActives(MstEvent::class, $now)
            ->filter(/* ... */);
    }

    // 同じキャッシュを使い回す
    public function getActiveEvent(string $id, CarbonImmutable $now): ?Entity
    {
        return $this->getAllActiveEvents($now)->get($id);
    }

    // 同じキャッシュを使い回す
    public function getActiveEventsBySeriesId(string $seriesId, CarbonImmutable $now): Collection
    {
        return $this->getAllActiveEvents($now)
            ->filter(fn($e) => $e->getMstSeriesId() === $seriesId);
    }
}
```

**効果**:
- 期間指定マスタで有効データのみキャッシュ（メモリ節約）
- 複数の検索条件に対応
- 日跨ぎで自動更新（手動クリア不要）

## メモリ使用量の最適化

### 全件キャッシュ vs getDayActives()

**例: MstEvent（全500件、有効50件）**

```php
// パターン1: 全件キャッシュ
$allEvents = $this->masterRepository->get(MstEvent::class);
// メモリ使用量: 500件 × 約1KB = 500KB

// パターン2: getDayActives()
$activeEvents = $this->masterRepository->getDayActives(MstEvent::class, $now);
// メモリ使用量: 50件 × 約1KB = 50KB（10分の1）
```

**判断基準**:

| 条件 | 推奨方式 |
|------|---------|
| 有効データ < 10% | getDayActives() |
| データ量 > 1000件 | getDayActives() |
| 有効期間が短い | getDayActives() |
| 全データが常時有効 | 全件キャッシュ |
| データ量 < 100件 | 全件キャッシュ |

## Collection操作の最適化

### 1. ハッシュマップによる高速検索

```php
// Bad: O(n)の検索
$filtered = $entities->filter(function ($entity) use ($targetIds) {
    return $targetIds->contains($entity->getId());  // 毎回配列をスキャン
});

// Good: O(1)の検索
$targetIdsMap = $targetIds->mapWithKeys(fn($id) => [$id => true]);
$filtered = $entities->filter(function ($entity) use ($targetIdsMap) {
    return $targetIdsMap->has($entity->getId());  // ハッシュマップ検索
});
```

**ベンチマーク（1000件のデータ）**:

| 方式 | 時間 |
|------|------|
| contains() | 50ms |
| has() | 5ms |

### 2. 早期リターンでメモリ節約

```php
// Good: 空の場合は早期リターン
public function getByIds(Collection $ids): Collection
{
    if ($ids->isEmpty()) {
        return collect();
    }
    return $this->getAll()->only($ids->toArray());
}

// Bad: 無駄な処理
public function getByIds(Collection $ids): Collection
{
    return $this->getAll()->only($ids->toArray());  // 空でも全件取得
}
```

### 3. チェーンの順序を最適化

```php
// Good: 絞り込みを先に
return $this->getAll()
    ->filter(fn($e) => $e->getRarity() === 'SSR')  // 100件 → 10件
    ->filter(fn($e) => $this->isActiveEntity($e, $now));  // 10件 → 8件

// Bad: 重い処理を先に
return $this->getAll()
    ->filter(fn($e) => $this->isActiveEntity($e, $now))  // 100件 → 50件（重い）
    ->filter(fn($e) => $e->getRarity() === 'SSR');  // 50件 → 8件
```

### 4. only() vs filter()

```php
// Good: IDのみの場合はonly()
$entities = $this->getAll()->only(['id1', 'id2', 'id3']);  // O(n)

// Bad: filterは全件スキャン
$entities = $this->getAll()->filter(function ($entity) use ($ids) {
    return in_array($entity->getId(), $ids);  // O(n × m)
});
```

## APCuの設定最適化

### php.ini設定

```ini
; APCuを有効化
apc.enabled=1
apc.enable_cli=1

; 共有メモリサイズ（マスタデータに応じて調整）
apc.shm_size=128M

; TTL（MasterRepositoryのデフォルト: 86400秒 = 1日）
apc.ttl=86400

; ガベージコレクション
apc.gc_ttl=3600
```

### メモリサイズの見積もり

```php
// マスタデータのメモリ使用量を確認
$info = apcu_cache_info();
echo "使用メモリ: " . round($info['mem_size'] / 1024 / 1024, 2) . " MB\n";
echo "キャッシュ数: " . $info['num_entries'] . "\n";
```

**目安**:

| マスタテーブル数 | 推奨メモリサイズ |
|----------------|---------------|
| 10-20テーブル | 64MB |
| 20-50テーブル | 128MB |
| 50-100テーブル | 256MB |

## 実装のベストプラクティス

### チェックリスト

- [ ] 全件キャッシュを基本とする（`getAll()`）
- [ ] 期間指定マスタには`getDayActives()`を使う
- [ ] 全件キャッシュを使い回すメソッド設計
- [ ] ハッシュマップで高速検索（`mapWithKeys` + `has()`）
- [ ] 早期リターンでメモリ節約
- [ ] チェーンの順序を最適化（絞り込みを先に）
- [ ] `only()`でID検索を高速化
- [ ] APCuのメモリサイズを適切に設定

### アンチパターン

```php
// Bad: 毎回DBクエリ
public function getById(string $id): ?Entity
{
    return MstItem::find($id)?->toEntity();
}

// Bad: IDごとに個別キャッシュ
public function getById(string $id): ?Entity
{
    return $this->masterRepository->getByColumn(MstItem::class, 'id', $id)->first();
}

// Bad: 全件取得せずにSQLで絞り込み（キャッシュ分散）
public function getByType(string $type): Collection
{
    return $this->masterRepository->getByColumn(MstItem::class, 'type', $type);
}
```

## パフォーマンステスト例

```php
public function test_キャッシュヒット時のパフォーマンス()
{
    // Setup
    MstItem::factory()->count(1000)->create();

    // Exercise: 1回目（キャッシュミス）
    $start = microtime(true);
    $items1 = $this->repository->getAll();
    $time1 = (microtime(true) - $start) * 1000;

    // Exercise: 2回目（キャッシュヒット）
    $start = microtime(true);
    $items2 = $this->repository->getAll();
    $time2 = (microtime(true) - $start) * 1000;

    // Verify
    $this->assertGreaterThan(10, $time1);  // 初回は10ms以上
    $this->assertLessThan(10, $time2);     // 2回目は10ms未満
    $this->assertEquals($items1->count(), $items2->count());
}
```

## まとめ

### キャッシュ戦略の原則

1. **全件キャッシュを基本とする**: 1つのキャッシュで複数の検索条件に対応
2. **期間指定マスタには getDayActives()**: メモリ節約と自動更新
3. **Collection操作を最適化**: ハッシュマップ、早期リターン、チェーン順序
4. **APCuを適切に設定**: メモリサイズ、TTL、ガベージコレクション

この原則に従うことで、高速かつメモリ効率の良いマスタデータアクセスを実現できます。
