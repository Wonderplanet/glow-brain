# キャッシュ戦略ガイド

MasterRepositoryは、APCu（Alternative PHP Cache User Cache）を使用してマスタデータを効率的にキャッシュします。

## キャッシュの仕組み

### 1. キャッシュストレージ

```php
// APCuUtilityでキャッシュを管理
Cache::store('apc')->get($key);
Cache::store('apc')->put($key, $value, $ttl);
```

APCuは、PHPのメモリ内キャッシュで、プロセス間で共有されます。Webサーバーのワーカープロセス全体で同じキャッシュデータを使用できるため、非常に高速です。

### 2. キャッシュキーの生成

```php
private function createCacheKey(string $modelClass, string $suffixKey): string
{
    return sprintf(
        ':%s_%s:%s',
        self::CACHE_KEY_PREFIX_MST,  // 'mst'
        $this->getTableName($modelClass),
        md5(
            config('database.connections.mst.database')
            . $suffixKey
        ),
    );
}
```

**キャッシュキーの構造**:
- プレフィックス: `mst`
- テーブル名: モデルクラスから抽出
- ハッシュ: データベース名 + suffixKey（SQL文やカスタムキー）のMD5

例: `:mst_MstItem:d41d8cd98f00b204e9800998ecf8427e`

### 3. TTL（Time To Live）設定

```php
private const DEFAULT_TTL_SECONDS = 86400; // 1日
```

デフォルトでは1日（86400秒）でキャッシュが自動削除されます。

**TTL設計の理由**:
- マスタデータは基本的に頻繁に変更されない
- 1日のTTLで、デプロイ後も古いデータが残る問題を最小化
- マスタデータ更新時は、再デプロイまたはキャッシュクリアで対応

## キャッシュ取得パターン

### パターン1: 全件キャッシュ（最も一般的）

```php
public function getAll(): Collection
{
    return $this->masterRepository->get(MstItem::class);
}
```

**特徴**:
- テーブル全件を取得してキャッシュ
- IDをキーとした連想配列で保存: `['item_1' => Entity, 'item_2' => Entity]`
- キャッシュキーはSQLクエリから自動生成

**使用例**:
```php
$items = $repository->getAll();
$item = $items->get('item_1');  // O(1)で取得
```

### パターン2: カラム条件指定

```php
public function getByColumn(string $modelClass, string $column, mixed $value): Collection
{
    return $this->masterRepository->getByColumn(
        MstItem::class,
        'type',
        ItemType::RANK_UP_MATERIAL->value,
    );
}
```

**特徴**:
- WHERE句でフィルタリングしたクエリをキャッシュ
- キャッシュキーは生成されたSQLから自動生成
- 異なる条件値ごとに別のキャッシュが作成される

### パターン3: 複数カラム条件

```php
public function getByColumns(string $modelClass, array $conditions): Collection
{
    return $this->masterRepository->getByColumns(
        MstItem::class,
        [
            'type' => ItemType::RANK_UP_MATERIAL->value,
            'rarity' => 'SSR',
        ]
    );
}
```

**特徴**:
- 複数のWHERE条件を組み合わせ
- 内部でBuilderを構築してgetByBuilder()を呼び出す

### パターン4: 期間指定マスタの日次キャッシュ

```php
public function getDayActives(
    string $modelClass,
    CarbonImmutable $nowUtc,
    string $startAtColumn = 'start_at',
    string $endAtColumn = 'end_at',
): Collection
```

**特徴**:
- 現在日時が含まれる1日間で有効になるデータのみキャッシュ
- キャッシュキーに日付（Ymd形式）を含む: `mst_day_actives:20250128`
- 日跨ぎで自動的に新しいキャッシュが作成される
- タイムゾーンは`Asia/Tokyo`でJST基準

**使用例**:
```php
// JST 2025-01-28 00:00:00 ~ 23:59:59 の間で有効なイベント
$events = $this->masterRepository->getDayActives(MstEvent::class, $now);
```

## キャッシュヒット率を高めるTips

### 1. 全件キャッシュを活用

可能な限り`get()`で全件キャッシュを使い、アプリケーション層でフィルタリングします。

```php
// Good: 全件キャッシュを使い回す
public function getByIds(Collection $ids): Collection
{
    return $this->getAll()->only($ids->toArray());
}

// Bad: IDごとに個別キャッシュを作成
public function getById(string $id): ?Entity
{
    return $this->masterRepository->getByColumn(
        MstItem::class,
        'id',
        $id
    )->first();
}
```

### 2. Repositoryで共通メソッドを定義

複数の条件検索をRepositoryで実装し、内部で全件キャッシュを使い回します。

```php
public function getActiveItemsByItemType(string $itemType, CarbonImmutable $now): Collection
{
    return $this->getAll()
        ->filter(function (Entity $entity) use ($itemType, $now) {
            return $entity->getItemType() === $itemType
                && $now->between($entity->getStartDate(), $entity->getEndDate());
        });
}
```

### 3. getDayActives()を期間指定マスタに使う

start_at/end_atカラムを持つマスタは、`getDayActives()`を使うことで当日有効なデータのみキャッシュできます。

```php
// MstEventRepository
public function getAllActiveEvents(CarbonImmutable $now): Collection
{
    return $this->masterRepository->getDayActives(MstEvent::class, $now)
        ->filter(function (Entity $entity) use ($now) {
            return $this->isActiveEntity($entity, $now);
        });
}
```

## キャッシュクリア方法

開発環境やテスト環境でキャッシュをクリアする場合:

```php
// テストコード内
apcu_clear_cache();

// または
Cache::store('apc')->flush();
```

本番環境では、マスタデータ更新後にWebサーバーの再起動またはPHP-FPMのリロードでキャッシュがクリアされます。

## パフォーマンス特性

- **キャッシュヒット時**: ～0.1ms（メモリアクセス）
- **キャッシュミス時**: 10～100ms（DB取得 + Entity変換 + キャッシュ保存）
- **全件キャッシュのフィルタ**: 1～10ms（PHPのコレクション操作）

全件キャッシュを使い回すことで、1つのキャッシュで複数の検索条件に対応できるため、メモリ効率とパフォーマンスの両面で有利です。
