# Slack-Claude Code連携システム 実装計画

## 概要

Slackからメンションでトリガーし、EC2上のClaude Codeセッションを操作するシステム。
同一スレッドでは同一セッションを継続使用し、生成物はSlack/Google Driveに転記する。

## 確定事項

- **Slack App**: 既存Appを活用
- **実装先**: `glow-brain/slack-claude-bot/` （このリポジトリ内に実装）

## 技術選定

| 項目 | 選定 |
|------|------|
| トリガー | Slack Bot（@mention方式） |
| セッション管理DB | SQLite |
| 並行セッション数 | 5-10 |
| 実行環境 | EC2（リモートサーバー） |
| Claude制御 | **tmux + pexpect（インタラクティブセッション永続化）** |
| 言語 | Python 3.11+ |

## アーキテクチャ

```
┌─────────────────────────────────────────────────────────────────────────┐
│                              EC2 Instance                               │
│  ┌─────────────────┐    ┌──────────────────┐    ┌───────────────────┐ │
│  │   Slack Bot     │───▶│ Session Manager  │───▶│  Claude Executor  │ │
│  │  (Bolt Python)  │    │    + SQLite      │    │ (tmux + pexpect)  │ │
│  └────────┬────────┘    └────────┬─────────┘    └─────────┬─────────┘ │
│           │                      │                        │            │
│           │              ┌───────▼────────┐       ┌───────▼────────┐  │
│           │              │  Worktree      │       │  tmux sessions │  │
│           │              │  Manager       │       │  ┌───────────┐ │  │
│           │              │ (git worktree) │       │  │ claude_01 │ │  │
│           │              └───────┬────────┘       │  │ claude_02 │ │  │
│           │                      │                │  │ ...       │ │  │
│           │       /var/glow-worktrees/            │  └───────────┘ │  │
│           │         └── session_xxx/              └────────────────┘  │
│           ▼                                                            │
│  ┌────────────────┐    ┌────────────────────────────────────────────┐ │
│  │ File Uploader  │───▶│              Google Drive API              │ │
│  └────────────────┘    └────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────┘
```

**データフロー**:
1. Slackメンション → Slack Bot受信
2. Session Manager: スレッドIDからセッション検索/作成
3. Worktree Manager: 新規セッションならworktree作成
4. Claude Executor: tmuxセッション内のclaudeにpexpectでプロンプト送信
5. **GitHub PR Manager: 変更をcommit → push → PR作成**
6. 出力取得 → Slackスレッドに返信（PRリンク含む）
7. ファイル生成時 → Google Driveアップロード → リンク投稿

## ディレクトリ構成

### 開発環境（glow-brain内）

```
glow-brain/
├── slack-claude-bot/                # 今回作成するアプリケーション
│   ├── src/
│   │   ├── __init__.py
│   │   ├── main.py                  # エントリーポイント
│   │   ├── config.py                # 設定管理
│   │   ├── slack/
│   │   │   ├── __init__.py
│   │   │   ├── bot.py               # Slack Bot本体
│   │   │   └── handlers.py          # イベントハンドラ
│   │   ├── claude/
│   │   │   ├── __init__.py
│   │   │   └── executor.py          # Claude CLI実行
│   │   ├── session/
│   │   │   ├── __init__.py
│   │   │   ├── manager.py           # セッション管理
│   │   │   └── db.py                # SQLite操作
│   │   ├── worktree/
│   │   │   ├── __init__.py
│   │   │   └── manager.py           # Worktree管理
│   │   ├── upload/
│   │   │   ├── __init__.py
│   │   │   └── google_drive.py      # Google Drive連携
│   │   └── github/
│   │       ├── __init__.py
│   │       └── pr_manager.py        # GitHub PR作成
│   ├── pyproject.toml
│   ├── .env.example
│   ├── README.md
│   └── systemd/
│       └── slack-claude-bot.service
└── ...（既存のglow-brain構造）
```

### 本番環境（EC2）

```
/var/glow/
├── glow-brain/                      # git clone したリポジトリ
│   └── slack-claude-bot/            # アプリケーション本体
├── glow-worktrees/                  # worktree配置ディレクトリ
│   └── session_xxx/                 # セッションごとのworktree
├── data/
│   └── sessions.db                  # SQLiteデータベース
├── credentials/
│   └── google-service-account.json  # Google認証情報
└── logs/
    └── slack-claude-bot.log         # アプリケーションログ
```

## 主要コンポーネント

### 1. Slack Bot (`slack/bot.py`)
- Slack Bolt for Python + Socket Mode
- `app_mention`イベントでトリガー
- ファイル添付の処理
- スレッドへの返信

### 2. Session Manager (`session/manager.py`)
- SlackスレッドID ↔ ClaudeセッションIDのマッピング
- 同時実行数制限（Semaphore）
- 有効期限管理（デフォルト24時間）
- 期限切れセッションの自動クリーンアップ

### 3. Worktree Manager (`worktree/manager.py`)
- `git worktree add/remove`の管理
- glow-brainリポジトリをベースに環境分離
- 並行数管理

### 4. Claude Executor (`claude/executor.py`) - tmux + pexpect方式

**セッション起動**:
```python
import pexpect

# tmuxセッション内でclaude起動
tmux_session = f"claude_{session_id}"
os.system(f"tmux new-session -d -s {tmux_session} -c {worktree_path}")

# pexpectでclaudeプロセスを制御
child = pexpect.spawn(f"tmux send-keys -t {tmux_session} 'claude' Enter")
```

**プロンプト送信と出力取得**:
```python
# pexpectで直接claudeを制御する方式
child = pexpect.spawn('claude', cwd=worktree_path, timeout=300)
child.expect('>')  # プロンプト待ち

child.sendline(prompt)
child.expect('>')  # 回答後のプロンプト再表示を待つ

output = child.before.decode()  # 回答を取得
```

**特徴**:
- セッションが常駐し、真の対話的体験
- コンテキストがメモリ上に維持される
- ANSIエスケープシーケンスの除去が必要
- タイムアウト管理

### 5. GitHub PR Manager (`github/pr_manager.py`)

**目的**: Claudeの作業結果をGitHubにpushしてPRを作成し、対応内容を保存

**機能**:
- worktree内の変更を検出
- commit作成（Slackスレッド情報をメッセージに含む）
- remote branchへpush
- PR作成（gh CLI使用）

**PR情報に含める内容**:
- Slackチャンネル名/スレッドリンク
- リクエストしたユーザー
- 対応内容のサマリー
- 生成ファイル一覧

**実装例**:
```python
class GitHubPRManager:
    async def create_pr_for_session(
        self,
        session: Session,
        slack_context: SlackContext,
        summary: str
    ) -> str:
        """
        セッションの変更をPRとして作成

        Returns:
            PR URL
        """
        worktree_path = session.worktree_path
        branch_name = f"slack-bot/{session.id}"

        # 変更があるか確認
        if not self._has_changes(worktree_path):
            return None

        # commit作成
        commit_message = self._build_commit_message(slack_context, summary)
        await self._commit_changes(worktree_path, commit_message)

        # push
        await self._push_branch(worktree_path, branch_name)

        # PR作成
        pr_body = self._build_pr_body(slack_context, summary)
        pr_url = await self._create_pr(worktree_path, branch_name, pr_body)

        return pr_url

    def _build_commit_message(self, ctx: SlackContext, summary: str) -> str:
        return f"""[Slack Bot] {summary}

Slack: #{ctx.channel_name}
Thread: {ctx.thread_link}
User: @{ctx.user_name}
"""

    def _build_pr_body(self, ctx: SlackContext, summary: str) -> str:
        return f"""## Slack Bot による自動PR

### 対応元
- **チャンネル**: #{ctx.channel_name}
- **スレッド**: {ctx.thread_link}
- **リクエストユーザー**: @{ctx.user_name}

### 対応内容
{summary}

---
🤖 Generated by Slack-Claude Bot
"""
```

### 6. File Uploader (`upload/google_drive.py`)
- Google Drive APIでファイルアップロード
- 共有リンク生成
- 既存実装パターン参考: `scripts/download_masterdata_design_docs/`

## DBスキーマ

```sql
CREATE TABLE sessions (
    id TEXT PRIMARY KEY,
    slack_thread_id TEXT UNIQUE NOT NULL,
    slack_channel_id TEXT NOT NULL,
    slack_channel_name TEXT,                 -- チャンネル名（PR用）
    slack_user_id TEXT NOT NULL,
    slack_user_name TEXT,                    -- ユーザー名（PR用）
    slack_thread_link TEXT,                  -- スレッドへのリンク
    claude_session_id TEXT,
    worktree_path TEXT NOT NULL,
    github_branch TEXT,                      -- 作成したブランチ名
    github_pr_url TEXT,                      -- PR URL
    github_pr_number INTEGER,                -- PR番号
    status TEXT DEFAULT 'active',
    created_at TIMESTAMP NOT NULL,
    last_activity TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NOT NULL
);

CREATE TABLE file_uploads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id TEXT NOT NULL,
    file_name TEXT NOT NULL,
    google_drive_link TEXT,
    upload_status TEXT DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL,
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);
```

## 環境変数

```bash
# Slack
SLACK_BOT_TOKEN=xoxb-xxx
SLACK_APP_TOKEN=xapp-xxx
SLACK_WORKSPACE_URL=https://your-workspace.slack.com  # スレッドリンク生成用

# Anthropic
ANTHROPIC_API_KEY=sk-ant-xxx

# GitHub
GITHUB_TOKEN=ghp_xxx                         # gh CLI用（または gh auth login）
GITHUB_REPO_OWNER=your-org
GITHUB_REPO_NAME=glow-brain
GITHUB_BASE_BRANCH=main                      # PRのベースブランチ

# Google Drive
GOOGLE_CREDENTIALS_PATH=/var/glow/slack-claude-bot/credentials/google-service-account.json
GOOGLE_DRIVE_UPLOAD_FOLDER_ID=xxx

# 設定
MAX_CONCURRENT_SESSIONS=10
SESSION_TTL_HOURS=24
CLAUDE_TIMEOUT_SECONDS=300
WORKTREE_BASE_PATH=/var/glow/glow-worktrees
SOURCE_REPO_PATH=/var/glow/glow-brain
```

## 実装ステップ

### Step 1: プロジェクト初期化
- [ ] ディレクトリ構成作成
- [ ] pyproject.toml作成（依存関係定義）
- [ ] 設定管理（config.py）
- [ ] ロギング設定

### Step 2: コア機能実装
- [ ] SQLiteスキーマ実装（db.py）
- [ ] Worktree Manager実装
- [ ] Session Manager実装
- [ ] Claude Executor実装

### Step 3: Slack連携実装
- [ ] Slack Bot実装（Socket Mode）
- [ ] イベントハンドラ実装
- [ ] ファイル添付処理
- [ ] メッセージ送信

### Step 4: GitHub連携実装
- [ ] GitHub PR Manager実装
- [ ] commit/push処理
- [ ] gh CLIでPR作成
- [ ] Slackコンテキスト情報の埋め込み

### Step 5: Google Drive連携実装
- [ ] File Uploader実装
- [ ] 共有リンク生成

### Step 6: 統合・運用
- [ ] 全コンポーネント統合
- [ ] systemdサービス設定
- [ ] ヘルスチェックスクリプト

## 検証方法

### ローカルテスト
```bash
# 各コンポーネントの単体テスト
pytest src/tests/

# Claude CLIの動作確認
claude -p "Hello" --output-format json
```

### 統合テスト
1. Slackでボットにメンション → 返答確認
2. 同じスレッドで追加質問 → 同一セッション継続確認
3. ファイル添付 → Claudeへの受け渡し確認
4. **コード変更時 → GitHub PR作成確認（Slack情報含む）**
5. ファイル生成 → Google Driveアップロード確認

### 運用確認
```bash
# systemdサービス状態
sudo systemctl status slack-claude-bot

# ログ確認
tail -f /var/glow/logs/slack-claude-bot.log

# ヘルスチェック
./scripts/health_check.sh
```

## 必要なSlack App設定

### OAuth Scopes
- `app_mentions:read`
- `chat:write`
- `files:read`
- `reactions:write`

### Event Subscriptions
- `app_mention`

### Socket Mode
- 有効化必須

## 参考ファイル

- Google Drive連携パターン: `scripts/download_masterdata_design_docs/download_masterdata_design_docs.py`
- プロジェクト構造: `CLAUDE.md`

## 備考

### tmux + pexpect方式のポイント

**セッション管理**:
- 各Slackスレッドに対応するtmuxセッションを作成
- セッション名: `claude_{session_id}`
- worktree内でclaudeを起動

**出力取得の課題と対策**:
- ANSIエスケープシーケンス除去: `strip-ansi`的な処理
- プロンプト検出: `>` や入力待ち状態の検知
- タイムアウト: pexpectの`timeout`パラメータで制御

**pexpect基本パターン**:
```python
child = pexpect.spawn('claude', cwd=worktree_path, timeout=300)
child.expect(r'>')  # プロンプト待ち
child.sendline(prompt)
child.expect(r'>', timeout=300)  # 回答完了待ち
output = child.before.decode()
```

**セッション終了**:
- 有効期限切れ時にtmuxセッションを終了
- `tmux kill-session -t claude_{session_id}`
