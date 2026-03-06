# gspread-to-xlsx

Google SpreadsheetをXLSX形式でダウンロードするCLIツールです。
URL/IDを複数指定でき、GoogleドライブのフォルダhierarchyをそのままローカルPCに再現して保存します。

## 概要

- サービスアカウント認証でGoogle Drive APIを使用
- SpreadsheetのURL または ID を複数指定可能
- Driveのフォルダ構成を再現して保存

**保存先**: `domain/raw-data/google-drive/spread-sheet/{Driveフォルダ構成}/{ファイル名}.xlsx`

## セットアップ

### 1. サービスアカウントの準備

1. [Google Cloud Console](https://console.cloud.google.com/) でサービスアカウントを作成
2. **Google Drive API** を有効化
3. サービスアカウントのキー（JSON）をダウンロード
4. `credentials.json.example` をコピーして `credentials.json` を作成し、内容を書き換える

```bash
cp credentials.json.example credentials.json
# credentials.json を実際のサービスアカウントJSONで置き換える
```

5. ダウンロードしたいSpreadsheetをサービスアカウントのメールアドレスに **閲覧者以上** で共有する

> **注意**: `credentials.json` は `.gitignore` で除外されています。絶対にコミットしないでください。

### 2. 依存パッケージのインストール

**uvを使う場合（推奨）**:
```bash
uv sync
```

**pipを使う場合**:
```bash
pip install -r requirements.txt
```

## 使い方

```bash
# URLで指定（複数可）
uv run python gspread_to_xlsx.py \
  "https://docs.google.com/spreadsheets/d/ABC123/edit" \
  "https://docs.google.com/spreadsheets/d/XYZ456/edit"

# スプシIDで指定（URLのID部分のみ）
uv run python gspread_to_xlsx.py ABC123 XYZ456

# credentials.jsonのパスを明示指定
uv run python gspread_to_xlsx.py "https://..." \
  --credentials /path/to/credentials.json

# 出力先ディレクトリを指定
uv run python gspread_to_xlsx.py "https://..." \
  --output-dir /path/to/output
```

### オプション

| オプション | デフォルト | 説明 |
|-----------|-----------|------|
| `targets` | （必須） | SpreadsheetのURLまたはID（複数指定可） |
| `--credentials` | `./credentials.json` | サービスアカウントのJSONパス |
| `--output-dir` | `domain/raw-data/google-drive/spread-sheet/` | 出力先ディレクトリ |

## 保存先の例

Drive上のフォルダ構成:
```
マイドライブ
└── GLOW
    └── 080_運営
        └── ガチャ設計書
```

保存先:
```
domain/raw-data/google-drive/spread-sheet/
└── GLOW/
    └── 080_運営/
        └── ガチャ設計書.xlsx
```

> ルートフォルダ（マイドライブ）は除外されます。

## トラブルシューティング

### `credentials.jsonが見つかりません`

`credentials.json` がスクリプトと同じディレクトリにあるか確認してください。
または `--credentials` オプションでパスを指定してください。

### `HttpError 403: The caller does not have permission`

サービスアカウントのメールアドレスにSpreadsheetの閲覧権限が付与されているか確認してください。

### `HttpError 429: Rate Limit Exceeded`

自動でリトライします（最大3回、3秒・6秒・9秒待機）。
多数のファイルを処理する場合は時間をおいて再実行してください。
