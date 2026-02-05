---
description: glow-schemaのPR変更をglow-serverに適用する。自動生成ファイル更新、品質チェック、PR作成まで実行。
argument-hint: "[PR番号]"
---

# glow-schema変更適用コマンド

glow-schemaリポジトリのプルリクエストの変更をglow-serverに適用するためのカスタムコマンドです。

## 使用方法

```
/api:glow-schema:apply-schema-changes [PR番号]
```

例: `/api:glow-schema:apply-schema-changes 420`

## 実行手順

引数: $ARGUMENTS（glow-schemaのPR番号）

以下の手順を自動実行します：

### 1. GitHub MCP を使用したPR情報取得

- `mcp__github__get_pull_request` を使用してWonderplanet/glow-schemaのPR #$ARGUMENTS の詳細を取得
- `mcp__github__get_pull_request_files` を使用して変更ファイル一覧を取得
- PR のタイトル、説明、変更内容を分析

### 2. glow-schema変更の解析

- 変更されたYMLファイルを特定
- APIスキーマの変更点を把握
- 必要な自動生成ファイルの特定

### 3. 自動生成ファイルの更新

- glow-schemaリポジトリから最新の変更を取得する必要があることを確認
- api/app/Http/Resources/Api以下の自動生成ファイルが更新されることを説明
- **重要**: 今回の変更に関係ないファイルは削除すること

手動実行が必要な作業:
```bash
# glow-schemaディレクトリで以下を実行
./update_schema.sh
```

### 4. 不要な自動生成ファイルの削除確認

- api/app/Http/Resources/Api以下で今回の変更に関係ないファイルをチェック
- 関係のないファイルがある場合は削除を提案
- 削除対象ファイルと理由をレポート

### 5. 品質チェック実行

```bash
./tools/bin/sail-wp check
```

- phpcs, phpstan, deptrac の全チェックを実行
- エラーが発生した場合:
  - 今回の変更に関係するエラーかどうかを判断
  - 関係ないエラーの場合は無視してその旨をレポート
  - 何をどんな経緯で無視したかを詳細に記録

### 6. プルリクエスト作成

変更が完了したら以下の手順でPRを作成:

1. **ブランチ作成**
   ```bash
   git checkout -b feature/apply-schema-pr-$ARGUMENTS
   ```

2. **コミット作成**
   ```bash
   git add .
   git commit -m "glow-schema PR #$ARGUMENTS の変更を適用

   - glow-schema の変更に対応した自動生成ファイルを更新
   - 関係ないファイルは除外済み
   - 品質チェック通過済み

   参考PR: Wonderplanet/glow-schema/pull/$ARGUMENTS"
   ```

3. **プルリクエスト作成**
   - タイトル: `glow-schema PR #$ARGUMENTS の変更を適用`
   - 本文は日本語で作成
   - 参考PRとして元のglow-schema PRをリンク
   - 変更内容の概要を記載
   - 品質チェック結果を記載

## 参考情報

- 過去の対応例: Wonderplanet/glow-server/pull/1307 (glow-schema PR #420 対応)
- 自動生成ファイルは api/app/Http/Resources/Api 以下
- glow-schemaでの生成方法: ./update_schema.sh (READMEに記載)

## 注意事項

- 自動生成ファイル以外は手動で変更しない
- 品質チェックで今回の変更に関係ないエラーが出る場合があるが、その場合は無視してレポート
- PRコメントは必ず日本語で記載
- 元のglow-schema PRとの関連性を明確にする