---
name: api-domain-layer
description: |
  クリーンアーキテクチャに従ったDomainレイヤー(api/app/Domain)の実装。レイヤー分離、ドメイン疎結合のためのDelegatorパターン、deptrac制約に準拠。以下の場合に使用: (1) UseCase/Service/Repository/Model/Entity/Delegatorの実装、(2) ドメイン分類(標準/Game/Resource/Common)の理解、(3) レイヤー依存関係ルール(deptrac制約)の遵守、(4) 正しいEntity型(DomainEntity/ResourceEntity/CommonEntity)の選択、(5) Delegatorの戻り値型制約の適用。Domainレイヤー実装、UseCase作成、Service作成、Repository追加、ビジネスロジック追加、Delegator実装、UseCase/Service/Repository追加時にトリガーされる。
---

# Implementing Domain Layer

新規API実装時にDomainレイヤー（`api/app/Domain`）のコードを実装するためのガイド

## Instructions

### 1. アーキテクチャと依存関係を理解する

クリーンアーキテクチャのレイヤー構造とdeptracによる依存関係ルールを理解する
参照: **[architecture.md](architecture.md)**

### 2. ドメインの分類を理解する

実装対象のドメインがどの分類に属するかを判断する（通常ドメイン、Game、Resource、Common）
参照: **[domain-types.md](domain-types.md)**

### 3. フォルダ構造と各サブフォルダの役割を確認する

Constants、Delegators、Entities、Models、Repositories、Services、UseCases等の使い分けを理解する
参照: **[folder-structure.md](folder-structure.md)**

### 4. Entityの種別と使用ルールを確認する

DomainEntity、ResourceEntity、CommonEntityの違いとDelegatorでのreturn制約を理解する
参照: **[entity-guide.md](entity-guide.md)**

### 5. Delegatorの役割と実装パターンを確認する

ドメイン間疎結合のためのDelegatorの役割、return型制約、実装パターンを理解する
参照: **[delegator-guide.md](delegator-guide.md)**

### 6. 実装例を参考にする

既存の実装パターンから具体的なコード例を確認する
参照:
- **[examples/standard-domain.md](examples/standard-domain.md)** - 通常ドメイン（Unit）の実装例
- **[examples/game-domain.md](examples/game-domain.md)** - Gameドメインの実装例
- **[examples/resource-domain.md](examples/resource-domain.md)** - Resourceドメインの実装例
- **[examples/common-domain.md](examples/common-domain.md)** - Commonドメインの実装例

## 参照ドキュメント

- **[architecture.md](architecture.md)** - クリーンアーキテクチャとdeptrac依存関係ルール
- **[domain-types.md](domain-types.md)** - ドメインの4つの分類
- **[folder-structure.md](folder-structure.md)** - サブフォルダの役割と使い分け
- **[entity-guide.md](entity-guide.md)** - Entity種別と使用ルール
- **[delegator-guide.md](delegator-guide.md)** - Delegatorの役割と実装パターン
- **[examples/standard-domain.md](examples/standard-domain.md)** - 通常ドメインの実装例
- **[examples/game-domain.md](examples/game-domain.md)** - Gameドメインの実装例
- **[examples/resource-domain.md](examples/resource-domain.md)** - Resourceドメインの実装例
- **[examples/common-domain.md](examples/common-domain.md)** - Commonドメインの実装例

## アーキテクチャドキュメント参照

アーキテクチャの詳細理解が必要な場合は、以下のドキュメントを参照してください：

| ドキュメント | 用途 |
|------------|------|
| `docs/01_project/architecture/01_レイヤードアーキテクチャ.md` | 各レイヤー（UseCase/Service/Repository/Model）の責務 |
| `docs/01_project/architecture/02_モジュラーモノリス.md` | Delegatorパターン、Entity分類の詳細 |
| `docs/01_project/architecture/03_ディレクトリ構造.md` | 正しい配置場所と命名規則 |
| `docs/01_project/architecture/04_依存関係ルール.md` | 依存関係の制約、特例ルール |
| `docs/01_project/architecture/06_共通基盤.md` | トランザクション管理、UsrModelManager |

### 依存関係クイックリファレンス

```
Controller → UseCase のみ
UseCase/Service → Service, Repository, Delegator
他ドメインへのアクセス → Delegator経由
```

**特例ルール:**
- **Resource/Mst**: 全ドメインから直接アクセス可（マスタデータ）
- **Gameドメイン**: 他ドメインService/Repositoryに直接アクセス可（課金除く）
- **Common**: 全ドメインから直接アクセス可
