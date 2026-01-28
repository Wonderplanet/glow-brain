# クリーンアーキテクチャとdeptrac依存関係ルール

このプロジェクトはクリーンアーキテクチャの考え方を取り入れており、deptracで依存関係を厳密に管理しています。

## 目次

- [レイヤー構造](#レイヤー構造)
- [Entity の種別と制約](#entity-の種別と制約)
- [Model の種別と制約](#model-の種別と制約)
- [Delegator の役割と制約](#delegator-の役割と制約)
- [依存関係ルール一覧](#依存関係ルール一覧)
- [deptrac設定ファイル](#deptrac設定ファイル)

## レイヤー構造

```
Controller (app/Http/Controllers)
  ↓
UseCase (app/Domain/*/UseCases)
  ↓
Service (app/Domain/*/Services)
  ↓
Delegator (app/Domain/*/Delegators)  ← ドメイン間の疎結合
  ↓
Repository (app/Domain/*/Repositories)
  ↓
Model (app/Domain/*/Models)
```

### 重要な原則

- **下位レイヤーから上位レイヤーへの依存は禁止**（例: Service → UseCase は NG）
- **ドメイン間の直接依存は禁止**（例: Unit\Service → Shop\Service は NG）
- **ドメイン間の依存はDelegator経由で行う**（例: Unit\Service → Shop\Delegator は OK）

## Entity の種別と制約

Entityは3つの種別があり、それぞれ使用範囲と制約が異なります。

### 1. DomainEntity（ドメイン固有Entity）

```php
// 例: App\Domain\Shop\Entities\CurrencyPurchase
namespace App\Domain\Shop\Entities;

class CurrencyPurchase
{
    // ドメイン固有のビジネスロジックを持つEntity
}
```

**特徴:**
- パス: `App\Domain\[ドメイン名]\Entities\*`
- ドメイン固有のビジネスロジックを持つ
- UsrModelInterfaceへの依存は可能（ユーザーデータ操作可能）

**制約:**
- ❌ **Delegatorのreturnで使用禁止**（ドメイン外へ渡さない）
- ✅ UseCase、Service内での使用はOK
- ✅ ResourceEntity、CommonEntity、MstModelEntity、UsrModelEntityへの依存はOK

### 2. ResourceEntity（部分共有Entity）

```php
// 例: App\Domain\Resource\Entities\CheatCheckUnit
namespace App\Domain\Resource\Entities;

class CheatCheckUnit
{
    // 複数ドメインで共有可能なEntity
}
```

**特徴:**
- パス: `App\Domain\Resource\Entities\*`
- 特定の複数ドメインで共有可能
- マスタデータやユーザーデータの共通Entity

**制約:**
- ✅ **Delegatorのreturnで使用可能**
- ✅ 全ドメインから参照可能
- ✅ CommonEntity、MstModelEntity、UsrModelEntityへの依存はOK

### 3. CommonEntity（全体共有Entity）

```php
// 例: App\Domain\Common\Entities\Clock
namespace App\Domain\Common\Entities;

class Clock
{
    // 全ドメインで利用可能なEntity
}
```

**特徴:**
- パス: `App\Domain\Common\Entities\*`
- 全ドメインで利用可能な汎用Entity
- ドメインロジックに依存しない純粋な値オブジェクト

**制約:**
- ✅ **Delegatorのreturnで使用可能**
- ✅ 全ドメインから参照可能
- ✅ 他のCommonEntityへの依存はOK

## Model の種別と制約

### 1. UsrModel / UsrModelInterface

```php
// UsrModelInterface（インターフェース）
namespace App\Domain\Unit\Models;

interface UsrUnitInterface
{
    public function getId(): string;
    public function getMstUnitId(): string;
    public function toEntity(): UsrUnitEntity;
}

// UsrModel（実装クラス）
namespace App\Domain\Unit\Models;

class UsrUnit implements UsrUnitInterface
{
    // Eloquent Model の実装
}
```

**特徴:**
- パス: `App\Domain\[ドメイン名]\Models\Usr*`
- ユーザーデータ（usr/log データベース）のEloquent Model
- `toEntity()` メソッドでUsrModelEntityに変換可能

**制約:**
- ❌ **UsrModelInterfaceはDelegatorのreturnで使用禁止**（ドメイン外へ渡さない）
- ✅ UsrModelEntity（`App\Domain\Resource\Usr\Entities\*`）に変換すればDelegatorのreturnで使用可能
- ✅ Service、Repository内での使用はOK

### 2. MstModel / MstModelEntity

```php
// MstModel
namespace App\Domain\Resource\Mst\Models;

class MstUnit
{
    // マスタデータのEloquent Model
}

// MstModelEntity
namespace App\Domain\Resource\Mst\Entities;

class MstUnitEntity
{
    // マスタデータのEntity
}
```

**特徴:**
- パス: `App\Domain\Resource\Mst\Models\*`（Model）
- パス: `App\Domain\Resource\Mst\Entities\*`（Entity）
- マスタデータ（mst データベース）のModel
- 全ドメインから参照可能

**制約:**
- ✅ **MstModelEntityはDelegatorのreturnで使用可能**
- ✅ 全ドメインから参照可能
- ✅ UseCase、Service、Delegator、Repository内での使用はOK

## Delegator の役割と制約

Delegatorはドメイン間の疎結合を実現するための重要なレイヤーです。

### Delegatorの役割

- 他ドメインから呼び出される公開インターフェース
- ドメイン固有のデータを外部に公開可能な形に変換
- ServiceやRepositoryを呼び出してビジネスロジックを実行

### Delegatorのreturn型制約

**✅ 使用可能な型:**
- CommonEntity（`App\Domain\Common\Entities\*`）
- ResourceEntity（`App\Domain\Resource\Entities\*`）
- MstModelEntity（`App\Domain\Resource\Mst\Entities\*`）
- UsrModelEntity（`App\Domain\Resource\Usr\Entities\*`）
- プリミティブ型（string, int, bool等）
- Collection（上記の型を含む）

**❌ 使用禁止の型:**
- DomainEntity（`App\Domain\[ドメイン名]\Entities\*`）
- UsrModelInterface（`App\Domain\[ドメイン名]\Models\Usr*Interface`）
- UsrModel（`App\Domain\[ドメイン名]\Models\Usr*`）

### 理由

ドメイン固有のデータ（DomainEntity、UsrModelInterface）をドメイン外へ渡すと、以下の問題が発生します:
- ドメイン間の密結合が発生
- ドメイン境界が曖昧になる
- テストが困難になる

## 依存関係ルール一覧

### Controller
- ✅ UseCase

### UseCase
- ✅ DomainEntity
- ✅ ResourceEntity
- ✅ CommonEntity
- ✅ Service
- ✅ MstModelEntity
- ✅ MstModelRepository
- ✅ UsrModelInterface
- ✅ UsrModelEntity
- ✅ UsrModelRepository
- ✅ Delegator

### Service
- ✅ DomainEntity
- ✅ ResourceEntity
- ✅ CommonEntity
- ✅ MstModelEntity
- ✅ MstModelRepository
- ✅ UsrModelInterface
- ✅ UsrModelEntity
- ✅ UsrModelRepository
- ✅ Delegator

### Delegator
- ✅ CommonEntity
- ✅ ResourceEntity
- ✅ MstModelEntity
- ✅ UsrModelEntity
- ✅ UsrModelRepository
- ✅ Service

### Repository
- ✅ UsrModelInterface
- ✅ UsrModel
- ✅ DomainEntity
- ✅ MstModelEntity

### UsrModel
- ✅ UsrModelInterface
- ✅ UsrModelEntity

### DomainEntity
- ✅ ResourceEntity
- ✅ CommonEntity
- ✅ MstModelEntity
- ✅ UsrModelEntity
- ✅ UsrModelInterface

### ResourceEntity
- ✅ CommonEntity
- ✅ MstModelEntity
- ✅ UsrModelEntity

### CommonEntity
- ✅ CommonEntity（相互参照可）

## deptrac設定ファイル

依存関係ルールは `api/deptrac.yaml` で定義されています。

```yaml
# api/deptrac.yaml（抜粋）
deptrac:
  layers:
    - name: Controller
      collectors:
        - type: directory
          value: ./app/Http/Controllers/.*

    - name: UseCase
      collectors:
        - type: directory
          regex: ./app/Domain/.*/UseCases/.*

    - name: DomainEntity
      # ドメイン固有のEntity。Delegatorのreturnで使用禁止。
      collectors:
        - type: class
          value: App\\Domain\\[^\\]+\\Entities\\[^\\]+

    - name: ResourceEntity
      # 複数ドメインで共有可能なEntity。Delegatorのreturnで使用可。
      collectors:
        - type: class
          value: App\\Domain\\Resource\\Entities\\[^\\]+

    - name: CommonEntity
      # 全ドメインで利用可能なEntity。Delegatorのreturnで使用可。
      collectors:
        - type: class
          value: App\\Domain\\Common\\Entities\\[^\\]+

  ruleset:
    Controller:
      - UseCase

    UseCase:
      - DomainEntity
      - ResourceEntity
      - CommonEntity
      - Service
      - MstModelEntity
      - UsrModelEntity
      - Delegator

    Delegator:
      - CommonEntity
      - ResourceEntity
      - MstModelEntity
      - UsrModelEntity
      - Service
```

### deptracチェック実行方法

```bash
# api ディレクトリでdeptracチェック実行
./tools/bin/sail-wp deptrac

# または
cd api && vendor/bin/deptrac
```

違反があった場合はエラーが表示され、修正が必要です。

## チェックリスト

新規ドメイン実装時に以下を確認してください:

- [ ] レイヤー構造に従った依存関係になっているか
- [ ] DomainEntityをDelegatorのreturnで使用していないか
- [ ] UsrModelInterfaceをDelegatorのreturnで使用していないか
- [ ] ドメイン間の直接依存を避け、Delegator経由になっているか
- [ ] deptracチェックが通るか（`./tools/bin/sail-wp deptrac`）
