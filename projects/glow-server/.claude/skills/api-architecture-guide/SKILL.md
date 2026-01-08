# API アーキテクチャガイド

glow-server APIの実装・調査タスクで、アーキテクチャドキュメントを適切に参照するためのスキルです。

## 使用タイミング

以下のようなタスクで使用してください：
- 新規API実装で、正しいレイヤー構造を確認したい
- 他ドメインとの連携方法（Delegator）を確認したい
- 依存関係ルールに違反していないか確認したい
- 既存コードの処理フローを理解したい

## タスク種別と参照ドキュメント

### 1. 新規APIエンドポイント実装

以下のドキュメントを順に確認してください：

1. **概要理解**: `docs/01_project/architecture/00_アーキテクチャ概要.md`
   - 全体像、技術スタック、ドメイン一覧

2. **レイヤー責務**: `docs/01_project/architecture/01_レイヤードアーキテクチャ.md`
   - Controller, UseCase, Service, Repository, Model の責務
   - 各レイヤー間の呼び出し制約

3. **配置場所**: `docs/01_project/architecture/03_ディレクトリ構造.md`
   - 正しいディレクトリ配置
   - 命名規則

4. **依存ルール**: `docs/01_project/architecture/04_依存関係ルール.md`
   - 依存の方向、禁止事項
   - 特例ルール（Mst, Game, Common）

### 2. ドメインロジック・他ドメイン連携

1. **モジュール設計**: `docs/01_project/architecture/02_モジュラーモノリス.md`
   - Delegatorパターン
   - ユーザーモデルエンティティへの変換ルール
   - Entityの分類（DomainEntity, ResourceEntity, CommonEntity）

2. **依存ルール**: `docs/01_project/architecture/04_依存関係ルール.md`
   - ドメイン間の依存禁止事項
   - 特例ルール

3. **共通機構**: `docs/01_project/architecture/06_共通基盤.md`
   - トランザクション管理（UseCaseTrait）
   - UsrModelManager, UsrModelDiffGetService

### 3. UseCase実装

1. **UseCase責務**: `docs/01_project/architecture/01_レイヤードアーキテクチャ.md`
   - UseCase層のセクションを確認

2. **データフロー**: `docs/01_project/architecture/05_データフロー.md`
   - リクエスト〜レスポンスの流れ
   - UsrModelManagerによるキャッシュフロー
   - レスポンスデータ生成フロー

3. **共通機構**: `docs/01_project/architecture/06_共通基盤.md`
   - applyUserTransactionChanges()の使用方法
   - Clock, CurrentUser の使用方法

### 4. テスト実装

1. **テスト戦略**: `docs/01_project/architecture/07_テスト戦略.md`
   - レイヤーごとのテスト方針
   - モック使用の指針
   - テスト命名規則、AAAパターン

### 5. 既存コード調査・バグ修正

1. **概要理解**: `docs/01_project/architecture/00_アーキテクチャ概要.md`
   - 全体像の把握

2. **データフロー**: `docs/01_project/architecture/05_データフロー.md`
   - 処理フローの理解

3. **依存ルール**: `docs/01_project/architecture/04_依存関係ルール.md`
   - 禁止パターンの確認

## クイックリファレンス

### 依存関係の基本ルール

```
Controller → UseCase のみ
UseCase/Service → Service, Repository, Delegator
他ドメインへのアクセス → Delegator経由（例外あり）
```

### 特例ルール

| 対象 | ルール |
|------|--------|
| Resource/Mst（マスタデータ） | 全ドメインから直接アクセス可 |
| Gameドメイン | 他ドメインService/Repositoryに直接アクセス可（課金除く） |
| Common | 全ドメインから直接アクセス可 |

### Delegator使用時の注意

```php
// NG: ユーザーモデルをそのまま返す
public function getUsrItem(...): ?UsrItem

// OK: エンティティに変換して返す
public function getUsrItem(...): ?UsrItemEntity
{
    return $this->repository->find(...)?->toEntity();
}
```

### レスポンスデータ配置

```
Http/Responses/
├── Data/           # Dataクラス（UsrParameterData等）
└── ResultData/     # ResultDataクラス（StageEndResultData等）
```

## 使用例

```
ユーザー: "新しいステージ終了APIを実装したい"
↓
1. 00_アーキテクチャ概要.md で全体像を確認
2. 01_レイヤードアーキテクチャ.md でController/UseCase/Serviceの責務を確認
3. 03_ディレクトリ構造.md で配置場所を確認
4. 04_依存関係ルール.md で依存ルールを確認
5. 必要に応じて 02_モジュラーモノリス.md でDelegator使用方法を確認
```
