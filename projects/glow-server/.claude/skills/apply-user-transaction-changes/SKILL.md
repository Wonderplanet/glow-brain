---
name: apply-user-transaction-changes
description: glow-server APIのユーザーデータ変更におけるUseCaseトランザクション制御。applyUserTransactionChanges（報酬配布、課金連携、usr_*テーブル書き込み、ミッション進捗用）とprocessWithoutUserTransactionChanges（読み取り専用操作用）の適切な使い分けをガイド。トランザクションフロー、実装パターン（ガチャ、ステージ、決済）、エラーハンドリング、UseCaseTrait使用方法を含む。トランザクション制御実装、applyUserTransactionChanges使用、ユーザーデータ更新、報酬配布、UseCaseトランザクション実装、ミッション進捗更新、決済システム連携時に使用。 (project)
---

# applyUserTransactionChanges スキル

UseCaseでのトランザクション制御パターンを理解し、正しく実装するためのスキルです。

## Instructions

1. **使用条件を判断** → [usage-conditions.md](usage-conditions.md) で使用要否を判断
2. **仕組みを理解** → [architecture.md](architecture.md) で処理フローを確認
3. **実装例を参照** → 該当するパターンの実装例を確認

## 使い分けの早見表

| 条件 | 使用するメソッド |
|------|----------------|
| 報酬配布がある | `applyUserTransactionChanges` |
| 課金基盤連携がある | `applyUserTransactionChanges` |
| usr_* への書き込みがある | `applyUserTransactionChanges` |
| ミッション進捗更新が必要 | `applyUserTransactionChanges` |
| 読み取り専用API | `processWithoutUserTransactionChanges` |
| データ変更なし | `processWithoutUserTransactionChanges` |

## 参照ドキュメント

- **[usage-conditions.md](usage-conditions.md)** - 使用条件と判断基準
- **[architecture.md](architecture.md)** - 仕組みの説明と処理フロー
- **[examples/with-transaction.md](examples/with-transaction.md)** - applyUserTransactionChanges 使用例
- **[examples/without-transaction.md](examples/without-transaction.md)** - processWithoutUserTransactionChanges 使用例
- **[patterns/gacha-pattern.md](patterns/gacha-pattern.md)** - ガチャ系実装パターン
- **[patterns/stage-pattern.md](patterns/stage-pattern.md)** - ステージ系実装パターン

## コード参照

- 実装元: `api/app/Domain/Common/Traits/UseCaseTrait.php`
