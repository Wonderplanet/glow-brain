---
name: sail-check-fixer
description: |
  sail check で検出される全てのコード品質エラー（phpcs/phpstan/deptrac/phpunit）を自動解消する。以下の場合に使用：(1) sail checkやコード品質チェックの実行、(2) phpcs/phpstan/deptracエラーの修正、(3) PR前の品質検証、(4) 静的解析エラーの解決、(5) コーディング規約違反の修正、(6) ユーザーが「sail check」「品質チェック」「静的解析」「コーディング規約」「PR前のチェック」に言及した時。
---

# Fixing Sail Check Errors

`sail check`コマンドを実行して、全てのエラーを解消するスキル。

## 概要

`sail check`は以下の5つのチェックを順次実行します：

1. **phpcbf** (自動修正) - コーディング規約違反の自動修正
2. **phpcs** (手動確認) - 自動修正できない規約違反の確認
3. **phpstan** (静的解析) - 型エラーやコード品質の問題を検出
4. **deptrac** (アーキテクチャ) - レイヤー間の依存関係違反を検出
5. **test** (テスト実行) - PHPUnitテストの実行とカバレッジ確認

## Instructions

### 1. `sail check`を実行してエラーを確認

```bash
./tools/bin/sail-wp check
```

参照: **[workflow.md](workflow.md)** - 全体的なワークフロー

### 2. エラーを優先順に解消

推奨順序：
1. **phpcbf (自動修正)** → 参照: **[phpcs-phpcbf-guide.md](phpcs-phpcbf-guide.md)**
2. **phpcs (手動修正)** → 参照: **[phpcs-phpcbf-guide.md](phpcs-phpcbf-guide.md)**
3. **phpstan (型エラー修正)** → 参照: **[phpstan-guide.md](phpstan-guide.md)**
4. **deptrac (依存関係修正)** → 参照: **[deptrac-guide.md](deptrac-guide.md)**
5. **test (テスト修正)** → 参照: **[test-guide.md](test-guide.md)**

### 3. よくあるエラーは共通パターンを参照

参照: **[common-errors.md](common-errors.md)** - 頻出エラーと修正例

### 4. 全てのチェックが成功するまで繰り返す

各チェックをクリアしたら、再度`sail check`を実行して全体確認。

## 参照ドキュメント

- **[workflow.md](workflow.md)** - エラー解消の全体フロー、優先順位、作業戦略
- **[phpcs-phpcbf-guide.md](phpcs-phpcbf-guide.md)** - コーディング規約チェック（自動/手動修正）
- **[phpstan-guide.md](phpstan-guide.md)** - 静的解析エラーの理解と修正方法
- **[deptrac-guide.md](deptrac-guide.md)** - アーキテクチャ違反の理解と修正方法
- **[test-guide.md](test-guide.md)** - テストエラーの分類と修正方法
- **[common-errors.md](common-errors.md)** - よくあるエラーパターンと具体的な修正例
