# Spreadsheet CSV Exporter

スプレッドシートをCSVとしてダウンロードする汎用GAS Webアプリ

## 📋 概要

Google SpreadsheetをCSV形式でエクスポートし、ZIP形式でダウンロードできるWebアプリケーションです。

### 主な機能

1. **単一スプシダウンロード**
   - スプレッドシートのURLを入力
   - 全シートを1シート1CSVでZIPダウンロード

2. **フォルダ一括ダウンロード**
   - Google DriveフォルダのURL/IDを入力
   - フォルダ内のスプレッドシート一覧を表示
   - チェックボックスで選択したスプシを個別ZIPでダウンロード

3. **ユースケース拡張**
   - 特定用途向けの専用ダウンローダーを追加可能（将来拡張）

4. **リアルタイムログ**
   - 処理状況をターミナル風UIでリアルタイム表示

## 🚀 セットアップ

### 1. GASプロジェクトの作成

```bash
cd domain/tools/masterdata-entry/spreadsheet-csv-exporter

# claspで新規GASプロジェクトを作成
clasp create --type webapp --title "Spreadsheet CSV Exporter"

# ファイルをGASにアップロード
clasp push
```

### 2. Webアプリとしてデプロイ

1. GASエディタを開く
   ```bash
   clasp open
   ```

2. **デプロイ** > **新しいデプロイ** を選択

3. デプロイ設定
   - **種類**: Webアプリ
   - **実行ユーザー**: 自分
   - **アクセスできるユーザー**: 組織内の全員（DOMAIN）

4. **デプロイ**ボタンをクリック

5. デプロイURLが発行される（このURLでWebアプリにアクセス可能）

### 3. 権限の承認

初回アクセス時に以下の権限を承認する必要があります：

- `https://www.googleapis.com/auth/spreadsheets.readonly` - スプレッドシート読み取り
- `https://www.googleapis.com/auth/drive.readonly` - Google Drive読み取り

## 📖 使い方

### 単一スプシのダウンロード

1. **単一スプシ**タブを開く
2. スプレッドシートのURLを入力
3. **ダウンロード**ボタンをクリック
4. 処理完了後、ZIPファイルが自動ダウンロードされる

**ZIP構成例**:
```
マスタデータ設計書.zip
├── マスタデータ設計書/
│   ├── シート1.csv
│   ├── シート2.csv
│   └── シート3.csv
```

### フォルダ一括ダウンロード

1. **フォルダ一括**タブを開く
2. Google DriveフォルダのURL または IDを入力
   - URL例: `https://drive.google.com/drive/folders/1a2b3c4d5e...`
   - ID例: `1a2b3c4d5e...`
3. **一覧取得**ボタンをクリック
4. スプレッドシート一覧が表示される
5. ダウンロードしたいスプシにチェック
6. **選択したものをダウンロード**ボタンをクリック
7. 選択したスプシごとに個別ZIPがダウンロードされる

**ダウンロード方式**:
- 順次ダウンロード（1つずつ処理完了→即座にダウンロード）
- GAS実行時間制限（6分）のリスクを低減

### ユースケース拡張

**ユースケース**タブには将来的に特定用途向けの専用ダウンローダーを追加できます。

現在準備中のユースケース：
- マスタデータ運営仕様書ダウンローダー
- QAテストケースダウンローダー

## 🛠️ ファイル構成

```
spreadsheet-csv-exporter/
├── .clasp.json              # clasp設定（GASプロジェクトID）
├── appsscript.json          # GASマニフェスト
├── コード.js                 # サーバーサイドメインロジック
├── Index.html               # WebアプリUI（タブ形式）
└── README.md                # このファイル
```

## 🔧 主要な関数

### サーバーサイド（コード.js）

| 関数名 | 用途 |
|--------|------|
| `doGet()` | WebアプリUI表示 |
| `downloadSingleSpreadsheet(url, sessionId)` | 単一スプシのCSV ZIP作成 |
| `listSpreadsheetsInFolder(folderInput, sessionId)` | フォルダ内スプシ一覧取得 |
| `downloadSpreadsheetById(ssId, sessionId)` | 指定IDのスプシをCSV ZIP化 |
| `fetchLogs(sessionId)` | ログ取得（ポーリング用） |
| `saveLog()` / `getLogs()` / `clearLogs()` | ログ管理 |
| `sanitizeFileName()` | ファイル名サニタイズ |
| `normalizeSpreadsheetUrl()` | URL正規化 |
| `extractFolderId()` | フォルダID抽出 |
| `fetchSheetAsCsv()` | CSVエクスポート（リトライ付き） |

### クライアントサイド（Index.html）

- タブ切り替えUI（単一スプシ / フォルダ一括 / ユースケース）
- リアルタイムログ表示（CacheServiceでポーリング）
- ZIPダウンロード処理

## ⚙️ カスタマイズ

### レートリミット対策

各シートのエクスポート後に**500msのスリープ**を設定しています。
Google APIのレート制限に引っかかる場合は、コード.js内の以下を調整：

```javascript
// コード.js: 77行目、238行目
Utilities.sleep(500); // ← この値を増やす（ミリ秒）
```

### リトライ設定

エクスポート失敗時のリトライは**最大3回、3秒/6秒/9秒待機**です。
変更する場合は `fetchSheetAsCsv()` 関数を編集してください。

### ユースケースの追加

**ユースケース**タブにカスタム機能を追加する手順：

1. `コード.js` に新しい関数を追加
2. `Index.html` の「ユースケースタブ」にカードを追加
3. ボタンのonclickイベントでGAS関数を呼び出す

## 📝 注意事項

- **権限**: スプレッドシート・フォルダへのアクセス権限が必要
- **実行時間**: GASの実行時間制限は6分（大量スプシの場合は注意）
- **CSV文字コード**: UTF-8（BOM付き）でエクスポート
- **ファイル名**: `/\:*?"<>|` などの特殊文字は `_` に置換

## 🐛 トラブルシューティング

### 「権限がありません」エラー

- スプレッドシート・フォルダの共有設定を確認
- 閲覧権限以上が必要

### レートリミットエラー（HTTP 429）

- リトライ処理が自動で実行される
- 連続エラーの場合は `Utilities.sleep()` の値を増やす

### ZIP内のCSVが文字化けする

- Excelで開く場合、UTF-8（BOM付き）に対応していない可能性
- テキストエディタで開くか、Googleスプレッドシートでインポート

## 📚 参考

- [Google Apps Script - Webアプリ](https://developers.google.com/apps-script/guides/web)
- [DriveApp Class](https://developers.google.com/apps-script/reference/drive/drive-app)
- [SpreadsheetApp Class](https://developers.google.com/apps-script/reference/spreadsheet/spreadsheet-app)

## 🔗 関連ツール

- `bulk-download-materdata-specs-from-data-qa-summary-spreadsheet/` - マスタデータ設計書一括ダウンローダー（参照実装）

---

**作成者**: GLOW Brain Project
**最終更新**: 2026-01-22
