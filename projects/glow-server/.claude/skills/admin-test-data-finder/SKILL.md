---
name: admin-test-data-finder
description: glow-server管理ツールでglow-server-local-db MCPを使用した条件検索により、テストに最適なプレイヤーデータ(usr_user_id)を特定するスキル。以下の場合に使用:テストデータの検索、プレイヤー検索、usr_user_idの特定、適切なテストユーザーの検索、検証用データの探索。usr/logデータベースへのSQLクエリで条件付きプレイヤー検索(ユニット所持、アイテム所持、スタミナ値、ステージ進行度、購入履歴、レベル範囲)に対応し、admin-page-navigatorと連携してブラウザでの検証を実現。「テストデータを探す」「プレイヤーを検索」「usr_user_idを特定」「適切なテストユーザーを見つける」「検証用データを探す」などのリクエストで起動。
---

# Admin Test Data Finder

glow-server管理ツール(admin)のテスト・動作確認に最適なプレイヤーデータをDBから特定します。

## Instructions

### 1. テストデータ検索戦略の理解

DB調査の基本戦略を理解:
参照: **[query-strategies.md](query-strategies.md)**

### 2. 検索クエリの実行

要件に応じて以下を参照:

- **特定ユニット所持プレイヤー** → **[find-unit-owner.md](examples/find-unit-owner.md)**
- **条件別プレイヤー検索** → **[find-by-condition.md](examples/find-by-condition.md)**

### 3. usr_user_idの特定

1. glow-server-local-db MCPでクエリ実行
2. 結果から適切なusr_user_idを取得
3. admin-page-navigatorでURL構築
4. ブラウザで動作確認

## 参照ドキュメント

- **[query-strategies.md](query-strategies.md)** - テストデータ探索のクエリ戦略
- **[examples/find-unit-owner.md](examples/find-unit-owner.md)** - 特定ユニット所持プレイヤーの検索
- **[examples/find-by-condition.md](examples/find-by-condition.md)** - 条件に応じたプレイヤー検索
