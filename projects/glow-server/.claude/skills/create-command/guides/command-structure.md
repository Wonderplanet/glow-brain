# コマンドファイルの基本構造

カスタムスラッシュコマンドはMarkdownファイルとして作成します。

## ファイル構成

```markdown
---
description: コマンドの説明
allowed-tools: 使用可能なツール
model: 使用するモデル
argument-hint: 引数のヒント
---

# コマンドのタイトル

コマンドの本文（プロンプト）
```

## 必須要素

### 1. ファイル名
- kebab-case形式（例: `create-migration.md`）
- コマンド名はファイル名から導出（`.md`を除いた部分）
- サブディレクトリを使用して名前空間を作成可能

### 2. YAMLフロントマター
- `---`で囲まれたYAML形式のメタデータ
- 最低限`description`を含めることを推奨

### 3. コマンド本文
- Markdown形式で記述
- 変数、Bash実行、ファイル参照が使用可能

## ファイル配置

```
プロジェクトレベル:
.claude/commands/
├── simple-command.md           # /simple-command
├── api/
│   └── generate.md             # /generate (project:api)
└── general/
    └── review.md               # /review (project:general)

ユーザーレベル:
~/.claude/commands/
└── my-command.md               # /my-command (user)
```

## 名前空間の競合

同名コマンドが複数存在する場合:
- プロジェクトレベルが優先される
- サブディレクトリで区別される（`/help`で確認可能）
