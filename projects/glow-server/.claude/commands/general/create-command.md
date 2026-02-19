---
description: 新しいカスタムスラッシュコマンドを作成する。YAMLフロントマター、引数変数、ツール権限設定を含む完全なコマンドファイルを生成する。
argument-hint: [コマンド名] [コマンドの説明]
---

# カスタムスラッシュコマンド作成

新しいカスタムスラッシュコマンドの作成を支援します。

## 引数

- 第1引数: コマンド名（kebab-case、例: fix-errors）
- 第2引数以降: コマンドの説明

引数: $ARGUMENTS

## 実行内容

`create-command`スキルを参照して、以下の手順でカスタムコマンドを作成します：

### 1. 基本構造の理解

コマンドファイルの構成要素を確認します。

参照: **[基本構造ガイド](.claude/skills/create-command/guides/command-structure.md)**

### 2. YAMLフロントマターの設計

以下のフィールドを検討します：
- `description`: コマンドの説明（必須推奨）
- `allowed-tools`: 使用可能なツール（最小権限の原則）
- `model`: 使用するモデル（必要な場合のみ）
- `argument-hint`: 引数のヒント

参照: **[フロントマター設計](.claude/skills/create-command/guides/frontmatter-design.md)**

### 3. コマンド本文の設計

変数と参照機能を適切に使用します：
- `$ARGUMENTS`: 全引数を文字列として取得
- `$1, $2...`: 位置指定引数
- Bash実行結果の埋め込み: `!(コマンド)` 形式で使用（バッククォートで囲む）
- `@path`: ファイル内容を参照

参照: **[変数と参照機能](.claude/skills/create-command/guides/variables-and-references.md)**

### 4. 配置場所の決定

- プロジェクトレベル: `.claude/commands/`（チーム共有）
- ユーザーレベル: `~/.claude/commands/`（個人用）

本プロジェクトでは以下のディレクトリを使用：
- `general/`: 汎用コマンド
- `api/`: API開発関連
- `admin/`: 管理画面関連
- `sdd/`: SDD関連

参照: **[配置ルール](.claude/skills/create-command/guides/placement-rules.md)**

### 5. コマンドファイルの作成

決定した内容に基づいてファイルを生成します。

### 6. 検証

作成したコマンドが正しく動作するか確認します。

参照: **[検証チェックリスト](.claude/skills/create-command/patterns/validation-checklist.md)**

---

create-commandスキルを使用して、引数に基づいたカスタムコマンドを作成してください。

## 質問

コマンドの詳細を教えてください：

1. **目的**: 何を達成するコマンドですか？
2. **入力**: どのような引数が必要ですか？
3. **出力**: 何を出力/実行しますか？
4. **ツール**: どのツールを使用しますか？
