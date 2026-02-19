---
name: api-endpoint-implementation
description: |
  glow-serverでの新規APIエンドポイント実装の完全ガイド。スキーマ検証からController実装、レスポンス処理、テストまでカバー。以下の場合に使用: (1) 新規APIエンドポイントの追加、(2) api/routes/api.phpでのルーティング定義、(3) Controllerの実装、(4) ResultDataとResponseFactoryの作成、(5) ミドルウェアの設定、(6) 関連スキル(api-schema-reference、migration、domain-layer、api-request-validation、api-response、api-test-implementation)との統合。スキーマ確認からルーティング、Controller実装、ResultData/ResponseFactory作成、テストまでの全ワークフローをカバー。新規API追加、エンドポイント実装、API作成、REST API実装、APIルート追加、新規エンドポイント作成時にトリガーされる。
---

# Implementing API Endpoint

glow-serverで新規APIエンドポイントを追加する際の完全ガイド。

## Instructions

### 1. 全体フローを確認する

API実装の全手順と既存スキルの使用タイミングを把握します。
参照: **[workflow.md](workflow.md)**

### 2. ルーティング定義を追加する

`api/routes/api.php` にルート定義を追加し、適切なミドルウェアを設定します。
参照:
- **[routing.md](routing.md)** - ルート定義の追加方法
- **[middleware.md](middleware.md)** - ミドルウェアの選択

### 3. Controllerを実装する

Controller層の実装パターンに従ってエンドポイントを実装します。
参照: **[controller.md](controller.md)**

### 4. ResultDataを実装する

UseCaseからResponseFactoryへのデータ受け渡し用のResultDataを実装します。
参照: **[result-data.md](result-data.md)**

### 5. 実装例を参考にする

既存の完全な実装例を確認して、パターンを理解します。
参照: **[examples.md](examples.md)**

## 参照ドキュメント

- **[workflow.md](workflow.md)** - API実装の全体フロー
- **[routing.md](routing.md)** - ルーティング定義の追加
- **[controller.md](controller.md)** - Controller実装パターン
- **[result-data.md](result-data.md)** - ResultData実装パターン
- **[middleware.md](middleware.md)** - ミドルウェアの選択と設定
- **[examples.md](examples.md)** - 完全な実装例

## 関連スキル

このスキルは以下の既存スキルと連携します：

1. **api-schema-reference** - glow-schemaのYAML仕様確認
2. **migration** - データベースマイグレーション実装
3. **domain-layer** - Domain層（Model/Repository/Service/UseCase/Delegator）実装
4. **api-request-validation** - リクエストバリデーション実装
5. **api-response** - ResponseFactory実装
6. **api-test-implementation** - テスト実装

典型的な使用フロー:
```
api-schema-reference → migration → domain-layer →
api-endpoint-implementation → api-request-validation →
api-response → api-test-implementation
```

## アーキテクチャドキュメント参照

より詳細なアーキテクチャ理解が必要な場合は、以下のドキュメントを参照してください：

| ドキュメント | 用途 |
|------------|------|
| `docs/01_project/architecture/00_アーキテクチャ概要.md` | 全体像の把握 |
| `docs/01_project/architecture/01_レイヤードアーキテクチャ.md` | 各レイヤーの責務確認 |
| `docs/01_project/architecture/03_ディレクトリ構造.md` | 正しい配置場所の確認 |
| `docs/01_project/architecture/04_依存関係ルール.md` | 依存関係の制約確認 |
| `docs/01_project/architecture/05_データフロー.md` | 処理フローの理解 |

### 主要な依存関係ルール

- **Controller** → UseCase のみ依存可
- **UseCase/Service** → Service, Repository, Delegator に依存可
- **他ドメインへのアクセス** → Delegator経由（例外: Resource/Mst, Common, Game）
