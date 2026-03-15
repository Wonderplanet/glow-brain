# コンテキスト埋め込みコマンドパターン

Bash実行（!`cmd`）やファイル参照（@path）を使用してコンテキストを提供するパターンです。

## Bash実行パターン

```markdown
---
description: Analyze current git status and suggest next steps
allowed-tools: Bash(git:*), Read
---

# Git Analysis

## Current State

Branch: !`git branch --show-current`

Status:
!`git status --short`

Recent commits:
!`git log --oneline -5`

## Task

Based on the current git state, suggest the next steps.
```

## ファイル参照パターン

```markdown
---
description: Review code against project guidelines
allowed-tools: Read, Grep
---

# Code Review with Guidelines

## Project Guidelines

@CONTRIBUTING.md

## Coding Standards

@.editorconfig

## Task

Review the current changes against these guidelines.
```

## 組み合わせパターン

```markdown
---
description: Create a migration based on current schema
allowed-tools: Bash(sail:*), Read, Write
argument-hint: [table-name]
---

# Migration Creation

## Context

Current migrations:
!`ls -la database/migrations | tail -10`

Database connection info:
!`cat .env | grep DB_`

## Schema Reference

@database/schema.md

## Task

Create a new migration for table: $ARGUMENTS

Follow the existing migration patterns in this project.
```

## ベストプラクティス

### Bash実行
- 出力が短いコマンドを選ぶ
- `--short`や`-1`などのオプションで出力を制限
- パイプで必要な部分だけ抽出

### ファイル参照
- 小さなファイルを参照する
- 必要な部分だけを参照する
- 存在確認を考慮する

### 注意点
- 大量の出力は避ける
- 機密情報を含むコマンド/ファイルに注意
- エラー時の挙動を考慮する
