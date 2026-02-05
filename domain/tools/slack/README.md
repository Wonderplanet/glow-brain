# Slack Tools

SlackのスレッドURLを指定して、全メッセージと添付ファイルを収集・保存するツール群です。

## 機能

- **Thread Exporter**: Slackスレッドの完全エクスポート
  - 全メッセージの取得
  - 添付ファイルのダウンロード
  - Markdown形式での保存
  - 生データ（JSON）の保存

- **Thread Finder**: 条件指定によるスレッド検索とバッチエクスポート
  - 日付・チャンネル・ユーザーを指定してスレッド検索
  - 検索結果のJSON出力
  - 見つかったスレッドの一括エクスポート

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

### Thread Finder

指定した条件でスレッドを検索し、必要に応じてバッチエクスポートします。

```bash
cd domain/tools/slack

# 基本的な検索（JSON出力のみ）
uv run python -m slack_tools.finders.thread_finder \
  --date 2024-01-15 \
  --channels C12345678 \
  --users U11111111

# 日付範囲を指定
uv run python -m slack_tools.finders.thread_finder \
  --date 2024-01-15 \
  --end-date 2024-01-17 \
  --channels C12345678 general \
  --users U11111111 @john

# 検索結果をバッチエクスポート
uv run python -m slack_tools.finders.thread_finder \
  --date 2024-01-15 \
  --channels C12345678 \
  --users U11111111 \
  --export \
  --delay 2.0

# ドライラン
uv run python -m slack_tools.finders.thread_finder \
  --date 2024-01-15 \
  --channels C12345678 \
  --users U11111111 \
  --dry-run
```

**オプション:**

- `--date`: 検索開始日（YYYY-MM-DD形式、必須）
- `--end-date`: 検索終了日（省略時は--dateと同じ）
- `--channels`: チャンネルID or 名前（複数指定可、必須）
- `--users`: ユーザーID or @ユーザー名（複数指定可、必須）
- `--export`: 検索結果をバッチエクスポート
- `--delay`: エクスポート時のスレッド間ディレイ（秒、デフォルト: 1.0）
- `--dry-run`: ドライラン（ファイル保存なし）

**検索結果の出力先:**

```
domain/raw-data/slack/{workspace}/_searches/{YYYYMMDD_HHMMSS}/
├── search_result.json      # 検索結果
├── export_summary.json     # エクスポートサマリー（--export時）
└── threads/                # エクスポートされたスレッド（--export時）
    └── {channel}_{thread_ts}/
        ├── raw.json
        ├── thread.md
        ├── meta.json
        └── attachments/
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
        ├── exporters/
        │   └── thread_exporter/      # スレッドエクスポーター
        │       ├── __main__.py       # エントリーポイント
        │       ├── main.py           # CLI処理
        │       └── exporter.py       # エクスポート処理
        └── finders/
            └── thread_finder/        # スレッド検索
                ├── __main__.py       # エントリーポイント
                ├── main.py           # CLI処理
                ├── finder.py         # 検索ロジック
                └── batch_exporter.py # バッチエクスポート
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

### 新しい検索機能の追加

`src/slack_tools/finders/`配下に新しい検索機能を追加できます。

例: メッセージ検索

```
src/slack_tools/finders/
└── message_finder/
    ├── __init__.py
    ├── __main__.py
    ├── main.py
    └── finder.py
```

### 共通モジュールの利用

`slack_tools.common`パッケージの各モジュールは、すべてのエクスポーター・検索機能から再利用できます。

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
