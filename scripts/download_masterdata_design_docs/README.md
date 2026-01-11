# GLOWマスタデータ設計書ダウンローダー

Googleスプレッドシートから設計書をHTML形式でダウンロードし、glow-brainリポジトリにPull Requestを作成するPythonスクリプトです。

## 機能

1. 一覧シートからリリースキーを自動抽出（B3セル）
2. 詳細シートURLを全て取得
3. 各詳細シートの「進捗管理表」から設計書URLを抽出
4. 各設計書の全シートをHTML形式でダウンロード
5. `マスターデータ/リリース/{リリースキー}/raw/` に保存
6. 自動的にGitブランチ作成、コミット、プッシュ
7. GitHub Pull Requestを自動作成

## 前提条件

### 1. Pythonのインストール

Python 3.8以上が必要です。

```bash
python3 --version
```

### 2. 必要なツールのインストール

```bash
# gh CLI（GitHub CLI）
brew install gh  # macOS
# または
sudo apt install gh  # Ubuntu/Debian

# gh CLIで認証
gh auth login
```

### 3. Google Cloud サービスアカウントの設定

#### 3.1 Google Cloud Projectの作成

1. [Google Cloud Console](https://console.cloud.google.com/)にアクセス
2. プロジェクトを作成（既存プロジェクトを使用する場合はスキップ）

#### 3.2 必要なAPIを有効化

以下のAPIを有効化してください:

- Google Sheets API
- Google Drive API

```bash
# gcloud CLIを使用する場合
gcloud services enable sheets.googleapis.com
gcloud services enable drive.googleapis.com
```

または、[APIライブラリ](https://console.cloud.google.com/apis/library)から手動で有効化。

#### 3.3 サービスアカウントの作成

1. [IAMと管理 > サービスアカウント](https://console.cloud.google.com/iam-admin/serviceaccounts)にアクセス
2. 「サービスアカウントを作成」をクリック
3. サービスアカウント名を入力（例: `glow-masterdata-downloader`）
4. 「作成して続行」をクリック
5. ロールは不要（スキップ）
6. 「完了」をクリック

#### 3.4 認証キーの作成

1. 作成したサービスアカウントをクリック
2. 「キー」タブを選択
3. 「鍵を追加」→「新しい鍵を作成」
4. キーのタイプ: **JSON**を選択
5. 「作成」をクリック
6. ダウンロードされたJSONファイルを `credentials.json` として保存

#### 3.5 スプレッドシートへのアクセス権限付与

サービスアカウントのメールアドレス（`xxx@xxx.iam.gserviceaccount.com`）に対して、対象のスプレッドシートの**閲覧権限**を付与してください。

**重要**: 一覧シート、詳細シート、設計書の全てのスプレッドシートに対して閲覧権限が必要です。

## インストール

### 1. リポジトリのクローン

既にglow-brainをクローンしている場合はスキップしてください。

```bash
cd /path/to/your/workspace
git clone <glow-brain-repository-url>
cd glow-brain
```

### 2. 依存ライブラリのインストール

```bash
cd scripts/download_masterdata_design_docs
pip install -r requirements.txt
```

または、仮想環境を使用する場合:

```bash
cd scripts/download_masterdata_design_docs

# 仮想環境作成
python3 -m venv venv

# 仮想環境有効化
source venv/bin/activate  # macOS/Linux
# または
venv\Scripts\activate  # Windows

# 依存ライブラリインストール
pip install -r requirements.txt
```

### 3. 認証情報の配置

ダウンロードした `credentials.json` をスクリプトと同じディレクトリに配置してください。

```bash
scripts/download_masterdata_design_docs/
├── credentials.json  # ← ここに配置
├── download_masterdata_design_docs.py
├── requirements.txt
└── README.md
```

## 使い方

### 基本的な使い方

```bash
cd scripts/download_masterdata_design_docs

python3 download_masterdata_design_docs.py "一覧シートのURL"
```

### 例

```bash
python3 download_masterdata_design_docs.py \
  "https://docs.google.com/spreadsheets/d/XXXXXXXXXXXXXXXXXXXXXXXXXXXXX/edit#gid=0"
```

### オプション

```bash
python3 download_masterdata_design_docs.py \
  --credentials /path/to/credentials.json \
  --repo-path /path/to/glow-brain \
  "一覧シートのURL"
```

#### オプション一覧

| オプション | デフォルト値 | 説明 |
|-----------|------------|------|
| `--credentials` | `credentials.json` | サービスアカウント認証情報JSONファイルパス |
| `--repo-path` | `/Users/junki.mizutani/Documents/workspace/glow/glow-brain` | glow-brainリポジトリのパス |

## 実行フロー

1. **リリースキー取得**: 一覧シートのB3セルから「リリースキー:YYYYMMDD」形式を抽出
2. **詳細シートURL取得**: 一覧シート内の全てのGoogleスプレッドシートURLを抽出
3. **進捗管理表解析**: 各詳細シートから「進捗管理表」シートを探す
4. **設計書URL取得**: 進捗管理表から設計書のスプレッドシートURLを抽出
5. **HTML変換**: 各設計書の全シートをGoogle Drive APIでHTML(ZIP)形式でエクスポート
6. **ファイル保存**: ZIPを展開し、HTMLファイルを `マスターデータ/リリース/{リリースキー}/raw/{スプレッドシート名}/` に保存
7. **Git操作**:
   - 新しいブランチ `masterdata-docs-{リリースキー}` を作成
   - ファイルを追加してコミット
   - リモートにプッシュ
8. **PR作成**: `gh` CLIを使用してPull Requestを作成

## 出力ファイル構造

```
glow-brain/
└── マスターデータ/
    └── リリース/
        └── {リリースキー}/
            └── raw/
                ├── {スプレッドシート名1}/
                │   ├── sheet1.html
                │   ├── sheet2.html
                │   └── ...
                ├── {スプレッドシート名2}/
                │   └── ...
                └── ...
```

## トラブルシューティング

### エラー: 認証情報ファイルが見つかりません

```
❌ エラー: 認証情報ファイルが見つかりません: credentials.json
```

**解決方法**: `credentials.json` が正しい場所に配置されているか確認してください。

---

### エラー: リリースキーが見つかりませんでした

```
❌ エラーが発生しました: リリースキーが見つかりませんでした。セル値: '...'
```

**解決方法**: 一覧シートのB3セルに「リリースキー:20251202」形式でリリースキーが記載されているか確認してください。

---

### エラー: Google API権限エラー

```
HttpError 403: The caller does not have permission
```

**解決方法**:
1. サービスアカウントのメールアドレスに対して、スプレッドシートの閲覧権限を付与してください
2. Google Sheets APIとGoogle Drive APIが有効化されているか確認してください

---

### エラー: gh CLI が見つかりません

```
FileNotFoundError: [Errno 2] No such file or directory: 'gh'
```

**解決方法**: gh CLIをインストールしてください。

```bash
# macOS
brew install gh

# Ubuntu/Debian
sudo apt install gh

# 認証
gh auth login
```

---

### エラー: git操作が失敗しました

```
fatal: not a git repository
```

**解決方法**: `--repo-path` オプションで正しいglow-brainリポジトリのパスを指定してください。

---

### エラー: HTMLファイルが0件

特定のシートでHTMLファイルが保存されない場合、以下を確認してください:

1. スプレッドシートのサイズが10MB以下か（Google Drive APIの制限）
2. サービスアカウントに閲覧権限があるか
3. シートが非表示になっていないか

## セキュリティに関する注意

### 認証情報の管理

- `credentials.json` は機密情報です
- Gitにコミットしないでください（`.gitignore` に追加推奨）
- 不要になったサービスアカウントキーは削除してください

### サービスアカウントの権限

- 必要最小限の権限（スプレッドシート閲覧のみ）で運用してください
- 本番環境では、アクセス可能なスプレッドシートを制限することを推奨します

## 制限事項

1. **ファイルサイズ制限**: Google Drive APIのHTML形式エクスポートは最大10MBまで
2. **API制限**: Google APIには1日あたりのクォータ制限があります
3. **シート名**: 「進捗管理表」という名前のシートが存在することが前提
4. **リリースキー形式**: B3セルに「リリースキー:YYYYMMDD」形式で記載されていることが前提

## ライセンス

このスクリプトはglow-brainプロジェクトの一部です。

## 開発者向け情報

### コード構成

- `MasterdataDownloader`: メインクラス
  - Google Sheets/Drive API操作
  - URL/データ抽出
  - HTML変換・保存
  - Git操作・PR作成

### デバッグモード

エラー発生時、自動的にスタックトレースが表示されます。

### カスタマイズ

スクリプト内の以下の定数を変更することで、動作をカスタマイズできます:

- `SCOPES`: Google APIのスコープ
- `B3セル`: リリースキーの取得位置
- `進捗管理表`: シート名

## 関連リンク

- [Google Sheets API](https://developers.google.com/sheets/api)
- [Google Drive API](https://developers.google.com/drive/api)
- [GitHub CLI](https://cli.github.com/)
- [glow-brainリポジトリ](https://github.com/your-org/glow-brain)

## お問い合わせ

問題が発生した場合は、glow-brainリポジトリのIssuesに報告してください。
