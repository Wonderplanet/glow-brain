# シンプルコマンドパターン

引数なしの単純なコマンドのパターンです。

## 基本構造

```markdown
---
description: コマンドの説明
allowed-tools: 必要なツール
---

# タスク

実行したいタスクの説明
```

## 例: コードレビューコマンド

```markdown
---
description: Review the current changes for code quality and best practices
allowed-tools: Read, Grep, Glob
---

# Code Review

Review the staged changes in this repository.

## Focus Areas

1. Code quality and readability
2. Potential bugs or errors
3. Performance considerations
4. Security vulnerabilities
5. Best practices compliance

## Output

Provide a structured review with:
- Summary of changes
- Issues found (categorized by severity)
- Suggestions for improvement
```

## 例: 開発環境ステータス確認

```markdown
---
description: Check the status of the development environment
allowed-tools: Bash(docker:*), Bash(git:*)
---

# Environment Status

Check the current development environment status:

1. Docker containers status
2. Git branch and status
3. Any pending migrations or updates

Report any issues that need attention.
```

## 使用する場面

- 固定のタスクを実行する場合
- ユーザー入力が不要な場合
- 毎回同じ処理を行う場合
