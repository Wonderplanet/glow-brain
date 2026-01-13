# Slack-Claude Bot (ローカル検証版)

Slackからメンションでトリガーし、Claude Codeセッションを操作するボット。

## 概要

- Slack Bot（@mention方式）でClaude Codeを起動
- 同一スレッドで会話を継続
- git worktreeで環境分離
- tmux + pexpectでClaudeセッション管理
- コード変更時に自動でGitHub PR作成

## セットアップ

### 1. 依存関係インストール

```bash
cd slack-claude-bot
uv sync
```

### 2. 環境変数設定

```bash
cp .env.example .env
# .envを編集してトークンを設定
```

必要な環境変数:
- `SLACK_BOT_TOKEN`: Slack Bot Token (xoxb-...)
- `SLACK_APP_TOKEN`: Slack App Token (xapp-...)
- `ANTHROPIC_API_KEY`: Anthropic API Key
- `GITHUB_TOKEN`: GitHub Personal Access Token (PRivate用)

### 3. worktreeディレクトリ作成

```bash
mkdir -p ~/glow-worktrees
```

### 4. Slack App設定

必要なスコープ:
- `app_mentions:read`
- `chat:write`
- `files:read`
- `reactions:write`
- `channels:history`

Socket Mode: 有効化

### 5. GitHub CLI認証

```bash
gh auth login
```

## 起動

```bash
cd slack-claude-bot
uv run python -m src.main
```

## 使い方

1. Slackで @bot にメンション
   ```
   @bot このプロジェクトの構造を教えて
   ```

2. 同じスレッドで会話を継続
   ```
   @bot じゃあREADMEを更新して
   ```

3. コード変更時は自動でPR作成
   - Slack情報（チャンネル、ユーザー、スレッドリンク）を含む

## ディレクトリ構成

```
slack-claude-bot/
├── src/
│   ├── main.py              # エントリーポイント
│   ├── config.py            # 設定管理
│   ├── slack/               # Slack Bot
│   ├── claude/              # Claude実行
│   ├── session/             # セッション管理
│   ├── worktree/            # Worktree管理
│   └── github/              # GitHub PR作成
├── data/
│   └── sessions.db          # SQLite DB
├── pyproject.toml
├── .env
└── .env.example
```

## デバッグ

### tmuxセッション確認

```bash
# セッション一覧
tmux list-sessions | grep claude_

# セッションにアタッチ
tmux attach -t claude_xxx
```

### worktree確認

```bash
git worktree list
```

### データベース確認

```bash
sqlite3 data/sessions.db "SELECT * FROM sessions;"
```

## トラブルシューティング

### エラー: "Maximum concurrent sessions reached"

同時セッション数が上限に達しています。`.env`の`MAX_CONCURRENT_SESSIONS`を増やすか、古いセッションを削除してください。

### エラー: "Failed to create worktree"

`~/glow-worktrees`ディレクトリが存在するか確認してください。

### Claude実行がタイムアウトする

`.env`の`CLAUDE_TIMEOUT_SECONDS`を増やしてください。

## 本番環境への移行

ローカル検証が完了したら、EC2版へ移行します。
計画書: `.claude/plans/Slack-Claude Code連携システム 実装計画.md`
