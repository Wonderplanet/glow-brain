---
name: api-test-runner
description: glow-server APIのPHPUnitテスト実行と失敗自動修正。アサーション失敗、例外、データベースエラー、モック期待値不一致を分析・修正。テスト実行、テスト失敗修正、PHPUnit実行、全テスト通過確認、sail phpunit実行、テストエラー解消時に使用。 (project)
---

# Running and Fixing API Tests

PHPUnitテストを実行し、失敗しているテストを自動的に修正するスキル。

## Instructions

### 1. テストを実行してエラーを確認

参照: **[guides/test-commands.md](guides/test-commands.md)** - sail phpunitコマンドの使い方

```bash
# 全テスト実行
sail phpunit

# 特定のテストのみ実行
sail phpunit --filter TestClassName
```

### 2. エラーの種類を特定して修正方針を決定

参照: **[workflow.md](workflow.md)** - エラー分析から修正完了までのフロー

エラーメッセージから失敗パターンを特定し、適切な修正パターンを適用。

### 3. 失敗パターンごとに修正を実行

各パターンの修正方法を参照:
- **[patterns/assertion-failures.md](patterns/assertion-failures.md)** - アサーション失敗
- **[patterns/exception-errors.md](patterns/exception-errors.md)** - 例外・エラー
- **[patterns/database-errors.md](patterns/database-errors.md)** - DB関連エラー
- **[patterns/mock-errors.md](patterns/mock-errors.md)** - モック期待値不一致

### 4. 修正後にテストを再実行して確認

全テストが成功するまで繰り返す。

参照: **[guides/debugging-methods.md](guides/debugging-methods.md)** - デバッグ方法

## 参照ドキュメント

- **[workflow.md](workflow.md)** - テスト実行から修正完了までの全体フロー
- **[guides/test-commands.md](guides/test-commands.md)** - sail phpunitコマンド使用方法
- **[guides/debugging-methods.md](guides/debugging-methods.md)** - デバッグ方法
- **[patterns/assertion-failures.md](patterns/assertion-failures.md)** - アサーション失敗の修正
- **[patterns/exception-errors.md](patterns/exception-errors.md)** - 例外エラーの修正
- **[patterns/database-errors.md](patterns/database-errors.md)** - DB関連エラーの修正
- **[patterns/mock-errors.md](patterns/mock-errors.md)** - モック期待値不一致の修正
