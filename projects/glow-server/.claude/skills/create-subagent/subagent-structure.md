# サブエージェントの基本構造

## ファイル配置

サブエージェントファイルは `.claude/agents/` ディレクトリに配置します。

```
.claude/agents/
├── api-phpstan-fixer.md
├── api-phpcs-phpcbf-fixer.md
├── admin-browser-tester.md
└── {新しいエージェント名}.md
```

## YAML Frontmatter 形式

すべてのサブエージェントファイルは以下のYAML frontmatterで始まる必要があります：

```markdown
---
name: agent-name
description: エージェントの機能と使用タイミングを1行で説明
model: sonnet
color: purple
---

# エージェントの詳細説明

（エージェントの本文）
```

## 必須フィールド

### name（必須）

- **形式**: 小文字、ハイフン区切り（kebab-case）
- **最大長**: 64文字
- **例**: `api-phpstan-fixer`, `admin-browser-tester`, `database-optimizer`

### description（必須）

- **最大長**: 1024文字
- **内容**: 以下を明確に記述
  - エージェントが何をするか（機能）
  - いつトリガーされるか（使用タイミング）
  - 具体的な動作内容

**良い例:**
```yaml
description: PHPStan静的解析エラーを検出・修正するサブエージェント。sail phpstanを実行し、型エラー・未定義変数・メソッド呼び出しエラー等の全ての静的解析エラーを解消する。設定変更によるエラー無視は禁止。sail checkコマンドでphpstanエラーが出た時や、型安全性を確保したい時に使用。
```

**悪い例:**
```yaml
description: PHPStanのエラーを修正する  # ❌ トリガー条件が不明確
```

### model（必須）

タスクの複雑さに応じてモデルを選択：

| モデル | 用途 | 例 |
|--------|------|-----|
| `haiku` | 軽量・高速なタスク | 単純なファイル操作、設定変更 |
| `sonnet` | 標準的なタスク（推奨） | API実装、テスト実行、エラー修正 |
| `opus` | 複雑・高度なタスク | アーキテクチャ設計、複雑な設計フロー |

### color（必須）

エージェントの種類を視覚的に分類：

| カラー | 用途 | 例 |
|--------|------|-----|
| `purple` | API・コア機能開発 | api-phpstan-fixer |
| `blue` | テスト・品質管理 | api-test-runner |
| `green` | データベース・リソース | database-optimizer |
| `red` | エラー修正・緊急対応 | error-analyzer |
| `yellow` | ドキュメント・設計 | sdd-orchestrator |
| `orange` | 運用・デプロイ | deployment-manager |

## description のベストプラクティス

### 1. 具体的な機能を記述

```yaml
# ✅ 良い例
description: sail phpstanを実行し、型エラー・未定義変数・メソッド呼び出しエラー等の全ての静的解析エラーを解消する

# ❌ 悪い例
description: コードの品質を改善する
```

### 2. トリガー条件を明記

```yaml
# ✅ 良い例
description: sail checkコマンドでphpstanエラーが出た時や、型安全性を確保したい時に使用

# ❌ 悪い例
description: 必要な時に使用する
```

### 3. 制約事項を含める（必要に応じて）

```yaml
# ✅ 良い例
description: ...設定変更によるエラー無視は禁止。sail checkコマンドでphpstanエラーが出た時や...

# ❌ 悪い例
description: エラーを修正する
```

### 4. Examples セクションを追加（推奨）

descriptionの末尾に具体的な使用例を追加すると、Claudeがより正確にエージェントを起動できます：

```yaml
description: |
  PHPStan静的解析エラーを検出・修正するサブエージェント。sail phpstanを実行し、型エラー・未定義変数・メソッド呼び出しエラー等の全ての静的解析エラーを解消する。設定変更によるエラー無視は禁止。sail checkコマンドでphpstanエラーが出た時や、型安全性を確保したい時に使用。

  Examples:
  <example>Context: sail checkでphpstanエラーが発生 user: 'phpstanエラーを全て修正して' assistant: 'api-phpstan-fixerエージェントを使用して静的解析エラーを解消します' <commentary>phpstanエラーの解消が必要なため、このエージェントを使用</commentary></example>
```

## glow-server プロジェクト固有の設定

### 環境実行コマンド

glow-serverプロジェクトでは、全てのコマンドはDocker環境で実行します：

```markdown
# ✅ 正しい実行方法
sail phpstan
sail artisan migrate
sail test

# または
./tools/bin/sail-wp phpstan
./tools/bin/sail-wp migrate

# ❌ 間違った実行方法
phpstan  # ローカル環境では実行しない
php artisan migrate  # Docker外では実行しない
```

### データベース接続

複数のデータベース接続があることを考慮：

- **MySQL系**: mst、mng、admin（mysqlコンテナ）
- **TiDB系**: usr、log、sys（tidbコンテナ）

### ディレクトリ構造

- `api/`: API開発用ディレクトリ
- `admin/`: 管理ツール開発用ディレクトリ

エージェントが両方に対応する場合は明記してください。

## エージェントファイルの完全な例

```markdown
---
name: api-migration-runner
description: glow-serverプロジェクトのマイグレーション実行専門エージェント。sail migrateまたはsail admin migrateを実行し、複数DB（mst/mng/usr/log/sys/admin）に対するマイグレーションを管理。エラー発生時はロールバックとデバッグを行う。新規テーブル作成やスキーマ変更時に使用。
model: sonnet
color: green
---

# Migration Runner

## 役割と責任

このエージェントは、glow-serverプロジェクトのデータベースマイグレーション実行を専門に担当します。

### 主な機能

1. マイグレーション実行（api/adminディレクトリ）
2. エラー検出とロールバック
3. 複数DB接続の管理
4. マイグレーション履歴の確認

## 基本原則

- 全てのコマンドはDocker環境（sail）で実行
- エラー発生時は自動的にロールバック
- マイグレーション前に必ずバックアップ確認

## 標準作業フロー

### 1. 事前確認

```bash
# Docker環境の起動確認
sail ps

# 既存マイグレーション状態確認
sail artisan migrate:status
```

### 2. マイグレーション実行

```bash
# APIディレクトリ
sail migrate

# Adminディレクトリ
sail admin migrate
```

### 3. エラーハンドリング

エラー発生時：
1. エラーログを解析
2. 該当マイグレーションファイルを確認
3. 必要に応じてロールバック
4. 修正後に再実行

## 品質保証基準

- マイグレーション実行前にテスト環境で確認
- ロールバック可能な状態を常に維持
- 本番環境では必ずバックアップ取得後に実行
```

## チェックリスト

新しいエージェントを作成する際の確認項目：

- [ ] `name` がkebab-caseで64文字以内
- [ ] `description` が機能とトリガー条件を明確に説明（1024文字以内）
- [ ] `model` が適切に選択されている（haiku/sonnet/opus）
- [ ] `color` がエージェントの種類に応じて設定されている
- [ ] glow-serverプロジェクトの環境（Docker/sail）を考慮している
- [ ] 複数DB接続の場合は明記されている
- [ ] api/adminディレクトリの対応範囲が明確
- [ ] 既存エージェントと役割が重複していない
