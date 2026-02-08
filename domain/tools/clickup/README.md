# ClickUp Tools

ClickUp データエクスポートツール（拡張可能な設計）

## 概要

ClickUp の各種リソース（リスト、タスク、スペースなど）をローカルにエクスポートするための Python CLI ツール群です。

**設計方針**: 共通ロジックを再利用可能なモジュールとして切り出し、様々なユースケースに対応できる拡張可能な構造になっています。

## ディレクトリ構造

```
domain/tools/clickup/
├── README.md                          # このファイル
├── clickup-api-rate-limits.md        # API レート制限ガイド
├── pyproject.toml                     # 依存関係
├── .gitignore
├── .env.example                       # 環境変数テンプレート
│
└── src/
    └── clickup_tools/
        ├── common/                    # 共通モジュール（再利用可能）
        │   ├── client.py              # ClickUp API クライアント
        │   ├── models.py              # データモデル
        │   ├── url_parser.py          # URL 解析
        │   ├── file_utils.py          # ファイル操作
        │   ├── markdown.py            # Markdown 生成
        │   └── config.py              # 設定管理
        │
        └── exporters/                 # ユースケース別エクスポーター
            ├── list_exporter/         # リストエクスポート
            │   ├── __init__.py
            │   ├── __main__.py
            │   ├── main.py
            │   └── exporter.py
            └── space_structure_exporter/  # スペース構造エクスポート
                ├── __init__.py
                ├── __main__.py
                ├── main.py
                └── exporter.py
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

### Space Structure Exporter

スペース配下の全フォルダとリストを一覧化し、CSV形式で出力します。

#### 基本的な使い方

```bash
cd domain/tools/clickup

# スペースURLで指定
uv run python -m clickup_tools.exporters.space_structure_exporter \
  --url "https://app.clickup.com/12345678/v/s/987654321"

# スペースIDで直接指定
uv run python -m clickup_tools.exporters.space_structure_exporter \
  --space-id "987654321"
```

#### オプション

```bash
# スペースURL
--url, -u URL            # ClickUp スペース URL

# スペースID（URLの代わり）
--space-id, -s ID        # スペースIDを直接指定
```

#### 出力データ構造

```
domain/raw-data/clickup/
└── {space_name}/
    └── _space_structure.csv    # スペース構造一覧
```

#### CSV出力仕様

| 列名 | 説明 | データ型 | 例 |
|------|------|----------|-----|
| スペース名 | スペースの名前 | 文字列 | `GLOW` |
| スペースID | スペースの一意ID | 文字列 | `987654321` |
| フォルダ名 | フォルダ名（フォルダレスの場合は空） | 文字列 | `QA` |
| フォルダID | フォルダID（フォルダレスの場合は空） | 文字列 | `123456789` |
| フォルダアーカイブ | フォルダがアーカイブ済みか | 文字列 | `TRUE`/`FALSE` |
| フォルダ作成日時 | フォルダの作成日時 | 文字列 | （API未提供のため空） |
| リスト名 | リストの名前 | 文字列 | `アプリQA_Ver1.5.0` |
| リストID | リストの一意ID | 文字列 | `901234567` |
| リストアーカイブ | リストがアーカイブ済みか | 文字列 | `TRUE`/`FALSE` |
| リスト作成日時 | リストの作成日時 | 文字列 | （API未提供のため空） |

**特徴**:
- アーカイブ済み/未アーカイブの全データを取得
- フォルダレスリスト（スペース直下のリスト）も含む
- UTF-8 BOM付きCSV（Excel互換）

#### 実行例

実行例とサンプル出力:

```bash
# スペース構造をエクスポート
uv run python -m clickup_tools.exporters.space_structure_exporter \
  --space-id "90100408512"

# 出力例:
# ============================================================
# ClickUp Space Structure Exporter
# ============================================================
# スペース情報を取得中... (ID: 90100408512)
# スペース名: GLOW
#
# フォルダ一覧を取得中...
#   取得フォルダ数: 21
#
#   フォルダ: QA
#     リスト数: 43
#
#   フォルダ: GLOW(開発)
#     リスト数: 31
#
# フォルダレスリストを取得中...
#   フォルダレスリスト数: 12
#
# CSV出力中: domain/raw-data/clickup/GLOW/_space_structure.csv
#   ✓ 出力完了
#
# ============================================================
# エクスポート完了
# ============================================================
# スペース名:     GLOW
# スペースID:     90100408512
# フォルダ数:     21
# リスト数:       224
# 出力先:         domain/raw-data/clickup/GLOW/_space_structure.csv
```

**CSVサンプル出力**:
```csv
スペース名,スペースID,フォルダ名,フォルダID,フォルダアーカイブ,フォルダ作成日時,リスト名,リストID,リストアーカイブ,リスト作成日時
GLOW,90100408512,QA,123456789,FALSE,,アプリQA_Ver1.5.0,901234567,FALSE,
GLOW,90100408512,QA,123456789,FALSE,,アプリQA_Ver1.4.1,901234566,TRUE,
GLOW,90100408512,開発,123456790,FALSE,,機能開発リスト,901234568,FALSE,
GLOW,90100408512,,,,,バックログ,901234569,FALSE,
```

### List Exporter

リスト内のチケットをエクスポートします。全ステータスに対応しており、オプションで特定のステータスのみフィルタすることも可能です。

#### 基本的な使い方

```bash
cd domain/tools/clickup

# 全ステータスのチケットをエクスポート
uv run python -m clickup_tools.exporters.list_exporter \
  --url "https://app.clickup.com/12345678/v/li/987654321"

# closed のみエクスポート
uv run python -m clickup_tools.exporters.list_exporter \
  --url "https://app.clickup.com/12345678/v/li/987654321" \
  --status closed
```

#### オプション

```bash
# ステータスでフィルタ
--status STATUS       # 例: closed, open, in progress など

# 出力先を指定
--output PATH         # デフォルト: domain/raw-data/clickup/{space}/{folder}/{list}/

# 添付ファイルをスキップ
--skip-attachments

# デバッグモード（最初の N 件のみ）
--debug-limit N

# ドライラン（ファイル出力なし）
--dry-run

# サブタスクも含めてエクスポート
--include-subtasks

# 「休日」タスクも含める（デフォルトでは除外される）
--include-holiday-tasks
```

#### 実行例

```bash
# 基本実行（全ステータス）
uv run python -m clickup_tools.exporters.list_exporter \
  --url "https://app.clickup.com/12345678/v/li/987654321"

# closed のみエクスポート
uv run python -m clickup_tools.exporters.list_exporter \
  --url "https://app.clickup.com/12345678/v/li/987654321" \
  --status closed

# 添付ファイルをスキップして高速化
uv run python -m clickup_tools.exporters.list_exporter \
  --url "https://app.clickup.com/12345678/v/li/987654321" \
  --skip-attachments

# 最初の3件のみエクスポート（動作確認用）
uv run python -m clickup_tools.exporters.list_exporter \
  --url "https://app.clickup.com/12345678/v/li/987654321" \
  --debug-limit 3

# ドライラン（どんな出力になるか確認）
uv run python -m clickup_tools.exporters.list_exporter \
  --url "https://app.clickup.com/12345678/v/li/987654321" \
  --dry-run

# 出力先を指定
uv run python -m clickup_tools.exporters.list_exporter \
  --url "https://app.clickup.com/12345678/v/li/987654321" \
  --output ./my-exports

# サブタスクも含めてエクスポート（「休日」は自動的に除外される）
uv run python -m clickup_tools.exporters.list_exporter \
  --url "https://app.clickup.com/12345678/v/li/987654321" \
  --include-subtasks

# 「休日」タスクも含めてエクスポート
uv run python -m clickup_tools.exporters.list_exporter \
  --url "https://app.clickup.com/12345678/v/li/987654321" \
  --include-subtasks \
  --include-holiday-tasks
```

#### フィルタリング機能

##### 「休日」タスクの除外

プロジェクト管理リストには、開発期間外の「休日」タスクとその配下のサブタスク（各祝日）が含まれています。
**デフォルトで、これらは自動的に除外されます。**

「休日」タスクも含めてエクスポートしたい場合は、`--include-holiday-tasks` オプションを使用してください。

**除外ルール**（デフォルト動作）:
- 親タスク（トップレベル）で名前が完全一致で「休日」のタスク
- その配下のサブタスク全て（孫タスク以降も含む）

**除外されないケース**:
- サブタスクとして存在する「休日」（親タスクを持つ）
- 「休日管理」「2024年休日」などの部分一致

**「休日」も含める場合の使用例**:
```bash
uv run python -m clickup_tools.exporters.list_exporter \
  --url "https://app.clickup.com/.../v/li/..." \
  --include-subtasks \
  --include-holiday-tasks
```

#### 出力データ構造

```
domain/raw-data/clickup/
└── {space_name}/
    └── {folder_name}/              # フォルダがない場合はスキップ
        └── {list_name}/
            └── {task_id}_{task_name}/
                ├── raw.json        # API レスポンス生データ
                ├── ticket.md       # タイトルと説明
                ├── meta.md         # メタ情報
                ├── activity.md     # コメント（存在する場合のみ）
                └── attachments/    # 添付ファイル
                    ├── file1.png
                    └── file2.pdf
```

#### エクスポートされるファイル

各チケットのディレクトリには以下のファイルが保存されます：

##### 1. `raw.json`
API から取得した生データ（タスクとコメント）を JSON 形式で保存。

##### 2. `ticket.md`
タスクのタイトルと説明のみを含む Markdown ファイル。

**内容**:
- タスク名（見出し1）
- タスクの説明（Markdown 形式を優先、なければ HTML 形式）

##### 3. `meta.md`
タスクのメタ情報を含む Markdown ファイル。

**内容**:
- **基本情報**: タスク ID、ステータス、優先度、作成者、担当者、タグ、日時、URL
- **リスト情報**: リスト名、スペース名、フォルダ名
- **カスタムフィールド**: 設定されているカスタムフィールドとその値
- **添付ファイル**: 添付ファイルのリスト（ファイル名、サイズ、形式、日時）

##### 4. `activity.md`（コメントがある場合のみ）
タスクに投稿されたコメントの履歴。

**内容**:
- コメント投稿者
- 投稿日時
- コメント本文

##### 5. `attachments/`（添付ファイルがある場合のみ）
タスクに添付されたファイルを保存するディレクトリ。

## 共通モジュール

### ClickUpClient

ClickUp API との通信を担当する再利用可能なクライアントです。

**機能**:
- レート制限対策（プラン別のレート制限に対応）
- 自動リトライ（429, 5xx エラー対応）
- 各種リソースの取得（リスト、タスク、コメント、添付ファイル）
- レスポンスヘッダーの監視（X-RateLimit-Remaining など）

レート制限の詳細については、[`clickup-api-rate-limits.md`](./clickup-api-rate-limits.md) を参照してください。

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

### FileUtils

ファイル操作を担当するユーティリティモジュールです。

**機能**:
- ファイル名のサニタイズ
- ディレクトリの作成
- ファイルの保存

## 将来の拡張計画

共通モジュールを活用して、以下のようなエクスポーターを追加予定です：

```
exporters/
├── list_exporter/                # ✅ 実装済み（全ステータス対応、フィルタ可能）
├── space_structure_exporter/     # ✅ 実装済み（フォルダ・リスト一覧CSV出力）
├── task_exporter/                # 単一タスク
├── space_exporter/               # スペース全体
├── tag_filter_exporter/          # タグでフィルタ
└── date_range_exporter/          # 期間指定
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

→ 自動的にリトライされます。頻繁に発生する場合は、処理を分割するか、`--debug-limit` で件数を制限してください。詳細は [`clickup-api-rate-limits.md`](./clickup-api-rate-limits.md) を参照してください。

### URL 解析エラー

```
ValueError: リスト URL ではありません
```

→ URL 形式が正しいか確認してください。対応形式: `https://app.clickup.com/{team}/v/li/{list_id}`

### ファイル名が長すぎるエラー

タスク名が長い場合、自動的に50文字に切り詰められます。元の名前は `ticket.md` および `meta.md` に記載されます。

## 参考ドキュメント

- [ClickUp API v2 Documentation](https://clickup.com/api)
- [ClickUp API レート制限ガイド](./clickup-api-rate-limits.md)

## ライセンス

このツールは GLOW プロジェクト内部での使用を想定しています。
