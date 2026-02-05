# Slack Tools

SlackのスレッドURLを指定して、全メッセージと添付ファイルを収集・保存するツール群です。

## 機能

- **Thread Exporter**: Slackスレッドの完全エクスポート
  - 全メッセージの取得
  - 添付ファイルのダウンロード
  - Markdown形式での保存
  - 生データ（JSON）の保存

## セットアップ

### 1. 依存関係のインストール

```bash
cd domain/tools/slack
uv sync
```

### 2. 環境変数の設定

`.env.example`をコピーして`.env`を作成し、Slack APIトークンを設定します。

```bash
cp .env.example .env
# .envファイルを編集してSLACK_TOKENを設定
```

**必要なスコープ:**

- `channels:history` - 公開チャンネルの履歴
- `groups:history` - プライベートチャンネルの履歴
- `channels:read` - 公開チャンネル情報
- `groups:read` - プライベートチャンネル情報
- `users:read` - ユーザー情報
- `files:read` - ファイルダウンロード

**トークンタイプ:** User Token (`xoxp-`) を推奨（公開/プライベートチャンネル両対応）

### 3. Slack App作成手順

1. https://api.slack.com/apps にアクセス
2. "Create New App" → "From scratch"
3. App Nameとワークスペースを選択
4. "OAuth & Permissions" → "User Token Scopes" で上記スコープを追加
5. "Install to Workspace" でインストール
6. "User OAuth Token" (`xoxp-`で始まる) をコピーして`.env`に設定

## 使用方法

### Thread Exporter

SlackスレッドURLを指定してエクスポートします。

```bash
cd domain/tools/slack

# 基本実行
uv run python -m slack_tools.exporters.thread_exporter \
  --url "https://glow-team.slack.com/archives/C123456/p1704067200123456?thread_ts=1704067200.123456"

# 添付ファイルをスキップ
uv run python -m slack_tools.exporters.thread_exporter \
  --url "..." --skip-attachments

# ドライラン（ファイル保存なし）
uv run python -m slack_tools.exporters.thread_exporter \
  --url "..." --dry-run
```

### 出力フォルダ構成

```
domain/raw-data/slack/
└── {workspace}/
    └── {channel_name}/
        └── {thread_ts}/
            ├── raw.json           # APIレスポンス生データ
            ├── thread.md          # 可読形式Markdown
            ├── meta.json          # メタ情報
            └── attachments/       # 添付ファイル
                └── {file_id}_{filename}
```

## プロジェクト構造

```
domain/tools/slack/
├── README.md              # このファイル
├── pyproject.toml         # プロジェクト設定
├── .env.example           # 環境変数テンプレート
├── .gitignore
└── src/
    └── slack_tools/
        ├── common/                    # 共通モジュール
        │   ├── client.py             # Slack APIクライアント
        │   ├── config.py             # 設定管理
        │   ├── models.py             # データモデル
        │   ├── url_parser.py         # URL解析
        │   ├── file_utils.py         # ファイル操作
        │   └── markdown.py           # Markdown生成
        └── exporters/
            └── thread_exporter/      # スレッドエクスポーター
                ├── __main__.py       # エントリーポイント
                ├── main.py           # CLI処理
                └── exporter.py       # エクスポート処理
```

## 拡張性

このプロジェクトは拡張可能な設計になっています。

### 新しいエクスポーターの追加

`src/slack_tools/exporters/`配下に新しいエクスポーターを追加できます。

例: チャンネル全体のエクスポーター

```
src/slack_tools/exporters/
└── channel_exporter/
    ├── __init__.py
    ├── __main__.py
    ├── main.py
    └── exporter.py
```

### 共通モジュールの利用

`slack_tools.common`パッケージの各モジュールは、すべてのエクスポーターから再利用できます。

## トラブルシューティング

### "SLACK_TOKEN が設定されていません"

`.env`ファイルを作成し、`SLACK_TOKEN`を設定してください。

### "Slack APIエラー: not_in_channel"

スレッドが属するチャンネルにBotまたはユーザーが参加していません。チャンネルに参加してから再試行してください。

### "Slack APIエラー: missing_scope"

必要なスコープが不足しています。Slack Appの設定を確認し、必要なスコープを追加してください。

## 参考

- [Slack API Documentation](https://api.slack.com/docs)
- [conversations.replies API](https://api.slack.com/methods/conversations.replies)
- [conversations.info API](https://api.slack.com/methods/conversations.info)
- [users.info API](https://api.slack.com/methods/users.info)
