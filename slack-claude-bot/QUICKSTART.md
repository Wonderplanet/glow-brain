# クイックスタートガイド

このガイドでは、ローカル環境でSlack-Claude Botを最速で起動する手順を説明します。

---

## 前提条件

以下がすべてインストール・設定済みであることを確認してください:

- ✅ **Claude Code** がインストール済み (`claude` コマンドが使える)
- ✅ **Maxプラン**でログイン済み (`claude` を起動して使える状態)
- ✅ **tmux** がインストール済み
- ✅ **gh CLI (GitHub CLI)** がインストール済み
- ✅ **既存のSlack App**がある

### 確認コマンド

```bash
# Claude Code
claude --version

# tmux
tmux -V

# GitHub CLI
gh --version

# Claude Codeがログイン済みか確認
claude
# → 起動したらCtrl+C で終了
```

---

## セットアップ

### 1. ディレクトリ移動

```bash
cd slack-claude-bot
```

### 2. 依存関係インストール

```bash
uv sync
```

### 3. worktreeディレクトリ作成

```bash
mkdir -p ~/glow-worktrees
```

### 4. 環境変数設定

```bash
# テンプレートをコピー
cp .env.example .env

# エディタで開く
vim .env
# または
code .env
```

以下の**最小構成**で設定します:

```bash
# ========================================
# Slack（必須）
# ========================================
SLACK_BOT_TOKEN=xoxb-...
SLACK_APP_TOKEN=xapp-...
SLACK_WORKSPACE_URL=https://your-workspace.slack.com

# ========================================
# GitHub（必須）
# ========================================
GITHUB_TOKEN=ghp_...
GITHUB_REPO_OWNER=your-org
GITHUB_REPO_NAME=glow-brain
GITHUB_BASE_BRANCH=main

# ========================================
# パス（通常は変更不要）
# ========================================
SOURCE_REPO_PATH=/Users/junki.mizutani/Documents/workspace/glow/glow-brain
WORKTREE_BASE_PATH=~/glow-worktrees
DB_PATH=./data/sessions.db
LOG_LEVEL=INFO
```

**重要**:
- `ANTHROPIC_API_KEY` は**不要**です（Maxプランのログインを使用します）
- トークンの取得方法は `ENV_SETUP_GUIDE.md` を参照

---

## 起動

```bash
uv run python -m src.main
```

### 成功時のログ

以下のようなログが表示されればOK:

```
slack_claude_bot_starting
initializing_components
database_initialized
components_initialized
starting_bot
slack_bot_started
```

---

## 起動後の操作

### 1. ボットにメンション（新規セッション開始）

Slackでボットがいるチャンネルに移動して、メンションします:

```
@bot こんにちは
```

ボットから返信があればOK。

### 2. 会話を継続（同じスレッド内）

同じスレッド内で質問を続けると、**同一セッション**で継続します:

```
あなた: @bot このプロジェクトの構造を教えて
ボット: (回答)

あなた: @bot じゃあREADME.mdを見せて
ボット: (同じセッションで回答)
```

### 3. コード変更を依頼

コード変更を依頼すると、**自動的にPRが作成**されます:

```
あなた: @bot README.mdに概要セクションを追加して
ボット: (変更を実施)
ボット: 📝 PRを作成しました: https://github.com/...
```

PRには以下の情報が含まれます:
- Slackチャンネル名
- リクエストユーザー名
- スレッドリンク

---

## デバッグコマンド

### tmuxセッション確認

```bash
# セッション一覧
tmux list-sessions | grep claude_

# セッションにアタッチ（Claudeの状態を直接見る）
tmux attach -t claude_xxx

# デタッチ（戻る）
# Ctrl+B → D
```

### worktree確認

```bash
git worktree list
```

### データベース確認

```bash
sqlite3 data/sessions.db "SELECT id, slack_thread_id, status FROM sessions;"
```

---

## 停止

### ボットを停止

```bash
# Ctrl+C でボットを停止
```

### 残ったtmuxセッションを削除

```bash
# 全セッション削除（注意: 他のtmuxセッションも削除されます）
tmux kill-server

# 個別削除（推奨）
tmux kill-session -t claude_xxx
```

---

## トラブルシューティング

### エラー: "Missing required configuration"

`.env`ファイルが正しく設定されていません。以下を確認してください:

- `SLACK_BOT_TOKEN` が `xoxb-` で始まる
- `SLACK_APP_TOKEN` が `xapp-` で始まる

詳細は `ENV_SETUP_GUIDE.md` を参照してください。

### ボットが反応しない

1. Slack App設定で**Socket Mode**が有効か確認
2. `SLACK_APP_TOKEN`が正しいか確認
3. ログに`slack_bot_started`と表示されているか確認

### Claude実行がタイムアウトする

`.env`の`CLAUDE_TIMEOUT_SECONDS`を増やしてください:

```bash
CLAUDE_TIMEOUT_SECONDS=600  # 10分に延長
```

### tmuxセッションが残り続ける

期限切れセッションは自動削除されますが、手動削除も可能:

```bash
tmux kill-session -t claude_xxx
```

---

## 次のステップ

ローカル検証が完了したら、EC2版へ移行します。

詳細は `.claude/plans/Slack-Claude Code連携システム 実装計画.md` を参照してください。
