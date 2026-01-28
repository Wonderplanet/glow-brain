---
description: PRの未解決レビューコメントを取得・一覧表示し、修正ワークフローを開始する。
argument-hint: "[PR番号]"
---

# PR未解決コメント対応コマンド

PRの未解決レビューコメントを取得・一覧表示し、修正ワークフローを開始するコマンドです。
スクリプト自体はコメントの取得のみを行い、実際の修正はClaude（pr-unresolved-commentsスキル）が対話的に実施します。

## 使用方法

```text
/api:fix-pr-comments [PR番号]
```

- PR番号を省略した場合、現在のブランチに対応するPRを自動検出します

## 実行内容

引数: $ARGUMENTS

このコマンドは `pr-unresolved-comments` スキルを使用します。

詳細な実行手順はスキルファイルを参照してください: `.claude/skills/pr-unresolved-comments/SKILL.md`

### 実行手順

```bash
bash .claude/skills/pr-unresolved-comments/get-unresolved-comments.sh $ARGUMENTS
```

上記スクリプトで未解決コメントを取得後、各コメントを順次確認・修正します。
