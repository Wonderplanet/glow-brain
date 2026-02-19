---
description: タスクを分析し、TODOを1メソッド/1クラス単位まで細分化、依存関係を整理、並列最適化してskills/subagentsを割り当て実行。
allowed-tools: Read, Write, Edit, Glob, Grep, Task, Skill, TodoWrite, AskUserQuestion
argument-hint: [タスクの説明] [--with-gap-analysis]
model: opus
---

# タスクオーケストレーター

## 使用方法

```bash
# 基本
/general:orchestrate-task 新規APIエンドポイントの実装とテスト

# ギャップ判定あり（不足スキル生成を検討）
/general:orchestrate-task 新規APIエンドポイントの実装とテスト --with-gap-analysis
```

## 引数

$ARGUMENTS

---

## 実行

`orchestrate-task` スキルを使用してタスクを実行：

```
Skill("orchestrate-task")
```

スキルの詳細は [.claude/skills/orchestrate-task/SKILL.md](../../skills/orchestrate-task/SKILL.md) を参照。
