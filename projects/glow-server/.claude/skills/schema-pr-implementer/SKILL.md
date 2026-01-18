---
name: schema-pr-implementer
description: |
  glow-schemaのPR変更をglow-serverに反映し、マイグレーション生成、Entity/Model/Repository/Resourceファイル更新、テスト作成を実施する。テーブル作成/削除、カラム変更、インデックス修正に対応。以下の場合に使用：(1) glow-schema PR変更をglow-serverに適用、(2) スキーマ変更やYAML同期の実装、(3) スキーマ定義からのマイグレーション作成、(4) ユーザーが「schemaのPRを反映」「スキーマ変更を適用」「YAMLを同期」または特定のglow-schema PR番号に言及した時。
---

# Implementing glow-schema PR Changes

glow-schemaのPR変更をglow-serverに反映するための実装スキル。

## Instructions

### 1. glow-schema PRを解析

PR内容を確認し、変更パターンを特定します。
参照: **[workflow.md](workflow.md)**

### 2. 変更パターンに応じて実装

8つのパターンから該当する実装方法を選択します。
参照: **[patterns.md](guides/patterns.md)**

### 3. マイグレーション作成

[migration スキル](../migration/SKILL.md) を使用してマイグレーションファイルを作成します。

### 4. 関連ファイル更新

Entity/Model/Repository/Resourceを必要に応じて更新します。

### 5. 実装確認

マイグレーション実行、テスト実行で動作確認します。

## 参照ドキュメント

- **[workflow.md](workflow.md)** - PR解析から実装完了までの全体フロー
- **[patterns.md](guides/patterns.md)** - 8つの変更パターン別詳細ガイド
- **[new-table.md](examples/new-table.md)** - 新規テーブル作成実例
- **[column-changes.md](examples/column-changes.md)** - カラム変更実例
- **[table-deletion.md](examples/table-deletion.md)** - テーブル削除実例
- **[complex.md](examples/complex.md)** - 複合変更実例
