# データQAまとめからマスタデータ書一括ダウンロードスクリプト

GAS「データQAまとめからマスタデータ書一括ダウンロード」をPythonで再実装したCLIツールです。

## 概要

一覧シート（「データQAまとめ」）から詳細シート→設計書を辿り、全シートをHTML/CSV形式でZIPダウンロードします。

### 処理フロー

1. 一覧シートの「総合進捗管理」シートからB列のハイパーリンクを取得（詳細シートのURL）
2. 各詳細シートの「進捗管理表」シートから設計書URLを抽出（テキスト内URL）
3. 各設計書スプレッドシートの全シートをHTML/CSV形式でエクスポート
4. ZIPファイルとして圧縮してダウンロード

## クイックスタート

```bash
# 1. uvをインストール（初回のみ）
curl -LsSf https://astral.sh/uv/install.sh | sh

# 2. スクリプトディレクトリに移動
cd マスタデータ/ツール/scripts

# 3. 依存ライブラリをインストール（初回のみ）
uv sync

# 4. credentials.jsonを配置（初回のみ）
#    このディレクトリに credentials.json を配置してください

# 5. 実行
uv run download_masterdata_docs.py \
  --url "https://docs.google.com/spreadsheets/d/YOUR_SHEET_ID/edit" \
  --output output.zip
```

## セットアップ

### 1. uvのインストール

[uv](https://github.com/astral-sh/uv)は高速なPythonパッケージマネージャーです。

```bash
# macOS/Linux
curl -LsSf https://astral.sh/uv/install.sh | sh

# または Homebrew
brew install uv
```

### 2. 依存ライブラリのインストール

スクリプトディレクトリに移動してインストール：

```bash
cd マスタデータ/ツール/scripts

# pyproject.tomlの依存関係を解決してインストール
uv sync
```

これにより、以下が作成されます：
- `.venv/` - 仮想環境（自動作成、gitignore対象）
- `uv.lock` - 依存関係のロックファイル（gitコミット推奨、再現性確保）

**注**:
- `uv sync`は初回のみ必要です。2回目以降は`uv run`だけで実行できます。
- `uv.lock`をgitコミットすることで、チーム全体で同じバージョンの依存関係を使えます。

### 3. サービスアカウント認証の設定

#### 3-1. サービスアカウントの作成

1. [Google Cloud Console](https://console.cloud.google.com/)にアクセス
2. プロジェクトを選択（または新規作成）
3. 「APIとサービス」→「認証情報」→「認証情報を作成」→「サービスアカウント」
4. サービスアカウント名を入力して作成
5. 「キー」タブ→「鍵を追加」→「新しい鍵を作成」→「JSON」を選択
6. `credentials.json`ファイルがダウンロードされます

#### 3-2. APIの有効化

1. [Google Cloud Console](https://console.cloud.google.com/)で「APIとサービス」→「ライブラリ」
2. 以下のAPIを検索して有効化：
   - **Google Sheets API**
   - **Google Drive API**

#### 3-3. スプレッドシートへのアクセス権限付与

1. ダウンロードした`credentials.json`を開く
2. `client_email`フィールドの値をコピー（例: `xxx@xxx.iam.gserviceaccount.com`）
3. 対象のスプレッドシートを開き、共有設定でこのメールアドレスに「閲覧者」権限を付与

#### 3-4. credentials.jsonの配置

以下のいずれかの方法でcredentials.jsonを配置してください：

- **方法1（推奨）**: スクリプトと同じディレクトリに配置
  ```bash
  # credentials.json.example をコピーして編集
  cd マスタデータ/ツール/scripts
  cp credentials.json.example credentials.json
  # credentials.json を編集して実際の値を入力
  ```

- **方法2**: 任意の場所に配置し、`--credentials`オプションで指定
  ```bash
  uv run download_masterdata_docs.py --url "..." --credentials /path/to/credentials.json
  ```

## 使い方

### 基本的な使い方

```bash
cd マスタデータ/ツール/scripts

# 初回のみ依存関係をインストール
uv sync

# 実行
uv run download_masterdata_docs.py \
  --url "https://docs.google.com/spreadsheets/d/YOUR_SHEET_ID/edit" \
  --output output.zip
```

**重要**: `uv sync`は初回のみ必要です。2回目以降は`uv run`だけで実行できます。

### オプション一覧

| オプション | 説明 | デフォルト |
|-----------|------|-----------|
| `--url` | 一覧シート（データQAまとめ）のURL（必須） | - |
| `--output` | 出力ZIPファイル名 | `output.zip` |
| `--format` | 出力形式（`html` または `csv`） | `html` |
| `--credentials` | credentials.jsonのパス | スクリプトと同じディレクトリ |
| `--delay` | 各エクスポート間のディレイ（ミリ秒） | `1000` |
| `--debug-limit` | デバッグ用の処理件数制限 | なし（全件処理） |

### 使用例

#### HTML形式でダウンロード（デフォルト）

```bash
uv run download_masterdata_docs.py \
  --url "https://docs.google.com/spreadsheets/d/YOUR_SHEET_ID/edit" \
  --output masterdata_docs.zip
```

#### CSV形式でダウンロード

```bash
uv run download_masterdata_docs.py \
  --url "https://docs.google.com/spreadsheets/d/YOUR_SHEET_ID/edit" \
  --format csv \
  --output masterdata_docs_csv.zip
```

#### デバッグモード（最初の3件のみ処理）

```bash
uv run download_masterdata_docs.py \
  --url "https://docs.google.com/spreadsheets/d/YOUR_SHEET_ID/edit" \
  --debug-limit 3
```

#### カスタム認証ファイルを使用

```bash
uv run download_masterdata_docs.py \
  --url "https://docs.google.com/spreadsheets/d/YOUR_SHEET_ID/edit" \
  --credentials ~/gcp/my-credentials.json
```

#### エイリアスを設定して簡単に使う

```bash
# .zshrc または .bashrc に追加
alias dl-masterdata="cd /path/to/マスタデータ/ツール/scripts && uv run download_masterdata_docs.py"

# 使用例
dl-masterdata --url "https://docs.google.com/spreadsheets/d/YOUR_SHEET_ID/edit"
```

### uvの動作

1. **`uv sync`** - pyproject.tomlから依存関係を読み取り、仮想環境にインストール
2. **`uv run`** - 仮想環境を使ってスクリプトを実行

### uvを使う利点

- **高速**: Rustで書かれており、pipより10〜100倍高速
- **依存関係の固定**: `uv.lock`で完全な再現性を確保
- **仮想環境自動管理**: 明示的な`venv`作成が不要
- **プロジェクト管理**: `pyproject.toml`でモダンなPythonプロジェクト管理

## 出力形式

### ZIPファイル構造

```
output.zip
├── 設計書1_フォルダ名/
│   ├── シート1.html
│   ├── シート2.html
│   └── シート3.html
├── 設計書2_フォルダ名/
│   ├── シート1.html
│   └── シート2.html
...
```

各設計書ごとにフォルダが作成され、その中に全シートがHTML/CSV形式で保存されます。

### 処理結果の統計情報

スクリプト実行後、以下の統計情報が表示されます：

```
============================================================
処理結果：
  詳細シート: 10/10件成功
  設計書: 25件検出
  生成ファイル: 150件
  エラー/警告: 0件
  出力ファイル: output.zip
============================================================
```

## 注意事項

### レートリミット対策

- 各シートエクスポート後に1秒（デフォルト）のディレイを設定
- HTTPステータス429または5xxの場合は自動的にリトライ（最大3回）
- リトライ間隔は3秒、6秒、9秒と増加

### エラーハンドリング

以下の場合はエラーログを出力してスキップします：

- 無効なURL形式
- スプレッドシートアクセス権限エラー
- シートが見つからない場合
- ネットワークエラー（リトライ上限到達時）

### アクセス権限

サービスアカウントに、対象となる全てのスプレッドシートへの閲覧権限が必要です。

## トラブルシューティング

### uvがインストールされていない

```
command not found: uv
```

**解決策**:
```bash
# macOS/Linux
curl -LsSf https://astral.sh/uv/install.sh | sh

# または Homebrew
brew install uv
```

### モジュールが見つからない

```
ModuleNotFoundError: No module named 'gspread'
```

**解決策**:
```bash
# 依存関係をインストール
cd マスタデータ/ツール/scripts
uv sync

# 実行
uv run download_masterdata_docs.py --url "..."
```

### credentials.jsonが見つからない

```
[ERROR] credentials.jsonが見つかりません: /path/to/credentials.json
```

**解決策**:
- `--credentials`オプションで正しいパスを指定
- または、スクリプトと同じディレクトリに`credentials.json`を配置

### アクセス権限エラー

```
[ERROR] スプレッドシートアクセス失敗: Permission denied
```

**解決策**:
- サービスアカウントに対象スプレッドシートの閲覧権限を付与
- Google Sheets API、Google Drive APIが有効になっているか確認

### シートが見つからない

```
[WARN] 「進捗管理表」シートが見つかりません
```

**解決策**:
- 詳細シートに「進捗管理表」シートが存在するか確認
- シート名が正確に一致しているか確認

### レートリミットエラー

```
[WARN] [リトライ] シート名.html: HTTPコード429、3秒待機後に再試行...
```

**解決策**:
- 自動的にリトライされるため、待機してください
- `--delay`オプションで間隔を長くする（例: `--delay 2000`で2秒）

## GASとの違い

| 機能 | GAS | Python + uv |
|------|-----|-------------|
| 実行環境 | ブラウザ | ローカルCLI |
| 認証方式 | OAuth2（ユーザー） | サービスアカウント |
| UI | Webアプリ | コマンドライン |
| リアルタイムログ | あり | コンソール出力 |
| バッチ処理 | 制限あり | 制限なし |
| パッケージ管理 | 不要 | uv（高速・自動） |
| 自動化 | トリガー設定 | cron/シェルスクリプト |

## ライセンス

このスクリプトはGLOWプロジェクト内部での使用を目的としています。
