---
name: admin-browser-tester
description: chrome-devtools MCPを使用したFilament管理画面の自動ブラウザテストスキル。以下の場合に使用:admin実装のテスト、ブラウザ動作確認、実際の画面挙動チェック、admin変更内容の検証、Filamentページのテスト。CRUD操作(作成/編集/削除)、フォームバリデーション、表示項目、ページ遷移を検証するブラウザ操作を自動化し、テスト結果レポートを生成。「管理画面をテスト」「ブラウザで確認」「実際の動作をチェック」「admin動作を検証」「Filamentページをテスト」などのリクエストで起動。
---

# Testing Admin Browser Functionality

admin実装や改修後に、実際のブラウザで動作確認を行うスキルです。chrome-devtools MCPを使用してブラウザ操作を自動化し、実装内容に応じた適切なテストを実施します。

## Instructions

### 1. 事前準備と環境確認

実装内容を確認し、テスト環境の準備を行います。
参照: **[環境セットアップガイド](guides/environment-setup.md)**

### 2. ブラウザ起動とログイン

chrome-devtools MCPを使用してブラウザを起動し、管理画面にログインします。
ログイン後、画面サイズを1920x1080に設定し、サイドバーを閉じます。
参照:
- **[chrome-devtools MCP使用ガイド](guides/chrome-devtools-mcp.md)** - 画面サイズ設定
- **[標準ログイン手順](patterns/login.md)** - ログイン〜サイドバークローズまでの完全な手順

### 3. 実装内容に応じたテスト実行

実装内容に応じて適切なテストパターンを選択し、段階的に検証を行います。
参照:
- **[新規ページ/リソース追加のテスト](patterns/new-resource.md)**
- **[CRUD機能のテスト](patterns/crud-operations.md)**
- **[フォーム/バリデーション変更のテスト](patterns/form-validation.md)**
- **[表示項目変更のテスト](patterns/display-changes.md)**

### 4. エラー検出とスクリーンショット撮影

コンソールエラー、表示異常、操作失敗を検出し、問題発生時はフルページでスクリーンショットを撮影します。
参照: **[chrome-devtools MCP使用ガイド](guides/chrome-devtools-mcp.md)** - エラー検出とスクリーンショット撮影

### 5. テスト結果レポート作成

テスト結果を標準フォーマットでレポートします。
参照: **[テスト結果レポートテンプレート](examples/test-report-template.md)**

## 参照ドキュメント

### ガイド
- **[chrome-devtools MCP使用ガイド](guides/chrome-devtools-mcp.md)** - MCPツールの使い方とセレクタパターン
- **[環境セットアップガイド](guides/environment-setup.md)** - ポート番号・認証情報の確認方法

### テストパターン
- **[標準ログイン手順](patterns/login.md)** - 全テストで共通のログイン手順
- **[新規ページ/リソース追加のテスト](patterns/new-resource.md)** - 新規追加画面のテスト
- **[CRUD機能のテスト](patterns/crud-operations.md)** - CRUD操作の段階的検証
- **[フォーム/バリデーション変更のテスト](patterns/form-validation.md)** - バリデーションテスト
- **[表示項目変更のテスト](patterns/display-changes.md)** - 表示確認テスト

### 実装例
- **[テスト結果レポートテンプレート](examples/test-report-template.md)** - レポートフォーマット
- **[トラブルシューティング](examples/troubleshooting.md)** - よくある問題と解決方法
