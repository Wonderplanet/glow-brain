# 引数付きコマンドパターン

ユーザーからの引数を受け取るコマンドのパターンです。

## $ARGUMENTSを使用するパターン

引数全体を1つの文字列として扱う場合

```markdown
---
description: Create a git commit with the provided message
allowed-tools: Bash(git:*)
argument-hint: [commit message]
---

# Git Commit

Create a commit with the following message:

$ARGUMENTS

Follow these guidelines:
- Use conventional commit format
- Keep the first line under 72 characters
```

## 位置指定引数を使用するパターン

複数の引数を個別に扱う場合

```markdown
---
description: Review a pull request with specified options
allowed-tools: Read, Grep, Glob, Bash(gh:*)
argument-hint: [pr-number] [focus-area]
---

# PR Review

Review PR #$1 with focus on: $2

## Steps

1. Fetch PR details using `gh pr view $1`
2. Review changes with emphasis on $2
3. Provide structured feedback
```

## 引数の使い分け

### $ARGUMENTS を使う場合
- 自然言語の入力（コミットメッセージ、説明文）
- 引数の区切りが曖昧な場合
- 柔軟な入力を許容する場合

### $1, $2... を使う場合
- 明確に分離された複数の値
- 各引数に異なる意味がある場合
- 構造化された入力

## 引数ヒントの例

```yaml
# 単一引数
argument-hint: [message]
argument-hint: [file-path]
argument-hint: [branch-name]

# 複数引数
argument-hint: [source] [destination]
argument-hint: [pr-number] [priority] [assignee]

# 選択肢を示す
argument-hint: add [id] | remove [id] | list
argument-hint: --dry-run | --force
```

## 注意点

- 引数が空の場合の挙動を考慮する
- 引数の検証はClaude側で行う
- 必須引数と任意引数を明確にする
