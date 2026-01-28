---
name: sail-execution
description: |
  sail-wpコマンドを正しく実行するためのガイド。ルートディレクトリからの実行を徹底し、`cd api`/`cd admin`などの誤った実行を防止。コード品質ツール（phpcs/phpstan/deptrac）、テスト（phpunit）、マイグレーション（migrate/rollback）、Artisanコマンド（make:*/cache:clear）に対応。以下の場合に使用：(1) phpcs/phpstan/deptrac/testコマンドの実行、(2) マイグレーションの実行、(3) Artisanコマンドの実行、(4) ユーザーが「sail実行」「マイグレーション」「phpcs」「phpstan」またはsail-wpコマンド実行に言及した時。
---

# Executing Sail Commands

sail-wpコマンドを正しく実行するための基本ルールと実行方法を明確化するスキル。

## 絶対に守るべき3つのルール

1. **常にglow-serverルートディレクトリから実行**
2. **`cd api`や`cd admin`は絶対に使わない**
3. **`sail`（API用）と`sail admin`（Admin用）を使い分ける**

## よくある間違い例

❌ **間違い**
```bash
cd api && ../tools/bin/sail-wp exec php vendor/bin/phpcs app/Http/Controllers/EncyclopediaController.php
```

✅ **正しい**
```bash
sail phpcs app/Http/Controllers/EncyclopediaController.php
```

## Instructions

### 1. 基本ルールを確認

参照: **[common-rules.md](common-rules.md)**

### 2. 実行したいコマンドに応じて参照

- **コード品質チェック**: **[examples/code-quality.md](examples/code-quality.md)**
- **テスト実行**: **[examples/testing.md](examples/testing.md)**
- **マイグレーション操作**: **[examples/migration.md](examples/migration.md)**
- **Artisanコマンド実行**: **[examples/artisan.md](examples/artisan.md)**

## 参照ドキュメント

- **[common-rules.md](common-rules.md)** - 絶対に守るべきルール、よくある間違いと正解の対比、sail-wpの仕組み
- **[examples/code-quality.md](examples/code-quality.md)** - phpcs/phpcbf/phpstan/deptracの実行例
- **[examples/testing.md](examples/testing.md)** - テスト実行の具体例
- **[examples/migration.md](examples/migration.md)** - マイグレーション操作の実行例
- **[examples/artisan.md](examples/artisan.md)** - Artisanコマンドの実行例
