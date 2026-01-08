---
description: 新しいClaude Code subagentを作成する
allowed-tools: Write, Read, Glob, Skill
argument-hint: [エージェント名] [エージェントの説明]
model: sonnet
---

# サブエージェント作成

create-subagentスキルを使用して、新しいClaude Code subagentを作成します。

## 引数

- 第1引数: エージェント名（kebab-case、例: api-error-handler）
- 第2引数以降: エージェントの説明

引数: $ARGUMENTS

## 実行内容

`.claude/skills/create-subagent/`スキルを参照して、以下の手順でサブエージェントを作成します：

1. **基本構造の理解** - YAML frontmatter、必須フィールドを確認
2. **パターン選択** - API開発系、テスト系、運用系から適切なパターンを選択
3. **既存エージェント参照** - 実例を確認してベストプラクティスを学習
4. **ファイル作成** - `.claude/agents/`に新しいエージェントファイルを作成
5. **トリガー条件の明確化** - descriptionに使用タイミングを含める

create-subagentスキルを使用して、引数に基づいたサブエージェントを作成してください。
