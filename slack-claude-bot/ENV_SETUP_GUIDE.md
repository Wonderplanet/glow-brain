# .env 設定ガイド

## 必要な環境変数一覧

```bash
# 必須項目
SLACK_BOT_TOKEN=xoxb-...
SLACK_APP_TOKEN=xapp-...
SLACK_WORKSPACE_URL=https://your-workspace.slack.com
GITHUB_TOKEN=ghp_...
GITHUB_REPO_OWNER=your-org
GITHUB_REPO_NAME=glow-brain
GITHUB_BASE_BRANCH=main

# オプション（ローカル検証では不要、EC2環境用）
# ANTHROPIC_API_KEY=sk-ant-...

# オプション（通常は変更不要）
MAX_CONCURRENT_SESSIONS=3
SESSION_TTL_HOURS=8
CLAUDE_TIMEOUT_SECONDS=300
WORKTREE_BASE_PATH=~/glow-worktrees
SOURCE_REPO_PATH=~/Documents/workspace/glow/glow-brain
DB_PATH=./data/sessions.db
LOG_LEVEL=INFO
```

---

## 1. Slack設定（3項目）

### 1-1. SLACK_BOT_TOKEN（必須）

**形式**: `xoxb-...`

**取得手順**:

1. https://api.slack.com/apps にアクセス
2. 既存のSlack Appを選択
3. 左メニューから **OAuth & Permissions** を選択
4. **Bot User OAuth Token** をコピー（`xoxb-`で始まる）

**必要なスコープ**:
- `app_mentions:read` - メンション受信
- `chat:write` - メッセージ送信
- `files:read` - ファイル読み取り（将来の拡張用）
- `reactions:write` - リアクション追加
- `channels:history` - チャンネル履歴読み取り

**スコープの追加方法**:
1. **OAuth & Permissions** ページ
2. **Scopes** セクション
3. **Bot Token Scopes** で上記スコープを追加
4. 変更後、ワークスペースに再インストール

---

### 1-2. SLACK_APP_TOKEN（必須）

**形式**: `xapp-...`

**取得手順**:

1. https://api.slack.com/apps にアクセス
2. 既存のSlack Appを選択
3. 左メニューから **Basic Information** を選択
4. **App-Level Tokens** セクション
5. **Generate Token and Scopes** をクリック
6. Token名を入力（例: `socket-token`）
7. スコープを追加: `connections:write`
8. **Generate** をクリック
9. 表示されるトークンをコピー（`xapp-`で始まる）

**注意**: このトークンは一度しか表示されません。必ず保存してください。

---

### 1-3. SLACK_WORKSPACE_URL（必須）

**形式**: `https://your-workspace.slack.com`

**取得方法**:

1. SlackデスクトップアプリまたはWebブラウザでSlackを開く
2. URLバーのアドレスを確認
3. `https://your-workspace.slack.com/` の形式で記載

**例**:
- `https://wonderplanet.slack.com`
- `https://my-team.slack.com`

---

## 2. Anthropic API Key（1項目）

### 2-1. ANTHROPIC_API_KEY（オプション）

**⚠️ ローカル検証では不要です**

ローカル検証では、**MaxプランでログインしたClaude Code**を使用するため、API Keyは不要です。

`.env`ファイルに`ANTHROPIC_API_KEY`を設定する必要はありません。

**必要な場合**:
- EC2環境で動作させる場合
- API経由でClaude Codeを実行する場合

**形式**: `sk-ant-...`

**取得手順**（EC2環境用）:

1. https://console.anthropic.com/ にアクセス
2. ログイン（アカウントがない場合は作成）
3. 左メニューから **API Keys** を選択
4. **Create Key** をクリック
5. キー名を入力（例: `slack-claude-bot-ec2`）
6. **Create Key** をクリック
7. 表示されるAPIキーをコピー（`sk-ant-`で始まる）

**注意**:
- このキーは一度しか表示されません。必ず保存してください。
- 課金が発生するため、使用量に注意してください。

**料金確認**:
- Console > Settings > Usage & Billing

---

## 3. GitHub設定（4項目）

### 3-1. GITHUB_TOKEN（必須）

**形式**: `ghp_...`

**取得手順**:

#### 方法A: GitHub CLI経由（推奨）

```bash
# GitHub CLIで認証
gh auth login

# トークンを確認
gh auth status

# トークンを取得
gh auth token
```

#### 方法B: Personal Access Token作成

1. https://github.com/settings/tokens にアクセス
2. **Generate new token** > **Generate new token (classic)** を選択
3. Note（説明）を入力: `slack-claude-bot-local`
4. Expiration（有効期限）を設定: 90 days（推奨）
5. **Select scopes** で以下にチェック:
   - `repo` (Full control of private repositories)
   - `workflow` (Update GitHub Action workflows)
6. **Generate token** をクリック
7. 表示されるトークンをコピー（`ghp_`で始まる）

**注意**: このトークンは一度しか表示されません。必ず保存してください。

---

### 3-2. GITHUB_REPO_OWNER（必須）

**形式**: GitHubのユーザー名または組織名

**例**:
```bash
GITHUB_REPO_OWNER=Wonderplanet
```

**確認方法**:
- リポジトリURL: `https://github.com/Wonderplanet/glow-brain`
- この場合、`GITHUB_REPO_OWNER=Wonderplanet`

---

### 3-3. GITHUB_REPO_NAME（必須）

**形式**: リポジトリ名

**例**:
```bash
GITHUB_REPO_NAME=glow-brain
```

**確認方法**:
- リポジトリURL: `https://github.com/Wonderplanet/glow-brain`
- この場合、`GITHUB_REPO_NAME=glow-brain`

---

### 3-4. GITHUB_BASE_BRANCH（必須）

**形式**: ブランチ名

**例**:
```bash
GITHUB_BASE_BRANCH=main
```

**説明**: PRを作成する際のベースブランチ（マージ先）

---

## 4. オプション設定

以下は通常変更不要ですが、カスタマイズ可能です。

### 4-1. MAX_CONCURRENT_SESSIONS

**デフォルト**: `3`

**説明**: 同時に起動できるClaudeセッション数

**例**:
```bash
MAX_CONCURRENT_SESSIONS=5  # 5セッションまで許可
```

---

### 4-2. SESSION_TTL_HOURS

**デフォルト**: `8`

**説明**: セッションの有効期限（時間）

**例**:
```bash
SESSION_TTL_HOURS=24  # 24時間有効
```

---

### 4-3. CLAUDE_TIMEOUT_SECONDS

**デフォルト**: `300`（5分）

**説明**: Claude実行のタイムアウト時間

**例**:
```bash
CLAUDE_TIMEOUT_SECONDS=600  # 10分に延長
```

---

### 4-4. WORKTREE_BASE_PATH

**デフォルト**: `~/glow-worktrees`

**説明**: worktreeを作成するディレクトリ

**変更不要**: 特別な理由がない限りデフォルトのままで問題ありません。

---

### 4-5. SOURCE_REPO_PATH

**デフォルト**: `~/Documents/workspace/glow/glow-brain`

**説明**: glow-brainリポジトリのパス

**確認方法**:
```bash
pwd
# 現在のディレクトリを確認
```

---

### 4-6. DB_PATH

**デフォルト**: `./data/sessions.db`

**説明**: SQLiteデータベースファイルのパス

**変更不要**: プロジェクト内の`data/`ディレクトリに保存されます。

---

### 4-7. LOG_LEVEL

**デフォルト**: `INFO`

**説明**: ログ出力レベル

**選択肢**:
- `DEBUG` - 詳細なデバッグ情報
- `INFO` - 通常の情報（推奨）
- `WARNING` - 警告のみ
- `ERROR` - エラーのみ

---

## 完成例

```bash
# Slack設定
SLACK_BOT_TOKEN=xoxb-...
SLACK_APP_TOKEN=xapp-...
SLACK_WORKSPACE_URL=https://wonderplanet.slack.com

# Anthropic API（ローカル検証では不要、コメントアウトのまま）
# ANTHROPIC_API_KEY=sk-ant-api03-abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz0123456789

# GitHub設定
GITHUB_TOKEN=ghp_...
GITHUB_REPO_OWNER=Wonderplanet
GITHUB_REPO_NAME=glow-brain
GITHUB_BASE_BRANCH=main

# アプリケーション設定（オプション）
MAX_CONCURRENT_SESSIONS=3
SESSION_TTL_HOURS=8
CLAUDE_TIMEOUT_SECONDS=300
WORKTREE_BASE_PATH=~/glow-worktrees
SOURCE_REPO_PATH=/Users/junki.mizutani/Documents/workspace/glow/glow-brain
DB_PATH=./data/sessions.db
LOG_LEVEL=INFO
```

---

## セットアップ手順

1. **ファイル作成**
   ```bash
   cd slack-claude-bot
   cp .env.example .env
   ```

2. **エディタで開く**
   ```bash
   vim .env
   # または
   code .env
   ```

3. **各項目を上記手順で取得した値で置き換える**

4. **保存して閉じる**

5. **権限設定**（セキュリティのため）
   ```bash
   chmod 600 .env
   ```

6. **動作確認**
   ```bash
   uv run python -m src.main
   ```

---

## トラブルシューティング

### エラー: "Missing required configuration"

以下のいずれかが設定されていません:
- `SLACK_BOT_TOKEN`
- `SLACK_APP_TOKEN`

すべて設定されているか確認してください。

**注意**: `ANTHROPIC_API_KEY`はローカル検証では不要です。

### Slackが反応しない

1. **Socket Modeが有効か確認**
   - Slack App設定 > Socket Mode > Enable

2. **Event Subscriptionsが設定されているか確認**
   - Event Subscriptions > Enable Events
   - Subscribe to bot events: `app_mention`

3. **トークンが正しいか確認**
   - `SLACK_BOT_TOKEN`は`xoxb-`で始まる
   - `SLACK_APP_TOKEN`は`xapp-`で始まる

### GitHub PRが作成できない

1. **gh CLIがインストールされているか確認**
   ```bash
   gh --version
   ```

2. **認証されているか確認**
   ```bash
   gh auth status
   ```

3. **リポジトリ情報が正しいか確認**
   - `GITHUB_REPO_OWNER`
   - `GITHUB_REPO_NAME`

---

## セキュリティ注意事項

⚠️ **重要**:

1. **.envファイルをGitにコミットしない**
   - `.gitignore`に`.env`が含まれていることを確認

2. **トークンを共有しない**
   - Slack、GitHub、各トークンは個人用です

3. **定期的にトークンをローテーション**
   - セキュリティのため、定期的に再生成を推奨

4. **不要になったトークンは削除**
   - 使わなくなったトークンは無効化

---

これで.envの設定は完了です！
