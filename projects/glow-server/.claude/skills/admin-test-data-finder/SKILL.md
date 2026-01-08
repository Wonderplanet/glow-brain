---
name: "Finding Admin Test Data"
description: admin画面のテスト・動作確認で適切なテストデータを持つプレイヤーを探したい時に使用。glow-server-local-db MCPを使ってusr/logDBから条件に応じたプレイヤーデータを効率的に検索し、テスト観点に最適なusr_user_idを特定する。
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
