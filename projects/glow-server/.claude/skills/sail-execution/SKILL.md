---
name: "Executing Sail Commands"
description: glow-serverでsail-wpコマンドを正しく実行する際に使用。glow-serverルートディレクトリから`cd`なしで実行し、`sail`または`sail admin`で全コマンドを実行。phpcs/phpcbf/phpstan/deptrac/test/artisan/migrate等の主要コマンドに対応し、`cd api`のようなディレクトリ移動ミスを完全に防ぐ。
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
