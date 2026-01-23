# ClickUp Tools

ClickUp データエクスポートツール（拡張可能な設計）

## 概要

ClickUp の各種リソース（リスト、タスク、スペースなど）をローカルにエクスポートするための Python CLI ツール群です。

**設計方針**: 共通ロジックを再利用可能なモジュールとして切り出し、様々なユースケースに対応できる拡張可能な構造になっています。

## ディレクトリ構造

```
domain/tools/clickup/
├── README.md                        # このファイル
├── pyproject.toml                   # 依存関係
├── .gitignore
├── .env.example                     # 環境変数テンプレート
│
├── src/
│   └── clickup_tools/
│       ├── common/                  # 共通モジュール（再利用可能）
│       │   ├── client.py            # ClickUp API クライアント
│       │   ├── models.py            # データモデル
│       │   ├── url_parser.py        # URL 解析
│       │   ├── file_utils.py        # ファイル操作
│       │   ├── markdown.py          # Markdown 生成
│       │   └── config.py            # 設定管理
│       │
│       └── exporters/               # ユースケース別エクスポーター
│           └── list_closed_exporter/    # closed チケットエクスポート
│               ├── main.py
│               └── exporter.py
│
└── scripts/                         # 便利スクリプト（将来用）
```

## セットアップ

### 1. 依存関係のインストール

```bash
cd domain/tools/clickup
uv sync
```

### 2. 環境変数の設定

`.env` ファイルを作成して、ClickUp API キーを設定します。

```bash
cp .env.example .env
# .env を編集して CLICKUP_API_KEY を設定
```

#### API キーの取得方法

1. ClickUp にログイン
2. 設定 → Apps → API Token
3. "Generate" をクリックして API キーを生成

## 利用可能なツール

### 1. List Closed Exporter

リスト内の closed チケットを1チケット1フォルダでエクスポートします。

#### 基本的な使い方

```bash
cd domain/tools/clickup

uv run python -m clickup_tools.exporters.list_closed_exporter \
  --url "https://app.clickup.com/12345678/v/li/987654321"
```

#### オプション

```bash
# 出力先を指定
--output PATH        # デフォルト: domain/raw-data/clickup/{space}/{folder}/{list}/

# 添付ファイルをスキップ
--skip-attachments

# デバッグモード（最初の N 件のみ）
--debug-limit N

# ドライラン（ファイル出力なし）
--dry-run
```

#### 実行例

```bash
# 基本実行
uv run python -m clickup_tools.exporters.list_closed_exporter \
  --url "https://app.clickup.com/12345678/v/li/987654321"

# 添付ファイルをスキップして高速化
uv run python -m clickup_tools.exporters.list_closed_exporter \
  --url "https://app.clickup.com/12345678/v/li/987654321" \
  --skip-attachments

# 最初の3件のみエクスポート（動作確認用）
uv run python -m clickup_tools.exporters.list_closed_exporter \
  --url "https://app.clickup.com/12345678/v/li/987654321" \
  --debug-limit 3

# ドライラン（どんな出力になるか確認）
uv run python -m clickup_tools.exporters.list_closed_exporter \
  --url "https://app.clickup.com/12345678/v/li/987654321" \
  --dry-run
```

#### 出力データ構造

```
domain/raw-data/clickup/
└── {space_name}/
    └── {folder_name}/           # フォルダがない場合はスキップ
        └── {list_name}/
            └── {task_id}_{task_name}/
                ├── ticket.md    # チケット情報（Markdown形式）
                └── attachments/ # 添付ファイル
                    ├── file1.png
                    └── file2.pdf
```

#### ticket.md の内容

各チケットの `ticket.md` には以下の情報が含まれます：

- **基本情報**: タスク ID、ステータス、優先度、作成者、担当者、タグ、日時、URL
- **リスト情報**: リスト名、スペース名、フォルダ名
- **説明**: タスクの説明文
- **カスタムフィールド**: 設定されているカスタムフィールドとその値
- **コメント**: タスクに投稿されたコメント（ユーザー名、日時付き）
- **添付ファイル**: 添付ファイルのリスト（ファイル名、サイズ、形式、日時）

## 共通モジュール

### ClickUpClient

ClickUp API との通信を担当する再利用可能なクライアントです。

**機能**:
- レート制限対策（10 req/s, 100 req/min）
- 自動リトライ（429, 5xx エラー対応）
- 各種リソースの取得（リスト、タスク、コメント、添付ファイル）

### Models

ClickUp のデータ構造を表現するデータモデルです。

- `Task`: タスク情報
- `Attachment`: 添付ファイル
- `Comment`: コメント
- `ListInfo`: リスト情報
- `CustomField`: カスタムフィールド
- `UrlInfo`: URL 情報

### ClickUpUrlParser

ClickUp の URL を解析して ID を抽出します。

**対応 URL 形式**:
- リスト: `https://app.clickup.com/{team}/v/li/{list_id}`
- タスク: `https://app.clickup.com/t/{task_id}`
- スペース: `https://app.clickup.com/{team}/v/s/{space_id}`

### MarkdownBuilder

Markdown 形式のテキストを生成するヘルパーです。

**機能**:
- 見出し、テーブル、リンクの生成
- タスク情報の整形
- コメント、添付ファイルのフォーマット

## 将来の拡張計画

共通モジュールを活用して、以下のようなエクスポーターを追加予定です：

```
exporters/
├── list_closed_exporter/      # ✅ 実装済み
├── list_all_exporter/         # 全ステータス対象
├── task_exporter/             # 単一タスク
├── space_exporter/            # スペース全体
├── tag_filter_exporter/       # タグでフィルタ
└── date_range_exporter/       # 期間指定
```

## トラブルシューティング

### API キーエラー

```
ValueError: CLICKUP_API_KEY が設定されていません。
```

→ `.env` ファイルを作成して `CLICKUP_API_KEY` を設定してください。

### レート制限エラー

```
リトライ 1/3 (status=429, wait=1s)
```

→ 自動的にリトライされます。頻繁に発生する場合は、処理を分割するか、`--debug-limit` で件数を制限してください。

### URL 解析エラー

```
ValueError: リスト URL ではありません
```

→ URL 形式が正しいか確認してください。対応形式: `https://app.clickup.com/{team}/v/li/{list_id}`

## ライセンス

このツールは GLOW プロジェクト内部での使用を想定しています。
