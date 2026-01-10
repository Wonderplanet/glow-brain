---
name: api-master-repository
description: |
  glow-serverのマスタデータリポジトリ実装をAPCuキャッシュを使って行う。MasterRepository基底クラスを使用したEntity/Model/Repositoryの三層アーキテクチャを実装する。以下の場合に使用: (1) 新しいマスタデータテーブルやリポジトリの実装、(2) APCuキャッシュを使ったマスタデータ取得、(3) 日次自動更新を伴う期間指定マスタデータ (getDayActives)、(4) カラムベースのフィルタリング (getByColumn/getByColumns)、(5) 1日TTLキャッシュを使った全件データ取得。MstModel継承、readonly Entity パターン、MasterRepositoryのDI対応をサポート。 (project)
---

# MasterRepository実装ガイド

マスタデータ（mst/opr DB）のリポジトリ実装を支援します。APCuキャッシュを活用した効率的なマスタデータ取得とEntity変換パターンを提供します。

## Instructions

### 1. 基本構造の理解

マスタデータの取得には必ず`App\Infrastructure\MasterRepository`を使用します。
- **Model**: Eloquentモデルでデータベーステーブルを表現し、`toEntity()`でEntityに変換
- **Entity**: 不変オブジェクトとしてビジネスロジックで使用
- **Repository**: MasterRepositoryをDIして各種取得メソッドを提供

### 2. キャッシュ戦略

MasterRepositoryは以下のキャッシュ方式を提供:
- `get()`: 全件取得、IDをキーとした連想配列でキャッシュ（デフォルト1日TTL）
- `getByColumn()`: 指定カラムでの絞り込み、クエリベースでキャッシュ
- `getByColumns()`: 複数カラム条件での絞り込み
- `getDayActives()`: 期間指定マスタで当日有効なデータのみキャッシュ（日跨ぎで自動更新）

### 3. 実装パターン

**基本パターン（全件取得）**:
```php
class MstXxxRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {}

    public function getAll(): Collection
    {
        return $this->masterRepository->get(MstXxx::class);
    }
}
```

**期間指定マスタパターン（getDayActives）**:
```php
public function getAllActiveEvents(CarbonImmutable $now): Collection
{
    return $this->masterRepository->getDayActives(MstEvent::class, $now)
        ->filter(function (Entity $entity) use ($now) {
            return $this->isActiveEntity($entity, $now);
        });
}
```

**カラム条件指定パターン**:
```php
public function getByType(string $type): Collection
{
    return $this->masterRepository->getByColumn(
        MstXxx::class,
        'type',
        $type,
    );
}
```

### 4. Model実装

- `MstModel`を継承
- `toEntity()`メソッドでEntity変換を実装
- `$connection = 'mst'`を指定（デフォルト）

### 5. Entity実装

- `readonly private`プロパティで不変性を保証
- コンストラクタで全プロパティを初期化
- getterのみ実装（setterは作らない）

## 参照ドキュメント

### ガイド
- **[キャッシュ戦略](guides/cache-strategy.md)** - APCuキャッシュの仕組み、TTL設定、キャッシュキー生成
- **[期間指定マスタの実装](guides/day-actives-pattern.md)** - getDayActives()の使い方、タイムゾーン考慮
- **[リポジトリパターン](guides/repository-pattern.md)** - MstRepositoryTraitの活用、エラーハンドリング

### パターン
- **[基本的な実装例](patterns/basic-implementation.md)** - Model/Entity/Repository の標準実装
- **[期間指定マスタ](patterns/timed-master.md)** - start_at/end_atを持つマスタの実装
- **[複雑な検索条件](patterns/complex-queries.md)** - 複数条件、カスタムフィルタ

### 実装例
- **[MstItemの実装](examples/mst-item.md)** - 完全な実装例（Model/Entity/Repository）
- **[MstEventの実装](examples/mst-event.md)** - 期間指定マスタの実装例
- **[キャッシュパフォーマンス](examples/cache-performance.md)** - ベンチマーク、最適化Tips
