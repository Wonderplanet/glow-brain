---
name: usr-model-manager-user-id-check
description: Repository、UseCase、Service層におけるUsrModelManagerのuser ID checkエラーを調査・修正する。以下の場合に使用: (1) スタックトレースに「user id check」エラー、(2) UsrModelManager例外、(3) User IDミスマッチエラー、(4) 「user_id_check failed」メッセージ、(5) user_id不整合によるcachedGetエラー。Repository層でのuser_id不適切使用、SignUpUseCaseの特殊ケース、Service/UseCase層でのcachedGet誤用、権限外のユーザーデータアクセスなどのエラーパターンに対応。
---

# Debugging UsrModelManager User ID Check Errors

UsrModelManager機構におけるuser id checkエラーの原因調査と修正実装を支援するスキル。

## Instructions

### 1. エラーパターンを特定する

エラーメッセージとスタックトレースから、該当するエラーパターンを特定する。
参照: **[error-patterns.md](error-patterns.md)**

### 2. UsrModelManagerの仕組みを理解する

必要に応じて、UsrModelManagerの動作原理とuser id checkの目的を確認する。
参照リスト:
- **[guides/architecture.md](guides/architecture.md)** - UsrModelManagerの仕組み
- **[guides/debugging.md](guides/debugging.md)** - デバッグ手順

### 3. 修正を実装する

該当する層（Repository/UseCase/Service）の修正例を参照して実装する。
参照リスト:
- **[examples/fix-signup-usecase.md](examples/fix-signup-usecase.md)** - SignUpUseCaseの特殊パターン
- **[examples/fix-repository.md](examples/fix-repository.md)** - Repository層での修正
- **[examples/fix-service.md](examples/fix-service.md)** - Service/UseCase層での修正

## 参照ドキュメント

- **[error-patterns.md](error-patterns.md)** - user id checkエラーの全パターンと原因
- **[guides/architecture.md](guides/architecture.md)** - UsrModelManagerの仕組み
- **[guides/debugging.md](guides/debugging.md)** - エラー調査のステップバイステップガイド
- **[examples/fix-signup-usecase.md](examples/fix-signup-usecase.md)** - SignUpUseCase特有の修正例
- **[examples/fix-repository.md](examples/fix-repository.md)** - Repository実装での修正例
- **[examples/fix-service.md](examples/fix-service.md)** - Service/UseCase層での修正例
