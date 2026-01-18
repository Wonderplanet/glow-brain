# リポジトリパターンガイド

マスタデータリポジトリの実装パターンとベストプラクティスを解説します。

## 基本構造

### Repositoryクラスの責務

```php
class MstXxxRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {}

    // 1. データ取得メソッド
    public function getAll(): Collection { }
    public function getById(string $id): ?Entity { }

    // 2. ビジネスロジックメソッド
    public function getActiveItems(CarbonImmutable $now): Collection { }
    public function getByType(string $type): Collection { }

    // 3. バリデーション・エラーハンドリング
    public function getOrThrow(string $id): Entity { }
}
```

**ポイント**:
- MasterRepositoryをDI（Dependency Injection）で注入
- データ取得は必ずMasterRepositoryを経由
- ビジネスロジックはRepository層で実装
- 直接DBアクセスは禁止（Eloquentクエリは使わない）

## MstRepositoryTraitの活用

期間指定マスタや共通処理がある場合は、`MstRepositoryTrait`を使います。

### Traitの機能

```php
trait MstRepositoryTrait
{
    protected string $startGetterMethod = 'getStartAt';
    protected string $endGetterMethod = 'getEndAt';

    // 期間内判定
    protected function isActiveEntity(object $entity, CarbonImmutable $now): bool;

    // エラーハンドリング
    public function throwMstNotFoundException(
        bool $isThrowError,
        string $modelClass,
        mixed $target,
        array|string $conditions
    ): void;

    // テーブル名取得
    protected function getTableNameByModelClass(string $modelClass): string;

    // whereInフィルタ
    protected function filterWhereIn(Collection $entities, string $getterMethod, Collection $values): Collection;
}
```

### 使用例

```php
class MstEventRepository
{
    use MstRepositoryTrait;

    public function __construct(
        private MasterRepository $masterRepository,
    ) {}

    public function getActiveEvent(string $id, CarbonImmutable $now, bool $isThrowError = false): ?Entity
    {
        $entity = $this->getAllActiveEvents($now)->get($id);

        // Traitのエラーハンドリング
        $this->throwMstNotFoundException(
            $isThrowError,
            MstEvent::class,
            $entity,
            ['id' => $id],
        );

        return $entity;
    }

    public function getAllActiveEvents(CarbonImmutable $now): Collection
    {
        return $this->masterRepository->getDayActives(MstEvent::class, $now)
            ->filter(function (Entity $entity) use ($now) {
                // Traitの期間判定
                return $this->isActiveEntity($entity, $now);
            });
    }
}
```

## エラーハンドリングパターン

### パターン1: nullableで返す（デフォルト）

```php
public function getById(string $id): ?Entity
{
    return $this->getAll()->get($id);
}
```

**特徴**:
- データが存在しない場合は`null`を返す
- 呼び出し側でnullチェックが必要

### パターン2: 例外を投げる

```php
public function getActiveItemById(string $id, CarbonImmutable $now, bool $isThrowError = false): ?Entity
{
    $entity = $this->getAll()->get($id);

    if ($entity !== null && !$now->between($entity->getStartDate(), $entity->getEndDate())) {
        $entity = null;
    }

    if ($isThrowError && is_null($entity)) {
        throw new GameException(
            ErrorCode::MST_NOT_FOUND,
            sprintf('mst_items record is not found. (id: %s)', $id),
        );
    }

    return $entity;
}
```

**特徴**:
- `$isThrowError = true`の場合、データが存在しない場合に例外を投げる
- APIレイヤーでのエラーハンドリングが簡潔になる

**使用例**:
```php
// サービス層
$item = $this->mstItemRepository->getActiveItemById($itemId, $now, isThrowError: true);
// データが存在しない場合はGameExceptionがthrowされる
```

### パターン3: Traitを使ったエラーハンドリング

```php
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
```

**特徴**:
- エラーメッセージの統一性を保てる
- テーブル名を自動取得してエラーメッセージに含める

## 検索メソッドの実装パターン

### パターン1: 全件キャッシュからフィルタ（推奨）

```php
public function getByType(string $type, CarbonImmutable $now): Collection
{
    return $this->getAll()
        ->filter(function (Entity $entity) use ($type, $now) {
            return $entity->getItemType() === $type
                && $now->between($entity->getStartDate(), $entity->getEndDate());
        });
}
```

**メリット**:
- 1つのキャッシュを使い回すため、メモリ効率が良い
- キャッシュヒット率が高い

### パターン2: getByColumnを使う

```php
public function getByType(string $type): Collection
{
    return $this->masterRepository->getByColumn(
        MstItem::class,
        'type',
        $type,
    );
}
```

**メリット**:
- SQLレベルでフィルタリング
- データ量が非常に多い場合はメモリ節約になる

**デメリット**:
- 条件ごとに別のキャッシュが作成される
- キャッシュミス時のオーバーヘッドが大きい

### パターン3: 複数条件でgetByColumns

```php
public function getByTypeAndRarity(string $type, string $rarity): Collection
{
    return $this->masterRepository->getByColumns(
        MstItem::class,
        [
            'type' => $type,
            'rarity' => $rarity,
        ]
    );
}
```

**使用例**: 非常に特殊な条件で、全件キャッシュから絞り込むよりもSQLで絞った方が効率的な場合

## 実装のベストプラクティス

### 1. 全件取得メソッドを必ず実装

```php
/**
 * @return Collection<string, Entity> key: id, value: Entity
 */
public function getAll(): Collection
{
    return $this->masterRepository->get(MstItem::class);
}
```

他のメソッドから`getAll()`を使い回すことで、キャッシュを効率的に利用できます。

### 2. IDによる取得は全件キャッシュから

```php
// Good
public function getByIds(Collection $ids): Collection
{
    if ($ids->isEmpty()) {
        return collect();
    }
    return $this->getAll()->only($ids->toArray());
}

// Bad: IDごとに個別キャッシュを作る
public function getByIds(Collection $ids): Collection
{
    return $ids->map(function ($id) {
        return $this->masterRepository->getByColumn(MstItem::class, 'id', $id);
    })->flatten();
}
```

### 3. 複雑な条件はRepository層で

```php
public function getRankUpMaterialByColor(string $color, CarbonImmutable $now, bool $isThrowError = false): Entity
{
    $entities = $this->getAll()
        ->filter(function (Entity $entity) use ($color, $now) {
            return $entity->getItemType() === ItemType::RANK_UP_MATERIAL->value
                && $entity->getEffectValue() === $color
                && $now->between($entity->getStartDate(), $entity->getEndDate());
        });

    if ($isThrowError && $entities->isEmpty()) {
        throw new GameException(
            ErrorCode::MST_NOT_FOUND,
            sprintf(
                'mst_items record is not found. (type: %s, effectValue: %s)',
                ItemType::RANK_UP_MATERIAL->value,
                $color
            ),
        );
    }

    return $entities->first();
}
```

### 4. 型アノテーションを明確に

```php
/**
 * @return Collection<string, Entity> key: id, value: Entity
 */
public function getAll(): Collection

/**
 * @param Collection<string> $ids
 * @return Collection<string, Entity>
 */
public function getByIds(Collection $ids): Collection
```

PHPStanでの静的解析がしやすくなります。

## 命名規則

| メソッド名 | 戻り値 | 説明 |
|-----------|--------|------|
| `getAll()` | Collection | 全件取得 |
| `getById(string $id)` | ?Entity | ID単体取得 |
| `getByIds(Collection $ids)` | Collection | 複数ID取得 |
| `getActiveXxx()` | ?Entity or Collection | 期間有効なデータ |
| `getByColumn()` | Collection | カラム条件指定 |
| `getXxxOrThrow()` | Entity | 見つからない場合は例外 |

## 依存関係の設計

```
Controller/Service
    ↓
Repository (MstXxxRepository)
    ↓
MasterRepository (Infrastructure層)
    ↓
APCuCache/Database
```

**禁止事項**:
- RepositoryからEloquentクエリを直接実行しない
- Controllerから直接MasterRepositoryを使用しない
- Entityを直接new してはいけない（Modelの`toEntity()`経由）

Repository層を経由することで、キャッシュ戦略の変更やデータソースの切り替えに柔軟に対応できます。
