# General Prompts

汎用的なGitHub Copilotプロンプト集です。

## プロンプト一覧

| プロンプト | 説明 | 元のClaude Codeコマンド |
|-----------|------|------------------------|
| `create-pr` | 現在のブランチの変更内容に基づいてPull Requestを作成または更新 | `/general:create-pr` |

## 使用方法

### VS Code Copilot Chatでの使用

1. Copilot Chatを開く（`Cmd+Shift+I` または `Ctrl+Shift+I`）
2. `@workspace /create-pr` と入力
3. プロンプトの指示に従って入力を行う

### 入力変数

#### create-pr

| 変数名 | 説明 | 必須 |
|--------|------|------|
| `backgroundInfo` | 背景情報（ClickUpタスクID、SlackURL等） | オプション |

## 変換元との対応

### create-pr

- **元ファイル**: `.claude/commands/general/create-pr.md`
- **変更点**:
  - YAMLフロントマター追加（mode: agent, tools: codebase, terminalCommand）
  - 引数を`${input:backgroundInfo}`形式に変換
  - フッターの署名を「Generated with GitHub Copilot」に変更
