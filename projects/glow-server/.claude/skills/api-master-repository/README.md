# master-repository スキル

マスタデータ（mst/opr DB）のリポジトリ実装を支援するスキルです。APCuキャッシュを活用した効率的なマスタデータ取得とEntity変換パターンを提供します。

## スキルの目的

- MasterRepositoryを使ったマスタデータアクセスの標準化
- APCuキャッシュを活用した高速なデータ取得
- 期間指定マスタ（start_at/end_at）の効率的な実装
- Model/Entity/Repositoryの実装パターンの統一

## 対象範囲

### 含まれるもの

- MasterRepositoryの使い方（get, getByColumn, getDayActives）
- マスタデータのキャッシュ戦略
- Model/Entity/Repositoryの実装パターン
- 期間指定マスタの実装（start_at/end_at, start_date/end_date）
- 複雑な検索条件の実装
- パフォーマンス最適化のベストプラクティス

### 含まれないもの

- ユーザーデータ（usr/log DB）のリポジトリ実装 → `usr-model-manager`スキルを参照
- Eloquentモデルの詳細な使い方 → Laravelの公式ドキュメントを参照
- APCuの詳細な設定 → PHP公式ドキュメントを参照

## ディレクトリ構成

```
master-repository/
├── skill.md                           # スキルのエントリポイント
├── README.md                          # このファイル
├── guides/                            # 詳細ガイド
│   ├── cache-strategy.md              # キャッシュ戦略の解説
│   ├── day-actives-pattern.md         # getDayActives()の使い方
│   └── repository-pattern.md          # リポジトリパターンの実装
├── patterns/                          # 実装パターン
│   ├── basic-implementation.md        # 基本的なModel/Entity/Repository実装
│   ├── timed-master.md                # 期間指定マスタの実装
│   └── complex-queries.md             # 複雑な検索条件の実装
└── examples/                          # 実装例
    ├── mst-item.md                    # MstItemの完全実装例
    ├── mst-event.md                   # MstEventの完全実装例（期間指定）
    └── cache-performance.md           # パフォーマンス最適化ガイド
```

## クイックスタート

### 1. 基本的なマスタデータの実装

```php
// Model
class MstItem extends MstModel
{
    public function toEntity(): MstItemEntity
    {
        return new MstItemEntity($this->id, $this->name, ...);
    }
}

// Entity
class MstItemEntity
{
    public function __construct(
        private string $id,
        private string $name,
    ) {}

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
}

// Repository
class MstItemRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {}

    public function getAll(): Collection
    {
        return $this->masterRepository->get(MstItem::class);
    }
}
```

詳細: [patterns/basic-implementation.md](patterns/basic-implementation.md)

### 2. 期間指定マスタの実装

```php
class MstEventRepository
{
    use MstRepositoryTrait;

    public function getAllActiveEvents(CarbonImmutable $now): Collection
    {
        return $this->masterRepository->getDayActives(MstEvent::class, $now)
            ->filter(fn($entity) => $this->isActiveEntity($entity, $now));
    }
}
```

詳細: [patterns/timed-master.md](patterns/timed-master.md)

### 3. 複雑な検索条件の実装

```php
public function getRankUpMaterialByColor(string $color, CarbonImmutable $now): Entity
{
    return $this->getAll()
        ->filter(function (Entity $entity) use ($color, $now) {
            return $entity->getItemType() === ItemType::RANK_UP_MATERIAL->value
                && $entity->getEffectValue() === $color
                && $now->between($entity->getStartDate(), $entity->getEndDate());
        })
        ->first();
}
```

詳細: [patterns/complex-queries.md](patterns/complex-queries.md)

## キャッシュ戦略

### 全件キャッシュ（推奨）

```php
// 1つのキャッシュを複数の検索条件で使い回す
public function getAll(): Collection
{
    return $this->masterRepository->get(MstItem::class);
}

public function getByType(string $type): Collection
{
    return $this->getAll()->filter(fn($e) => $e->getType() === $type);
}

public function getByRarity(string $rarity): Collection
{
    return $this->getAll()->filter(fn($e) => $e->getRarity() === $rarity);
}
```

**メリット**:
- キャッシュヒット率100%（2回目以降）
- メモリ効率が良い（1つのキャッシュで複数検索に対応）
- 柔軟な検索条件

詳細: [guides/cache-strategy.md](guides/cache-strategy.md)

### 期間指定マスタのキャッシュ

```php
// 当日有効なデータのみキャッシュ（日跨ぎで自動更新）
public function getAllActiveEvents(CarbonImmutable $now): Collection
{
    return $this->masterRepository->getDayActives(MstEvent::class, $now);
}
```

**メリット**:
- 有効データのみキャッシュ（メモリ節約）
- 日跨ぎで自動的に新しいキャッシュ作成
- 手動でのキャッシュクリア不要

詳細: [guides/day-actives-pattern.md](guides/day-actives-pattern.md)

## 実装パターン一覧

### Model実装

- [ ] MstModelを継承
- [ ] @propertyアノテーションでカラム型を明示
- [ ] $castsで型キャストを定義
- [ ] toEntity()メソッドでEntity変換

詳細: [patterns/basic-implementation.md](patterns/basic-implementation.md#model実装)

### Entity実装

- [ ] privateプロパティで不変性を保証
- [ ] getterのみ実装（setterは作らない）
- [ ] nullable型を明示（?string等）
- [ ] ビジネスロジックメソッドを追加

詳細: [patterns/basic-implementation.md](patterns/basic-implementation.md#entity実装)

### Repository実装

- [ ] MasterRepositoryをDI
- [ ] getAll()メソッドを必ず実装
- [ ] 型アノテーション（@return等）を記述
- [ ] 全件キャッシュを使い回す実装

詳細: [patterns/basic-implementation.md](patterns/basic-implementation.md#repository実装)

### 期間指定マスタ

- [ ] MstRepositoryTraitをuse
- [ ] getDayActives()または全件キャッシュを選択
- [ ] isActiveEntity()で期間判定
- [ ] start_date/end_dateの場合はsetStartGetterMethod()でカスタマイズ

詳細: [patterns/timed-master.md](patterns/timed-master.md)

## パフォーマンスTips

### 1. 全件キャッシュを使い回す

```php
// Good: 1つのキャッシュで複数検索
public function getByIds(Collection $ids): Collection
{
    return $this->getAll()->only($ids->toArray());
}

// Bad: IDごとに個別キャッシュ
public function getByIds(Collection $ids): Collection
{
    return $ids->map(fn($id) => $this->getByColumn('id', $id));
}
```

### 2. ハッシュマップで高速検索

```php
// Good: O(1)検索
$targetIds = $ids->mapWithKeys(fn($id) => [$id => true]);
$filtered = $entities->filter(fn($e) => $targetIds->has($e->getId()));

// Bad: O(n)検索
$filtered = $entities->filter(fn($e) => $ids->contains($e->getId()));
```

### 3. 早期リターンでメモリ節約

```php
public function getByIds(Collection $ids): Collection
{
    if ($ids->isEmpty()) {
        return collect();  // 早期リターン
    }
    return $this->getAll()->only($ids->toArray());
}
```

詳細: [examples/cache-performance.md](examples/cache-performance.md)

## よくある質問

### Q1: 全件キャッシュとgetByColumn、どちらを使うべきか？

**A**: 基本的には全件キャッシュ（`get()`）を推奨します。

理由:
- 1つのキャッシュで複数の検索条件に対応できる
- キャッシュヒット率が高い
- メモリ効率が良い

例外:
- データ量が非常に多い（10,000件以上）
- 検索条件が固定で、他の条件では使わない

### Q2: getDayActives()を使うべきケースは？

**A**: 期間指定マスタで、有効データが全体の10%未満の場合に推奨します。

使用例:
- イベントマスタ（MstEvent）
- ガチャマスタ（OprGacha）
- 期間限定ミッション

使わない例:
- アイテムマスタ（MstItem）→ ほとんどが常時有効
- 固定マスタ → 期間指定なし

詳細: [guides/day-actives-pattern.md](guides/day-actives-pattern.md#getDayActivesを使うべきケース)

### Q3: MstRepositoryTraitは必須か？

**A**: 期間指定マスタの場合は推奨、それ以外は任意です。

Traitのメリット:
- `isActiveEntity()`で期間判定を統一
- `throwMstNotFoundException()`でエラーハンドリングを統一
- `filterWhereIn()`で高速なIN検索

詳細: [guides/repository-pattern.md](guides/repository-pattern.md#mstrepositorytraitの活用)

### Q4: キャッシュはいつクリアされる？

**A**: 以下のタイミングでクリアされます。

1. TTL（Time To Live）経過時: デフォルト1日（86400秒）
2. Webサーバー再起動時
3. PHP-FPMリロード時
4. 手動クリア（`apcu_clear_cache()`）

日跨ぎの場合:
- `getDayActives()`は自動的に新しいキャッシュを作成（手動クリア不要）

詳細: [guides/cache-strategy.md](guides/cache-strategy.md#キャッシュクリア方法)

## 実装例

### 完全な実装例

- [MstItem実装](examples/mst-item.md): 基本的なマスタデータの実装
- [MstEvent実装](examples/mst-event.md): 期間指定マスタの実装
- [パフォーマンス最適化](examples/cache-performance.md): キャッシュ戦略とベンチマーク

### 段階的な実装手順

1. [基本実装を理解する](patterns/basic-implementation.md)
2. [期間指定マスタを理解する](patterns/timed-master.md)（必要に応じて）
3. [複雑な検索条件を実装する](patterns/complex-queries.md)
4. [パフォーマンスを最適化する](examples/cache-performance.md)

## 関連スキル

- **usr-model-manager**: ユーザーデータのリポジトリ実装
- **migration**: マイグレーションファイルの作成
- **api-test-implementation**: テストコードの実装

## 更新履歴

- 2025-12-15: 初版作成
