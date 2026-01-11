# GLOWマスタデータ設計書ダウンローダー（OAuth版）

**OAuth 2.0認証**を使用するバージョンです。サービスアカウントよりも簡単にセットアップできます。

## 📋 サービスアカウント版との違い

| 項目 | OAuth版 | サービスアカウント版 |
|-----|---------|-------------------|
| **認証方式** | OAuth 2.0（ブラウザ認証） | サービスアカウント |
| **初回セットアップ** | ブラウザで1回認証 | サービスアカウント作成+権限付与 |
| **権限付与** | 不要（自分のアカウントでアクセス） | 各スプレッドシートに明示的に付与 |
| **トークン管理** | 自動更新（token.pickle） | 不要 |
| **利用シーン** | 個人利用、手動実行 | サーバー、バッチ処理 |

### OAuth版のメリット

✅ **簡単なセットアップ**: スプレッドシートへの権限付与不要
✅ **初回のみ認証**: 2回目以降は自動
✅ **自動トークン更新**: 有効期限切れを自動処理
✅ **個人アカウントで実行**: 自分がアクセスできるスプレッドシート全てに対応

## 前提条件

### 1. Pythonのインストール

Python 3.8以上が必要です。

```bash
python3 --version
```

### 2. gh CLIのインストール

```bash
# macOS
brew install gh

# Ubuntu/Debian
sudo apt install gh

# 認証
gh auth login
```

## セットアップ

### 1. 依存ライブラリのインストール

```bash
cd scripts/download_masterdata_design_docs
pip install -r requirements.txt
```

### 2. Google Cloud OAuth 2.0クライアントIDの作成

#### 2.1 Google Cloud Projectの作成

1. [Google Cloud Console](https://console.cloud.google.com/)にアクセス
2. 新規プロジェクトを作成（または既存プロジェクトを選択）

#### 2.2 必要なAPIを有効化

以下のAPIを有効化してください:

- **Google Sheets API**
- **Google Drive API**

[APIライブラリ](https://console.cloud.google.com/apis/library)から検索して有効化。

#### 2.3 OAuth 2.0クライアントIDの作成

1. [APIとサービス → 認証情報](https://console.cloud.google.com/apis/credentials)にアクセス
2. 「**認証情報を作成**」→「**OAuth クライアント ID**」を選択
3. 初回の場合、OAuth同意画面の設定を求められます:
   - ユーザータイプ: **外部**を選択
   - アプリ名: 任意（例: GLOW Masterdata Downloader）
   - ユーザーサポートメール: 自分のメールアドレス
   - スコープ: デフォルトのまま
   - テストユーザー: 自分のメールアドレスを追加
4. OAuth クライアント ID作成に戻る:
   - アプリケーションの種類: **デスクトップアプリ**
   - 名前: 任意（例: GLOW Downloader Desktop）
5. 「作成」をクリック
6. **JSONをダウンロード**して、`credentials.json` として保存

#### 2.4 credentials.jsonの配置

ダウンロードした `credentials.json` をスクリプトと同じディレクトリに配置してください。

```bash
scripts/download_masterdata_design_docs/
├── credentials.json  # ← ここに配置
├── download_masterdata_design_docs_oauth.py
└── ...
```

## 使い方

### 初回実行（認証）

初回実行時は自動的にブラウザが開き、Googleアカウントでの認証を求められます。

```bash
cd scripts/download_masterdata_design_docs

python3 download_masterdata_design_docs_oauth.py "一覧シートのURL"
```

**実行フロー:**

1. スクリプトを実行
2. ブラウザが自動的に開く
3. Googleアカウントでログイン
4. アクセス許可を承認
5. `token.pickle` にトークンが保存される
6. ダウンロード処理が開始

### 2回目以降の実行

保存された `token.pickle` を自動的に使用するため、ブラウザ認証は不要です。

```bash
python3 download_masterdata_design_docs_oauth.py "一覧シートのURL"
```

### 実行例

```bash
python3 download_masterdata_design_docs_oauth.py \
  "https://docs.google.com/spreadsheets/d/XXXXXXXXXXXXXXXXXXXXXXXXXXXXX/edit#gid=0"
```

### オプション

```bash
python3 download_masterdata_design_docs_oauth.py \
  --credentials /path/to/credentials.json \
  --repo-path /path/to/glow-brain \
  "一覧シートのURL"
```

## 実行フロー

スクリプトの実行フローは以下の通りです:

1. ✅ **認証**: 初回のみブラウザで認証、以降は自動
2. 📋 **リリースキー取得**: 一覧シートのB3セルから抽出
3. 🔍 **詳細シートURL取得**: 一覧シート内のURLを抽出
4. 📄 **進捗管理表解析**: 各詳細シートから設計書URLを抽出
5. 📚 **HTML変換**: 各設計書の全シートをHTML形式でダウンロード
6. 💾 **ファイル保存**: `マスターデータ/リリース/{リリースキー}/raw/` に保存
7. 🔧 **Git操作**: ブランチ作成、コミット、プッシュ
8. 🚀 **PR作成**: GitHub Pull Request自動作成

## 出力ファイル構造

```
glow-brain/
├── マスターデータ/
│   └── リリース/
│       └── {リリースキー}/
│           └── raw/
│               ├── {スプレッドシート名1}/
│               │   ├── sheet1.html
│               │   └── ...
│               └── {スプレッドシート名2}/
│                   └── ...
└── scripts/
    └── download_masterdata_design_docs/
        ├── credentials.json  # OAuth 2.0クライアントID
        └── token.pickle      # 保存された認証トークン
```

## トークン管理

### トークンの保存場所

初回認証後、認証トークンは `token.pickle` に保存されます。

### トークンの有効期限

- アクセストークン: 1時間
- リフレッシュトークン: 無期限（または6ヶ月未使用で失効）

トークンの有効期限が切れた場合、スクリプトが自動的にリフレッシュします。

### 再認証が必要な場合

以下の場合は `token.pickle` を削除して再度認証してください:

```bash
rm token.pickle
python3 download_masterdata_design_docs_oauth.py "URL"
```

**再認証が必要なケース:**
- リフレッシュトークンが失効した場合
- スコープを変更した場合
- エラーが発生した場合

## トラブルシューティング

### エラー: 認証情報ファイルが見つかりません

```
❌ エラー: 認証情報ファイルが見つかりません: credentials.json
```

**解決方法**:

1. Google Cloud Consoleで OAuth 2.0クライアントIDを作成
2. JSONをダウンロード
3. `credentials.json` として保存
4. スクリプトと同じディレクトリに配置

---

### エラー: ブラウザが開かない

```
Please visit this URL to authorize this application: https://...
```

**解決方法**:

1. 表示されたURLをコピー
2. ブラウザで手動で開く
3. 認証を完了

---

### エラー: アクセスがブロックされました

```
Error 403: access_denied
This app is blocked
```

**解決方法**:

OAuth同意画面で自分のメールアドレスを**テストユーザー**に追加してください。

1. [OAuth同意画面](https://console.cloud.google.com/apis/credentials/consent)にアクセス
2. 「テストユーザー」セクションで「ADD USERS」
3. 自分のGoogleアカウントのメールアドレスを追加

---

### エラー: invalid_grant

```
google.auth.exceptions.RefreshError: ('invalid_grant: Token has been expired or revoked.', ...)
```

**解決方法**: トークンが失効しています。再認証してください。

```bash
rm token.pickle
python3 download_masterdata_design_docs_oauth.py "URL"
```

---

### エラー: Google APIの権限エラー

```
HttpError 403: The caller does not have permission
```

**解決方法**:

1. 対象のスプレッドシートに自分のGoogleアカウントでアクセスできるか確認
2. Google Sheets APIとGoogle Drive APIが有効化されているか確認

---

### トークンをリセットしたい

```bash
# トークンを削除
rm token.pickle

# 次回実行時に再認証
python3 download_masterdata_design_docs_oauth.py "URL"
```

---

### 複数のGoogleアカウントを使い分けたい

`token.pickle` が特定のアカウントに紐づいているため、アカウントを切り替える場合は:

```bash
# 現在のトークンを削除
rm token.pickle

# 別のアカウントで再認証
python3 download_masterdata_design_docs_oauth.py "URL"
# → ブラウザで別のGoogleアカウントを選択
```

## セキュリティに関する注意

### 認証情報の管理

- `credentials.json` はクライアントシークレットを含むため機密情報です
- `token.pickle` は認証トークンを含むため機密情報です
- **両方ともGitにコミットしないでください**（`.gitignore`に追加済み）

### OAuth同意画面の公開範囲

- 本スクリプトでは「**外部**」の「**テストモード**」で十分です
- 公開する必要はありません
- テストユーザーに追加したアカウントのみ使用可能

## サービスアカウント版との使い分け

### OAuth版を使うべき場合

- ✅ 個人利用
- ✅ 手動実行
- ✅ 開発・テスト環境
- ✅ 簡単なセットアップが必要

### サービスアカウント版を使うべき場合

- ✅ サーバーでの自動実行
- ✅ CI/CDパイプライン
- ✅ 複数人での共有実行
- ✅ ブラウザ認証ができない環境

## 制限事項

1. **ファイルサイズ制限**: Google Drive APIのHTML形式エクスポートは最大10MBまで
2. **API制限**: Google APIには1日あたりのクォータ制限があります
3. **トークン有効期限**: リフレッシュトークンは6ヶ月未使用で失効します
4. **同意画面**: テストモードでは100ユーザーまで

## よくある質問

### Q: サービスアカウント版とどちらを使えばいい？

**A:** 個人利用で手動実行する場合は**OAuth版**が簡単です。サーバーでの自動実行や複数人での共有にはサービスアカウント版を推奨します。

### Q: token.pickleは共有できる？

**A:** いいえ。`token.pickle`は個人のアカウントに紐づいているため、他の人と共有しないでください。

### Q: 認証が面倒。スキップできない？

**A:** 初回のみの認証で、2回目以降は自動です。サーバーで完全自動化したい場合はサービスアカウント版を使用してください。

### Q: OAuth 2.0クライアントIDとサービスアカウントの違いは？

**A:**
- **OAuth 2.0**: ユーザーが自分のアカウントでログインして認証
- **サービスアカウント**: アプリケーション専用のアカウント（メールアドレス形式）

## 関連リンク

- [Google OAuth 2.0 ドキュメント](https://developers.google.com/identity/protocols/oauth2)
- [Google Sheets API](https://developers.google.com/sheets/api)
- [Google Drive API](https://developers.google.com/drive/api)
- [GitHub CLI](https://cli.github.com/)

## お問い合わせ

問題が発生した場合は、glow-brainリポジトリのIssuesに報告してください。
