# GAS リミット制限の原因と条件

このドキュメントでは、Spreadsheet CSV Exporter の GAS 実装で発生する可能性のあるリミット制限について整理します。

---

## 📋 目次

1. [実行時間制限（6分）](#1-実行時間制限6分)
2. [UrlFetch レート制限（HTTP 429）](#2-urlfetch-レート制限http-429)
3. [SpreadsheetApp/DriveApp 呼び出し制限](#3-spreadsheetappdriveapp-呼び出し制限)
4. [メモリ制限](#4-メモリ制限)
5. [CacheService 制限](#5-cacheservice-制限)
6. [リミットに引っかかりやすいシナリオまとめ](#-リミットに引っかかりやすいシナリオまとめ)
7. [推奨される回避策](#-推奨される回避策)

---

## 1. 実行時間制限（6分）

### ⚠️ 最も引っかかりやすいリミット

### 原因箇所

- `downloadMultipleSpreadsheets()` - 複数スプシ一括処理
- `scanProgressSheetsForUrls()` - 進捗管理表スキャン
- `downloadFilteredSheets()` - シート名フィルタ一括

### 引っかかる条件

```
処理時間 = (スプシ数 × スリープ時間) + (シート数 × スリープ時間) + API呼び出し時間
```

#### 具体例

- **100シート**の場合：`100 × 500ms = 50秒 + API時間` → **ギリギリセーフ**
- **200シート**の場合：`200 × 500ms = 100秒 + API時間` → **危険域**
- **進捗管理表スキャン**で50スプシ：`50 × 300ms + 各スプシのデータ取得` → **3-4分**

### 該当コード

| 場所 | 内容 |
|------|------|
| `Code.js:127-129` | シート間500msスリープ |
| `Code.js:269-272` | シート間500msスリープ |
| `Code.js:545-548` | シート間300msスリープ |
| `Code.js:712-715` | スプシ間300msスリープ |

---

## 2. UrlFetch レート制限（HTTP 429）

### 原因箇所

- `fetchSheetAsCsv()` - CSV エクスポートAPI呼び出し (`Code.js:352-385`)

```javascript
const response = UrlFetchApp.fetch(url, {
  headers: { 'Authorization': 'Bearer ' + token },
  muteHttpExceptions: true
});
```

### 引っかかる条件

- **短時間に大量のシート**をエクスポートする場合
- Google APIのレート制限（明確な数値は非公開だが、**1秒間に数回程度**と推測）
- 500msスリープでは不十分な場合がある

### 対策済み箇所

`Code.js:357-376` にリトライロジックを実装済み：
- **最大3回リトライ**
- 待機時間：3秒 → 6秒 → 9秒と段階的に増加
- 429エラーを検出して自動リトライ

```javascript
if (code === 429 || code >= 500) {
  const waitMs = 3000 * (retry + 1); // 3秒、6秒、9秒と増加
  Utilities.sleep(waitMs);
  continue;
}
```

### 引っかかる例

- **100シートを連続処理** → 500msスリープでも頻度が高すぎてレート制限に到達
- リトライ処理により救済されるが、処理時間が大幅に増加（実行時間制限のリスク増）

---

## 3. SpreadsheetApp/DriveApp 呼び出し制限

### 原因箇所

`scanProgressSheetsForUrls()` - `Code.js:691-695`

```javascript
const targetSs = SpreadsheetApp.openById(ssIdFromUrl);
```

### 引っかかる条件

- 進捗管理表内に**大量のスプシURL**（50個以上）がある場合
- 各URLごとに`openById()`を呼び出すため、APIクォータを消費
- **1日あたりの呼び出し上限**に到達する可能性

### GASの呼び出し制限（参考値）

| サービス | 無料アカウント | Workspace アカウント |
|---------|--------------|---------------------|
| `SpreadsheetApp` 呼び出し | 制限あり | より緩い制限 |
| 1日あたりの実行時間 | 90分 | 6時間 |

### 引っかかる例

- 進捗管理表に**100個のスプシURL**がある場合
- `100回 × SpreadsheetApp.openById()` → **クォータ消費大**
- 同じスクリプトを1日に何度も実行すると上限到達

---

## 4. メモリ制限

### 原因箇所

`Utilities.zip()` - `Code.js:151, 285, 476, 580`

```javascript
const zip = Utilities.zip(zipFiles, zipFileName);
return {
  data: Utilities.base64Encode(zip.getBytes()),
  fileName: zipFileName
};
```

### 引っかかる条件

- 大量のCSVファイルを1つのZIPにまとめる場合
- 特に`downloadMultipleSpreadsheets()`で全CSVを1つのZIPに結合
- 各CSVが大きい（数MB）場合、合計サイズが数十MB → **メモリ不足エラー**

### GASのメモリ制限

- **最大メモリ**: 約100MB程度（非公開）
- Base64エンコードで**約1.33倍**にサイズ増加

### 引っかかる例

- `50スプシ × 10シート × 1MB/シート = 500MB` のデータをZIP化 → **確実に失敗**
- **目安**: 合計10-20MB程度までが安全

### 回避策

現在の実装は**個別ZIP方式**（フォルダ一括モード）を採用しているため、このリスクは低い。

---

## 5. CacheService 制限

### 原因箇所

ログ保存 - `Code.js:18-26`

```javascript
function saveLog(sessionId, log) {
  const cache = CacheService.getScriptCache();
  const key = `logs_${sessionId}`;
  const existing = cache.get(key);
  const logs = existing ? JSON.parse(existing) : [];
  logs.push(log);
  cache.put(key, JSON.stringify(logs), 600); // 10分間保持
}
```

### 引っかかる条件

**大量のログ**を保存する場合

### CacheServiceの制限

| 制限項目 | 上限 |
|---------|------|
| 1キーあたりの最大サイズ | **100KB** |
| スクリプト全体のキャッシュ | **10MB** |

### 引っかかる例

- **1000シート**を処理して各シートでログを3件出力 → 3000件のログ
- JSON文字列が100KBを超える → **キャッシュ保存失敗**
- エラーは出ないが、**ログが途中から表示されなくなる**

### 回避策

- ログの詳細度を調整
- 一定件数を超えたら古いログを削除（FIFO方式）

---

## 🎯 リミットに引っかかりやすいシナリオまとめ

| 機能 | リスク | 引っかかる条件 | 主なリミット |
|------|--------|---------------|-------------|
| **単一スプシ** | 🟡 中 | シート数が200枚以上 | 実行時間、レート制限 |
| **フォルダ一括** | 🟡 中 | 選択したスプシ数×シート数が200以上 | 実行時間、レート制限 |
| **複数スプシ一括** | 🔴 高 | スプシ数が30個以上、またはZIPサイズが20MB以上 | 実行時間、メモリ |
| **進捗管理表スキャン** | 🔴 高 | フォルダ内スプシ数が50個以上、または検出URLが100個以上 | 実行時間、API呼び出し制限 |
| **シート名フィルタ** | 🟡 中 | フォルダ内スプシ数が100個以上 | 実行時間、レート制限 |

---

## 💡 推奨される回避策

### 1. 処理を分割する

**最も効果的な対策**

- 一度に処理するスプシ数を減らす（**10-20個ずつ**）
- クライアント側で順次処理（既に実装済み）
- 中断機能を活用して分割実行

### 2. スリープ時間を増やす

**レート制限対策**

現在の設定：
```javascript
Utilities.sleep(500); // シート間
Utilities.sleep(300); // スプシ間
```

推奨設定（レート制限が発生する場合）：
```javascript
Utilities.sleep(1000); // シート間（500ms → 1000ms）
Utilities.sleep(500);  // スプシ間（300ms → 500ms）
```

**トレードオフ**: 処理時間が増加するため、実行時間制限に到達しやすくなる

### 3. 中断機能を活用

既に実装済み（`Code.js:103-107`）

```javascript
if (checkAbortFlag(sessionId)) {
  addLog({ type: 'warn', message: '処理が中断されました' });
  aborted = true;
  break;
}
```

**使い方**:
- 処理が6分に近づいたら手動中断
- 残りのスプシを再度選択して実行

### 4. ログを削減

**CacheService制限対策**

- 詳細ログを減らす（特に大量シート処理時）
- ログの最大保持件数を設定（例: 500件まで）

### 5. バッチサイズの調整

**推奨値**:

| 機能 | 推奨バッチサイズ |
|------|----------------|
| 単一スプシ | 100シートまで |
| フォルダ一括 | 10スプシまで（1回あたり） |
| 複数スプシ一括 | 20スプシまで |
| 進捗管理表スキャン | フォルダ内30スプシまで |

---

## 🔧 設定変更箇所（カスタマイズ用）

### スリープ時間の調整

**Code.js 該当箇所**:

```javascript
// 単一スプシ: Code.js:127-129
if (index < sheets.length - 1) {
  Utilities.sleep(500); // ← この値を調整（推奨: 1000）
}

// フォルダ一括: Code.js:269-272
if (index < sheets.length - 1) {
  Utilities.sleep(500); // ← この値を調整（推奨: 1000）
}

// 複数スプシ一括: Code.js:545-548
if (sheetIndex < sheets.length - 1) {
  Utilities.sleep(300); // ← この値を調整（推奨: 500）
}

// 進捗管理表スキャン: Code.js:712-715
if (index < spreadsheets.length - 1) {
  Utilities.sleep(300); // ← この値を調整（推奨: 500）
}
```

### リトライ設定の調整

**Code.js:357-376**:

```javascript
// 現在: 最大3回リトライ、3秒/6秒/9秒待機
for (let retry = 0; retry < 3; retry++) {
  // ...
  if (code === 429 || code >= 500) {
    const waitMs = 3000 * (retry + 1); // ← 待機時間の倍率を調整
    Utilities.sleep(waitMs);
    continue;
  }
}

// より慎重な設定（推奨: レート制限が頻発する場合）
for (let retry = 0; retry < 5; retry++) { // リトライ回数を増加
  // ...
  if (code === 429 || code >= 500) {
    const waitMs = 5000 * (retry + 1); // 5秒/10秒/15秒/20秒/25秒
    Utilities.sleep(waitMs);
    continue;
  }
}
```

---

## 📊 まとめ

このGAS実装では、以下の2つが**最も引っかかりやすいリミット**です：

1. **実行時間制限（6分）** - 大量シート/スプシ処理時
2. **レート制限（HTTP 429）** - 短時間の連続API呼び出し

**対策の優先順位**:

1. 🥇 **処理を分割**（10-20個ずつ実行）
2. 🥈 **中断機能を活用**（6分以内に手動中断して分割）
3. 🥉 **スリープ時間を調整**（レート制限が発生する場合のみ）

適切なバッチサイズと中断機能の活用により、安定した運用が可能です。

---

**作成日**: 2026-01-22
**対象バージョン**: Spreadsheet CSV Exporter v1.0
