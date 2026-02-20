# GAS版との機能対応表

## 機能比較

| 機能 | GAS版 | Local版 | 実装状況 |
|------|-------|---------|----------|
| **単一スプシダウンロード** | ✅ | ✅ | ✅ 完全実装 |
| **フォルダ一括ダウンロード** | ✅ | ✅ | ✅ 完全実装 |
| **シート名フィルタ** | ✅ | ✅ | ✅ 完全実装 |
| **複数スプシ一括** | ✅ | ✅ | ✅ 完全実装 |
| **進捗管理表スキャン** | ✅ | ✅ | ✅ 完全実装 |
| **非表示シート除外** | ✅ | ✅ | ✅ 完全実装 |
| **レートリミット対策** | ✅ | ✅ | ✅ 完全実装 |
| **リトライ処理** | ✅ | ✅ | ✅ 完全実装 |
| **ファイル名サニタイズ** | ✅ | ✅ | ✅ 完全実装 |
| **UTF-8 BOM付きCSV** | ✅ | ✅ | ✅ 完全実装 |

## 実装方式の違い

### 認証

| 項目 | GAS版 | Local版 |
|------|-------|---------|
| 認証方式 | ユーザーOAuth | サービスアカウント |
| 権限承認 | 各ユーザーが初回に承認 | 事前にフォルダ共有が必要 |
| 実行権限 | アクセスユーザーの権限 | サービスアカウントの権限 |

### UI/UX

| 項目 | GAS版 | Local版 |
|------|-------|---------|
| インターフェース | Webブラウザ | コマンドライン |
| ログ表示 | リアルタイムターミナル風UI | コンソールログ |
| スプシ選択 | チェックボックス | 番号入力（インタラクティブ） |
| 中断機能 | ✅ ボタン | ⚠️ Ctrl+C |

### パフォーマンス

| 項目 | GAS版 | Local版 |
|------|-------|---------|
| 実行時間制限 | ⚠️ 6分 | ✅ なし |
| 同時処理数 | 順次処理 | 順次処理 |
| メモリ制限 | ⚠️ GAS制限あり | ✅ Node.js制限 |

## 関数対応表

### サーバーサイド関数（GAS Code.js → Local exporter.ts）

| GAS関数 | Local関数 | 説明 |
|---------|-----------|------|
| `downloadSingleSpreadsheet()` | `exportSpreadsheetToZip()` | 単一スプシのCSV ZIP化 |
| `listSpreadsheetsInFolder()` | `listSpreadsheetsInFolder()` | フォルダ内スプシ一覧取得 |
| `downloadSpreadsheetById()` | `exportSpreadsheetToZip()` | ID指定でスプシCSV ZIP化 |
| `downloadFilteredSheets()` | `exportFilteredSheets()` | シート名フィルタ |
| `downloadMultipleSpreadsheets()` | `multipleCommand()` | 複数スプシ一括 |
| `scanProgressSheetsForUrls()` | `scanProgressSheetsForUrls()` | 進捗管理表スキャン |
| `fetchSheetAsCsv()` | `exportSheetAsCsvViaExport()` | シート→CSV変換 |
| `sanitizeFileName()` | `sanitizeFileName()` | ファイル名サニタイズ |
| `normalizeSpreadsheetUrl()` | `normalizeSpreadsheetUrl()` | URL正規化 |
| `extractFolderId()` | `extractFolderId()` | フォルダID抽出 |
| `extractSpreadsheetUrls()` | `extractSpreadsheetUrls()` | URL抽出 |

### ユーティリティ関数

| GAS | Local | 説明 |
|-----|-------|------|
| `Utilities.sleep()` | `sleep()` | スリープ |
| `Utilities.zip()` | `archiver` | ZIP作成 |
| `Utilities.base64Encode()` | （不要） | Base64エンコード（GASのみ） |
| `CacheService` | （不要） | ログキャッシュ（GASのみ） |
| `Logger.log()` | `console.log()` / `chalk` | ログ出力 |

## API使用の違い

### GAS版

```javascript
// SpreadsheetApp（GAS専用API）
const ss = SpreadsheetApp.openByUrl(url);
const sheets = ss.getSheets();

// DriveApp（GAS専用API）
const folder = DriveApp.getFolderById(folderId);
const files = folder.getFilesByType(MimeType.GOOGLE_SHEETS);

// Export API（UrlFetchApp）
const url = `https://docs.google.com/spreadsheets/d/${ssId}/export?format=csv&gid=${sheetId}`;
const response = UrlFetchApp.fetch(url, { headers: { 'Authorization': 'Bearer ' + token } });
```

### Local版

```typescript
// Google Sheets API（googleapis）
const sheets = google.sheets({ version: 'v4', auth });
const response = await sheets.spreadsheets.get({ spreadsheetId });

// Google Drive API（googleapis）
const drive = google.drive({ version: 'v3', auth });
const response = await drive.files.list({ q: `'${folderId}' in parents` });

// Export API（axios経由）
const url = `https://docs.google.com/spreadsheets/d/${ssId}/export?format=csv&gid=${sheetId}`;
const response = await authClient.request({ url, method: 'GET' });
```

## 移行ガイド

### GAS版からLocal版への移行手順

1. **サービスアカウント作成** → [SETUP_GUIDE.md](./SETUP_GUIDE.md) 参照
2. **フォルダ共有設定** → サービスアカウントに閲覧権限を付与
3. **コマンド実行** → 各コマンドはGAS版と同等の機能を提供

### コマンド対応表

| GAS版の操作 | Local版のコマンド |
|------------|------------------|
| 単一スプシタブ → URLを入力 → ダウンロード | `npm run export -- single "<URL>"` |
| フォルダ一括タブ → 一覧取得 → チェック → ダウンロード | `npm run export -- folder "<folderURL>"` |
| ユースケース → シート名フィルタ | `npm run export -- filter "<folderURL>" "<sheetName>"` |
| ユースケース → 複数スプシ一括 | `npm run export -- multiple urls.txt` |
| ユースケース → 進捗管理表スキャン | `npm run export -- scan "<folderURL>"` |

## 今後の拡張予定

### Local版で追加可能な機能

- ✅ **並列処理**: Node.jsの非同期処理で高速化
- ✅ **バッチモード**: インタラクティブ入力なしで全自動実行
- ✅ **進捗バー**: CLI進捗バー表示（`cli-progress`）
- ✅ **設定ファイル**: YAML/JSON形式の設定ファイル対応
- ✅ **スケジューラー**: cron連携で定期実行

### GAS版との併用

- **GAS版**: 手動操作・ブラウザから簡単にダウンロードしたい場合
- **Local版**: バッチ処理・自動化・CI/CD統合が必要な場合

---

**結論**: Local版はGAS版の全機能を実装し、さらに拡張性と自動化に優れています。
