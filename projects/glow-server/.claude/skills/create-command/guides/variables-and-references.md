# 変数と参照機能

コマンド本文で使用できる変数と参照機能について説明します。

## 引数変数

### $ARGUMENTS
すべての引数を単一の文字列として取得

```markdown
/fix-issue 123 high-priority
→ $ARGUMENTS = "123 high-priority"
```

### 位置指定引数 ($1, $2, ...)
個別の引数にアクセス

```markdown
/review-pr 456 high alice
→ $1 = "456"
→ $2 = "high"
→ $3 = "alice"
```

## 使用例

### $ARGUMENTSの使用

```markdown
---
argument-hint: [commit message]
---

Create a git commit with message: $ARGUMENTS
```

### 位置指定引数の使用

```markdown
---
argument-hint: [pr-number] [priority] [assignee]
---

Review PR #$1 with priority $2 and assign to $3.
```

## Bash実行 (!`command`)

コマンド実行結果をコンテキストに含める

```markdown
## Context

- Current git status: !`git status`
- Current branch: !`git branch --show-current`
- Recent commits: !`git log --oneline -5`
```

### 注意点
- 実行結果がそのまま展開される
- 長い出力は避ける
- エラー時の挙動を考慮する

## ファイル参照 (@path)

ファイル内容をコマンドに含める

```markdown
## Reference

- Current implementation: @src/utils/helpers.js
- Test file: @tests/unit/helpers.test.js
```

### 注意点
- ファイルパスはコマンド実行時のカレントディレクトリからの相対パス
- 大きなファイルは避ける

## 組み合わせ例

```markdown
---
allowed-tools: Bash(git:*), Read, Write
argument-hint: [message]
---

## Context

Current git status:
!`git status`

Current diff:
!`git diff HEAD`

## Task

Create a commit with the following message:
$ARGUMENTS

Follow the conventions in: @CONTRIBUTING.md
```
