---
name: create-command
description: |
  Claude Codeカスタムスラッシュコマンド作成。YAML frontmatter（name、aliases、description、tools-allowed）の定義、引数変数（$ARGUMENTS、$1、$2等）の設定、Bash実行（!`cmd`）とファイル参照（@path）の実装、プロジェクト/ユーザーレベルの配置、名前空間機能をサポート。以下の場合に使用: (1) 新しいスラッシュコマンドを作成する、(2) カスタムコマンドを追加する、(3) /コマンドを実装する、(4) プロジェクト固有のコマンド自動化、(5) 繰り返し使うコマンド操作の効率化、(6) .claude/commands/または~/.claude/commands/への配置
---

# Creating Custom Slash Commands

Claude Codeのカスタムスラッシュコマンドを作成するためのスキルです。`.claude/commands/`ディレクトリに新しいコマンドファイルを生成します。

## Instructions

### 1. コマンドの基本情報を収集

ユーザーにコマンドの目的、名前、配置場所を質問します。

参照: **[基本構造ガイド](guides/command-structure.md)**

### 2. YAMLフロントマターを設計

コマンドに必要なメタデータを決定します。

参照: **[フロントマター設計](guides/frontmatter-design.md)**

### 3. コマンド本文を設計

変数（$ARGUMENTS、$1など）、Bash実行（!`cmd`）、ファイル参照（@path）を適切に使用します。

参照: **[変数と参照機能](guides/variables-and-references.md)**

### 4. 配置場所を決定

プロジェクトレベル vs ユーザーレベル、名前空間の使用を決定します。

参照: **[配置ルール](guides/placement-rules.md)**

### 5. コマンドファイルを作成

決定した内容に基づいてファイルを生成します。

参照: **[検証チェックリスト](patterns/validation-checklist.md)**

## 参照ドキュメント

### ガイド
- **[基本構造](guides/command-structure.md)** - コマンドファイルの構成要素
- **[フロントマター設計](guides/frontmatter-design.md)** - YAMLフィールドの使い方
- **[変数と参照機能](guides/variables-and-references.md)** - $ARGUMENTS、!`cmd`、@pathの使用法
- **[配置ルール](guides/placement-rules.md)** - プロジェクト/ユーザーレベルの選択

### パターン
- **[シンプルコマンド](patterns/simple-command.md)** - 引数なしの単純なコマンド
- **[引数付きコマンド](patterns/with-arguments.md)** - 引数を受け取るコマンド
- **[コンテキスト埋め込みコマンド](patterns/with-context.md)** - Bash実行やファイル参照を使うコマンド
- **[検証チェックリスト](patterns/validation-checklist.md)** - 作成後の確認項目

### 実装例
- **[既存コマンド一覧](examples/existing-commands.md)** - プロジェクトの既存コマンド
- **[一般的なユースケース](examples/common-usecases.md)** - よくある使用パターン
