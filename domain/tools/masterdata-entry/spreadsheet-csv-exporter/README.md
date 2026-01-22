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
clasp create --type standalone --title "Spreadsheet CSV Exporter"

# ファイルをGASにアップロード
clasp push
```

### 2. Webアプリとしてデプロイ

1. GASエディタを開く
   - `clasp create`実行時に表示されたURLをブラウザで開く
   - または `.clasp.json` の `scriptId` からURLを作成：
     `https://script.google.com/d/{scriptId}/edit`

2. **デプロイ** > **新しいデプロイ** をクリック

3. 歯車アイコン（種類を選択）をクリック > **ウェブアプリ** を選択

4. デプロイ設定
   - **説明**: 任意（例: v1）
   - **次のユーザーとして実行**: **アクセスしているユーザー**
   - **アクセスできるユーザー**: **組織内の全員**

5. **デプロイ**ボタンをクリック

6. デプロイURLが発行される（このURLでWebアプリにアクセス可能）

### 3. 権限の承認

初回アクセス時に、**各ユーザーが個別に**以下の権限を承認する必要があります：

- `https://www.googleapis.com/auth/spreadsheets.readonly` - スプレッドシート読み取り
- `https://www.googleapis.com/auth/drive.readonly` - Google Drive読み取り

### 4. セキュリティ設定の確認

**重要**: 以下の設定により、社内限定・安全な運用が保証されています：

- ✅ **実行権限**: アクセスユーザーの権限で動作（`executeAs: "USER_ACCESSING"`）
  - 各ユーザーが**自分の権限**でスプレッドシート・ドライブにアクセス
  - 各ユーザーがアクセスできるファイルのみダウンロード可能
  - セキュリティ的に最も安全な設定
- ✅ **公開範囲**: 組織内限定（`access: "DOMAIN"`）
  - Google Workspaceドメイン内のユーザーのみアクセス可能
  - 一般公開はされません（社外秘を保護）

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
├── Code.js                  # サーバーサイドメインロジック
├── Index.html               # WebアプリUI（タブ形式）
└── README.md                # このファイル
```

## 🔧 主要な関数

### サーバーサイド（Code.js）

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
Google APIのレート制限に引っかかる場合は、Code.js内の以下を調整：

```javascript
// Code.js: 77行目、238行目
Utilities.sleep(500); // ← この値を増やす（ミリ秒）
```

### リトライ設定

エクスポート失敗時のリトライは**最大3回、3秒/6秒/9秒待機**です。
変更する場合は `fetchSheetAsCsv()` 関数を編集してください。

### ユースケースの追加

**ユースケース**タブにカスタム機能を追加する手順：

1. `Code.js` に新しい関数を追加
2. `Index.html` の「ユースケースタブ」にカードを追加
3. ボタンのonclickイベントでGAS関数を呼び出す

## 📝 注意事項

- **権限**: 各ユーザーが自分の権限でアクセス
  - 自分がアクセスできるスプレッドシート・フォルダのみダウンロード可能
  - 他人のファイルへの不正アクセスは不可能（セキュア）
  - 初回アクセス時に各ユーザーが権限承認が必要
- **公開範囲**: 組織内（Google Workspaceドメイン）限定
  - 社外からはアクセス不可（安全）
- **実行時間**: GASの実行時間制限は6分（大量スプシの場合は注意）
- **CSV文字コード**: UTF-8（BOM付き）でエクスポート
- **ファイル名**: `/\:*?"<>|` などの特殊文字は `_` に置換

## 🐛 トラブルシューティング

### 「権限がありません」エラー

- あなた自身がスプレッドシート・フォルダにアクセスできるか確認
- 閲覧権限以上が必要（編集権限は不要）
- 初回アクセス時は権限承認画面が表示されます

### 初回アクセス時の権限承認

1. 「承認が必要です」と表示される
2. **詳細**をクリック
3. **[アプリ名]（安全ではないページ）に移動** をクリック
4. 権限を確認して**許可**をクリック
   - スプレッドシートの表示
   - Google Driveのファイルの表示

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
