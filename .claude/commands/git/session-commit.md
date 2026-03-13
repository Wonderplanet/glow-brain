---
description: 本セッションの変更をコミット
argument-hint: [--push]
allowed-tools: Bash(git status:*), Bash(git diff:*), Bash(git log:*), Bash(git add:*), Bash(git commit:*), Bash(git push:*)
---

本セッションで行った変更をコミットしてください。

git status・git diff・git log を確認し、変更内容に沿ったコミットメッセージを作成してコミットしてください。

引数に `--push` が指定されている場合は、コミット後に `git push` も実行してください。
指定がない場合はコミットのみで終了してください。
