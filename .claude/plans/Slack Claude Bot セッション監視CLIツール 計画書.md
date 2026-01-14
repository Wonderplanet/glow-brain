# Slack Claude Bot セッション監視CLIツール 計画書

## 概要

Slack Claude Botの各セッションのステータスや詳細情報を一覧表示するエンジニア向けCLIツールを作成する。

## 背景

現在、以下の情報を確認する手段がない：
- どのSlackスレッドからメンションされているか
- どのセッション（worktree）で作業中か
- 作業状態（回答中、休眠状態など）
- 各セッションの詳細情報

## 現状の実装

### セッション管理（SQLite）

**DBファイル**: `data/sessions.db`

**スキーマ**:
| カラム | 型 | 説明 |
|--------|-----|------|
| id | TEXT | セッションID |
| slack_thread_id | TEXT | `{channel_id}:{thread_ts}` |
| slack_channel_id | TEXT | チャンネルID |
| slack_channel_name | TEXT | チャンネル名 |
| slack_user_id | TEXT | ユーザーID |
| slack_user_name | TEXT | ユーザー名 |
| slack_thread_link | TEXT | スレッドURL |
| claude_session_started | INTEGER | 初回メッセージ送信済みフラグ |
| worktree_path | TEXT | worktreeパス |
| github_branch | TEXT | ブランチ名 |
| github_pr_url | TEXT | PR URL |
| github_pr_number | INTEGER | PR番号 |
| status | TEXT | `active` / `expired` |
| created_at | TIMESTAMP | 作成日時 |
| last_activity | TIMESTAMP | 最終活動日時 |
| expires_at | TIMESTAMP | 有効期限 |

---

## 実装計画

### 1. CLIツール作成

**ファイル**: `src/cli/status.py`

**コマンド**:
```bash
# セッション一覧表示
uv run python -m src.cli.status list

# セッション詳細表示
uv run python -m src.cli.status show <session_id>

# JSON形式で出力
uv run python -m src.cli.status list --json

# watchモード（定期更新）
uv run python -m src.cli.status list --watch
```

### 2. 表示する情報

#### 一覧表示（list）

| 項目 | 説明 |
|------|------|
| ID | セッションID（短縮） |
| Status | DB状態 + リアルタイム状態 |
| Channel | チャンネル名 |
| User | ユーザー名 |
| Last Activity | 最終活動からの経過時間 |
| Expires | 有効期限までの残り時間 |
| PR | PR番号（あれば） |

#### リアルタイム状態の判定

- **🔄 Running**: worktreeディレクトリでClaudeプロセスが実行中
- **💤 Idle**: アクティブだがClaudeプロセスは実行していない
- **⏰ Expired**: 有効期限切れ

**判定方法**: `ps aux | grep claude` でworktree_pathを含むプロセスを検索

### 3. ディレクトリ構成

```
src/
├── cli/
│   ├── __init__.py
│   └── status.py      # 新規作成
└── ...
```

### 4. 依存関係

**追加するパッケージ**:
- `rich` - テーブル表示、カラー出力用

---

## 実装ステップ

1. **`src/cli/__init__.py` 作成** - 空のパッケージ初期化
2. **`src/cli/status.py` 作成** - メインCLIツール
   - argparseでコマンドライン引数パース
   - 既存の`SessionDatabase`クラスを再利用
   - プロセス確認ロジック実装
   - rich.tableで一覧表示
3. **pyproject.toml 更新** - richを依存関係に追加
4. **動作確認** - 実際のDBで一覧表示テスト

---

## 検証方法

1. `uv sync` で依存関係インストール
2. `uv run python -m src.cli.status list` でセッション一覧表示
3. 実行中のClaudeプロセスがある場合、正しく「Running」と表示されることを確認
4. `--json` フラグでJSON出力を確認

---

## 修正対象ファイル

| ファイル | 変更内容 |
|----------|---------|
| `src/cli/__init__.py` | 新規作成（空） |
| `src/cli/status.py` | 新規作成（メインCLI） |
| `pyproject.toml` | rich依存関係追加 |
