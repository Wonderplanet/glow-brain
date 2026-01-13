# セットアップガイド

## 前提条件

- Python 3.11以上
- tmux
- gh CLI (GitHub CLI)
- Git
- Slack App（既存のものを使用）

## 1. 依存関係インストール

```bash
cd slack-claude-bot

# uvを使用（推奨）
uv sync

# または pip
pip install -e .
```

## 2. 環境変数設定

```bash
# .envファイルを作成
cp .env.example .env
```

`.env`を編集して以下の値を設定:

```bash
# Slack (必須)
SLACK_BOT_TOKEN=xoxb-your-bot-token
SLACK_APP_TOKEN=xapp-your-app-token
SLACK_WORKSPACE_URL=https://your-workspace.slack.com

# Anthropic (必須)
ANTHROPIC_API_KEY=sk-ant-your-api-key

# GitHub (必須)
GITHUB_TOKEN=ghp_your-token
GITHUB_REPO_OWNER=your-org
GITHUB_REPO_NAME=glow-brain
GITHUB_BASE_BRANCH=main

# パス（ローカル用、通常は変更不要）
WORKTREE_BASE_PATH=~/glow-worktrees
SOURCE_REPO_PATH=~/Documents/workspace/glow/glow-brain
DB_PATH=./data/sessions.db
```

### Slack Tokenの取得方法

1. https://api.slack.com/apps にアクセス
2. 既存のアプリを選択
3. **OAuth & Permissions** → **Bot User OAuth Token** をコピー (`xoxb-...`)
4. **Basic Information** → **App-Level Tokens** → Tokenを作成/コピー (`xapp-...`)

必要なスコープ:
- `app_mentions:read`
- `chat:write`
- `files:read`
- `reactions:write`
- `channels:history`

### GitHub Tokenの取得方法

```bash
# GitHub CLIで認証
gh auth login

# または Personal Access Tokenを作成
# Settings > Developer settings > Personal access tokens
# 必要なスコープ: repo
```

## 3. Worktreeディレクトリ作成

```bash
mkdir -p ~/glow-worktrees
```

## 4. Slack App設定

### Socket Mode有効化

1. Slack App設定画面
2. **Socket Mode** → Enable
3. App-Level Tokenを作成（`connections:write`スコープ）

### Event Subscriptions

1. **Event Subscriptions** → Enable Events
2. **Subscribe to bot events**:
   - `app_mention`

## 5. 起動

```bash
cd slack-claude-bot
uv run python -m src.main
```

ログが表示され、以下のようなメッセージが出れば成功:

```
slack_claude_bot_starting
initializing_components
components_initialized
starting_bot
slack_bot_started
```

## 6. 動作確認

### 基本的な確認

1. Slackでボットがいるチャンネルに移動
2. `@bot hello` とメンション
3. ボットから返信があればOK

### セッション継続確認

```
あなた: @bot このプロジェクトの構造を教えて
ボット: (回答)

あなた: @bot じゃあREADMEを見せて
ボット: (同じセッションで回答)
```

### GitHub PR確認

```
あなた: @bot READMEに概要を追加して
ボット: (変更を実施)
ボット: 📝 PRを作成しました: https://github.com/...
```

## トラブルシューティング

### エラー: "Missing required configuration"

`.env`ファイルが正しく設定されていません。すべての必須フィールドを確認してください。

### エラー: "Unable to determine which files to ship inside the wheel"

`pyproject.toml`に以下が含まれているか確認:

```toml
[tool.hatch.build.targets.wheel]
packages = ["src"]
```

### ボットが反応しない

1. Slack App設定でSocket Modeが有効か確認
2. `SLACK_APP_TOKEN`が正しいか確認
3. ログに`slack_bot_started`と表示されているか確認

### tmuxセッションが残る

期限切れセッションは自動削除されますが、手動削除も可能:

```bash
# セッション一覧
tmux list-sessions

# 特定セッション削除
tmux kill-session -t claude_xxx
```

### worktreeが残る

```bash
# worktree一覧
git worktree list

# 削除
git worktree remove ~/glow-worktrees/session_xxx
```

## デバッグ

### ログレベル変更

`.env`で設定:

```bash
LOG_LEVEL=DEBUG
```

### tmuxセッションに接続

```bash
tmux attach -t claude_xxx
```

### データベース確認

```bash
sqlite3 data/sessions.db
.tables
SELECT * FROM sessions;
.quit
```

## 本番環境への移行

ローカル検証が完了したら、EC2版へ移行します。

計画書: `.claude/plans/Slack-Claude Code連携システム 実装計画.md`
