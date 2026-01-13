# Slack-Claude Code連携システム ローカル検証版

## 概要

EC2本番環境構築前の**ローカルPC検証版**。
Slackからメンションでトリガーし、ローカルPC上のClaude Codeセッションを操作する。

## 目的

- アーキテクチャの検証
- Slack Bot連携の動作確認
- tmux + pexpect方式の検証
- GitHub PR作成フローの確認

## 技術選定

| 項目 | 選定 |
|------|------|
| トリガー | Slack Bot（@mention方式） |
| セッション管理DB | SQLite |
| 並行セッション数 | 1-3（検証用） |
| 実行環境 | **ローカルPC (macOS)** |
| Claude制御 | tmux + pexpect（インタラクティブセッション永続化） |
| 言語 | Python 3.11+ |

## EC2版との違い

| 項目 | ローカル検証版 | EC2本番版 |
|------|---------------|-----------|
| 実行環境 | macOS | Amazon Linux / Ubuntu |
| worktree配置 | `~/glow-worktrees/` | `/var/glow/glow-worktrees/` |
| データ配置 | プロジェクト内 `data/` | `/var/glow/data/` |
| 起動方式 | 手動 or launchd | systemd |
| 同時セッション | 1-3 | 5-10 |
| Google Drive | オプション | 必須 |

## アーキテクチャ

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           Local PC (macOS)                              │
│  ┌─────────────────┐    ┌──────────────────┐    ┌───────────────────┐ │
│  │   Slack Bot     │───▶│ Session Manager  │───▶│  Claude Executor  │ │
│  │  (Bolt Python)  │    │    + SQLite      │    │ (tmux + pexpect)  │ │
│  └────────┬────────┘    └────────┬─────────┘    └─────────┬─────────┘ │
│           │                      │                        │            │
│           │              ┌───────▼────────┐       ┌───────▼────────┐  │
│           │              │  Worktree      │       │  tmux sessions │  │
│           │              │  Manager       │       │  ┌───────────┐ │  │
│           │              │ (git worktree) │       │  │ claude_01 │ │  │
│           │              └───────┬────────┘       │  └───────────┘ │  │
│           │                      │                └────────────────┘  │
│           │       ~/glow-worktrees/                                    │
│           │         └── session_xxx/                                   │
│           ▼                                                            │
│  ┌────────────────┐    ┌────────────────────────────────────────────┐ │
│  │ GitHub PR Mgr  │───▶│              GitHub API (gh CLI)           │ │
│  └────────────────┘    └────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────┘
```

**データフロー**:
1. Slackメンション → Slack Bot受信
2. Session Manager: スレッドIDからセッション検索/作成
3. Worktree Manager: 新規セッションならworktree作成
4. Claude Executor: tmuxセッション内のclaudeにpexpectでプロンプト送信
5. GitHub PR Manager: 変更をcommit → push → PR作成
6. 出力取得 → Slackスレッドに返信（PRリンク含む）

## ディレクトリ構成（ローカル）

```
glow-brain/
├── slack-claude-bot/                # アプリケーション
│   ├── src/
│   │   ├── __init__.py
│   │   ├── main.py
│   │   ├── config.py
│   │   ├── slack/
│   │   │   ├── __init__.py
│   │   │   ├── bot.py
│   │   │   └── handlers.py
│   │   ├── claude/
│   │   │   ├── __init__.py
│   │   │   └── executor.py
│   │   ├── session/
│   │   │   ├── __init__.py
│   │   │   ├── manager.py
│   │   │   └── db.py
│   │   ├── worktree/
│   │   │   ├── __init__.py
│   │   │   └── manager.py
│   │   └── github/
│   │       ├── __init__.py
│   │       └── pr_manager.py
│   ├── data/                        # ローカル用データディレクトリ
│   │   └── sessions.db
│   ├── pyproject.toml
│   ├── .env                         # ローカル環境変数（gitignore）
│   └── .env.example
│
└── .claude/plans/                   # 計画ファイル

~/glow-worktrees/                    # worktree配置（ホームディレクトリ）
└── session_xxx/
```

## 環境変数（ローカル用）

```bash
# .env.example

# Slack
SLACK_BOT_TOKEN=xoxb-xxx
SLACK_APP_TOKEN=xapp-xxx
SLACK_WORKSPACE_URL=https://your-workspace.slack.com

# Anthropic
ANTHROPIC_API_KEY=sk-ant-xxx

# GitHub
GITHUB_TOKEN=ghp_xxx
GITHUB_REPO_OWNER=your-org
GITHUB_REPO_NAME=glow-brain
GITHUB_BASE_BRANCH=main

# 設定（ローカル用に調整）
MAX_CONCURRENT_SESSIONS=3
SESSION_TTL_HOURS=8
CLAUDE_TIMEOUT_SECONDS=300

# パス（ローカル用）
WORKTREE_BASE_PATH=~/glow-worktrees
SOURCE_REPO_PATH=~/Documents/workspace/glow/glow-brain
DB_PATH=./data/sessions.db
```

## 実装ステップ（ローカル検証版）

### Step 1: プロジェクト初期化
- [ ] ディレクトリ構成作成
- [ ] pyproject.toml作成
- [ ] .env.example作成
- [ ] data/ディレクトリ作成

### Step 2: コア機能実装
- [ ] config.py（環境変数読み込み）
- [ ] db.py（SQLiteスキーマ）
- [ ] worktree/manager.py
- [ ] session/manager.py

### Step 3: Claude Executor実装
- [ ] tmux + pexpect方式の実装
- [ ] 出力パース処理
- [ ] ANSIエスケープ除去

### Step 4: GitHub連携実装
- [ ] pr_manager.py
- [ ] commit/push処理
- [ ] gh CLIでPR作成

### Step 5: Slack Bot実装
- [ ] bot.py（Socket Mode）
- [ ] handlers.py（イベント処理）
- [ ] メッセージ送信

### Step 6: 統合テスト
- [ ] 全コンポーネント結合
- [ ] E2Eテスト

## 検証シナリオ

### 1. 基本動作確認
```bash
# アプリケーション起動
cd slack-claude-bot
uv run python -m src.main

# Slackで @bot にメンション
# → 返答が来ることを確認
```

### 2. セッション継続確認
```
Slack: @bot このプロジェクトの構造を教えて
Bot: (回答)

Slack: @bot じゃあmain.pyを見せて
Bot: (同じセッションで継続して回答)
```

### 3. GitHub PR作成確認
```
Slack: @bot READMEに概要を追加して
Bot: (変更を実施)
Bot: PRを作成しました: https://github.com/xxx/xxx/pull/123
```

### 4. tmuxセッション確認
```bash
# tmuxセッション一覧
tmux list-sessions

# 特定セッションにアタッチして確認
tmux attach -t claude_xxx
```

## ローカル起動方法

```bash
# 1. 依存関係インストール
cd slack-claude-bot
uv sync

# 2. 環境変数設定
cp .env.example .env
# .envを編集

# 3. worktreeディレクトリ作成
mkdir -p ~/glow-worktrees

# 4. 起動
uv run python -m src.main
```

## デバッグ用コマンド

```bash
# tmuxセッション確認
tmux list-sessions | grep claude_

# worktree確認
git worktree list

# SQLite確認
sqlite3 data/sessions.db "SELECT * FROM sessions;"

# ログ確認（stdoutに出力）
# アプリケーションはフォアグラウンドで実行
```

## 本番移行時の変更点

ローカル検証完了後、EC2版への移行時に変更が必要な箇所：

1. **環境変数のパス**
   - `WORKTREE_BASE_PATH`: `~/glow-worktrees` → `/var/glow/glow-worktrees`
   - `SOURCE_REPO_PATH`: ローカルパス → `/var/glow/glow-brain`
   - `DB_PATH`: `./data/sessions.db` → `/var/glow/data/sessions.db`

2. **並行数設定**
   - `MAX_CONCURRENT_SESSIONS`: 3 → 10

3. **起動方式**
   - 手動起動 → systemdサービス

4. **Google Drive連携**
   - オプション → 必須（credentials設定）

5. **ログ出力**
   - stdout → ファイル出力

## 備考

### tmux + pexpect検証ポイント

- プロンプト検出パターン（`>` など）の確認
- ANSIエスケープシーケンスの除去方法
- タイムアウト値の調整
- 長文出力時の挙動

### Slack Bot検証ポイント

- Socket Modeの接続安定性
- メンション検出の確実性
- スレッド返信の正確性
- ファイル添付の受け取り（後回し可）

### GitHub PR検証ポイント

- worktree内でのgit操作
- ブランチ作成・push
- gh CLIでのPR作成
- PR本文へのSlack情報埋め込み
