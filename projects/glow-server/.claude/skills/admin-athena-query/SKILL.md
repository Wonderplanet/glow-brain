---
name: admin-athena-query
description: 管理ツール(admin)で31日(TiDB TTL)を超える過去ログをAWS Athenaから取得するログ履歴ページを実装するスキル。以下の場合に使用:(1) 新規ログテーブル(log_*)の管理画面実装、(2) 既存ログページへのAthenaクエリ対応追加、(3) ログテーブルカラム変更時のAthenaテーブル定義更新、(4) ユーザーが31日以前のログ閲覧、過去ログ参照、Athena連携、AthenaQueryTrait実装をリクエストした時。IAthenaModelインターフェース実装、FilamentページでのAthenaQueryTrait使用、develop/production環境向けAthenaテーブル定義のartisanコマンド生成に対応。
---

# Athenaクエリ対応ログページ実装

管理ツール（admin）で新しいログテーブルの履歴ページを実装する際のガイドです。TiDBのTTL（31日）を超えた過去ログをAWS Athenaから取得できるようにします。

## Instructions

### 1. 対応が必要なケースの確認

以下のいずれかに該当する場合、このスキルを使用します：

- 新しいログテーブル（log_*）を管理ツールで表示したい
- 既存のログ表示ページにAthenaクエリ対応を追加したい
- ログテーブルにカラム変更があり、Athenaテーブル定義を更新したい

参照: **[Athenaクエリ対応が必要なケース](guides/when-to-use-athena.md)**

### 2. Athenaテーブル定義SQLの生成

artisanコマンドでdevelop/production環境用のSQLファイルを生成します。

参照: **[Athenaテーブル定義の生成](guides/generate-athena-table.md)**

### 3. admin側Modelの実装

ログモデルに`IAthenaModel`と`AthenaModelTrait`を実装します。

参照: **[Model実装パターン](patterns/model-implementation.md)**

### 4. Filamentログページの実装

`AthenaQueryTrait`を使用してログページを実装します。

参照: **[FilamentページでのAthena対応](patterns/filament-page-implementation.md)**

### 5. 動作確認

ローカル環境では日付範囲30日未満でDB参照を確認。develop/production環境でAthena参照を確認。

参照: **[動作確認手順](guides/testing.md)**

## 参照ドキュメント

### ガイド
- **[Athenaクエリ対応が必要なケース](guides/when-to-use-athena.md)** - いつAthena対応が必要か
- **[Athenaテーブル定義の生成](guides/generate-athena-table.md)** - artisanコマンドの使い方
- **[動作確認手順](guides/testing.md)** - ローカル・本番での確認方法

### 実装パターン
- **[Model実装パターン](patterns/model-implementation.md)** - IAthenaModel実装
- **[FilamentページでのAthena対応](patterns/filament-page-implementation.md)** - AthenaQueryTrait使用

### 実装例
- **[LogExchangeAction実装例](examples/log-exchange-action.md)** - 交換所ログの実装例

## 関連ファイル

| ファイル | 説明 |
|---------|------|
| `admin/app/Console/Commands/AthenaGenerateTableCommand.php` | テーブル定義生成コマンド |
| `admin/app/Contracts/IAthenaModel.php` | Athenaモデルインターフェース |
| `admin/app/Traits/AthenaModelTrait.php` | モデル用トレイト |
| `admin/app/Traits/AthenaQueryTrait.php` | ページ用トレイト |
| `admin/app/Constants/AthenaConstant.php` | Athena関連定数 |
| `admin/app/Operators/AthenaOperator.php` | Athenaクエリ実行 |

## 運用ドキュメント

チーム向け運用フローは以下を参照:
**[docs/01_project/operations/athena-query-setup.md](/docs/01_project/operations/athena-query-setup.md)**
