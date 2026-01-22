# GAS Spreadsheet CSV Exporter パフォーマンス改善計画書

## 概要

このドキュメントでは、Spreadsheet CSV ExporterのGAS実装におけるパフォーマンス改善の計画をまとめます。

---

## 現状の問題点

### 1. 順次処理によるボトルネック

現在の実装では、各シートを**1件ずつ順次処理**し、シート間に**500msのスリープ**を入れています。

```javascript
// 現状: downloadSingleSpreadsheet() - Code.js:97-133
for (let index = 0; index < sheets.length; index++) {
  const csvBlob = fetchSheetAsCsv(ssId, sheetId, fileName);  // 1件ずつ取得
  Utilities.sleep(500);  // 毎回500ms待機
}
```

**問題**: 10シートで約5秒、100シートで約50秒のスリープ時間が発生

### 2. scanProgressSheetsForUrlsの重複API呼び出し

URLを見つけるたびに`SpreadsheetApp.openById()`で名前を取得しています。

```javascript
// 現状: Code.js:691-699
const targetSs = SpreadsheetApp.openById(ssIdFromUrl);  // 重いAPI呼び出し
urlMap[ssIdFromUrl] = {
  name: targetSs.getName(),  // 名前取得のためだけに呼び出し
};
```

**問題**: URLが100個あれば100回のAPI呼び出しが発生

### 3. 6分制限への対策不足

GASの実行時間制限（6分）を超える大量処理に対応できません。

---

## 改善計画

### Phase 1: 並列化による高速化（効果: 5-10倍）

#### 概要

`UrlFetchApp.fetchAll()` を使用して複数シートのCSVエクスポートを**並列実行**します。

#### 新規関数: `fetchSheetsAsCsvBatch()`

```javascript
/**
 * 複数シートを並列でCSVエクスポート
 * @param {Array} requests - [{ssId, sheetId, fileName}, ...]
 * @param {string} token - OAuthトークン（事前取得）
 * @returns {Array} - [{blob, fileName, success, error}, ...]
 */
function fetchSheetsAsCsvBatch(requests, token) {
  // リクエスト構築
  const fetchRequests = requests.map(req => ({
    url: `https://docs.google.com/spreadsheets/d/${req.ssId}/export?format=csv&gid=${req.sheetId}`,
    headers: { 'Authorization': 'Bearer ' + token },
    muteHttpExceptions: true
  }));

  // 並列取得（GASが内部で並列化）
  const responses = UrlFetchApp.fetchAll(fetchRequests);

  // レスポンス処理
  return responses.map((response, index) => {
    const req = requests[index];
    const code = response.getResponseCode();

    if (code === 200) {
      return {
        blob: response.getBlob().setName(req.fileName),
        fileName: req.fileName,
        success: true
      };
    }
    return {
      blob: null,
      fileName: req.fileName,
      success: false,
      error: `HTTP ${code}`
    };
  });
}
```

#### 改善後のdownloadSingleSpreadsheet()

```javascript
function downloadSingleSpreadsheet(url, sessionId) {
  // ... 前処理 ...

  const token = ScriptApp.getOAuthToken();  // 1回だけ取得
  const sheets = ss.getSheets().filter(s => !s.isSheetHidden());

  // リクエスト構築
  const requests = sheets.map(sheet => ({
    ssId: ssId,
    sheetId: sheet.getSheetId(),
    fileName: `${folderName}/${sanitizeFileName(sheet.getName())}.csv`
  }));

  addLog({ type: 'info', message: `${sheets.length}シートを並列取得中...` });

  // 並列取得
  const results = fetchSheetsAsCsvBatch(requests, token);

  // 成功したBlobのみ収集
  const zipFiles = results
    .filter(r => r.success)
    .map(r => r.blob);

  // ZIP作成
  const zip = Utilities.zip(zipFiles, `${folderName}.zip`);

  // ... 後処理 ...
}
```

#### 変更対象関数

| 関数 | 変更内容 |
|------|---------|
| `downloadSingleSpreadsheet()` | 並列処理対応、500msスリープ削除 |
| `downloadSpreadsheetById()` | 並列処理対応、500msスリープ削除 |
| `downloadMultipleSpreadsheets()` | バッチ処理対応、スリープ削減 |
| `downloadFilteredSheets()` | バッチ処理対応 |

#### 期待効果

| シート数 | 現状 | 改善後 | 高速化率 |
|---------|------|--------|---------|
| 10シート | 約5秒 | 約0.5秒 | 10倍 |
| 50シート | 約25秒 | 約2-3秒 | 8-10倍 |
| 100シート | 約50秒 | 約5秒 | 10倍 |

#### 注意点

- `fetchAll()` にもレート制限あり（バッチサイズ10-20件推奨）
- 失敗したリクエストの個別リトライが必要

---

### Phase 2: API効率化

#### 2.1 scanProgressSheetsForUrls() の最適化

`SpreadsheetApp.openById()` は**シート情報も含めて読み込む重いAPI**です。
名前取得のみの場合は、`DriveApp.getFileById()` の方が軽量です。

```javascript
// 改善前（重い）
const targetSs = SpreadsheetApp.openById(ssIdFromUrl);
const name = targetSs.getName();

// 改善後（軽量）
const file = DriveApp.getFileById(ssIdFromUrl);
const name = file.getName();
```

#### 2.2 OAuthトークンの事前取得

現在は`fetchSheetAsCsv()`内で毎回トークンを取得していますが、
並列化（Phase 1）では呼び出し元で1回取得して引数で渡します。

```javascript
// 改善前: fetchSheetAsCsv() 内で毎回取得
const token = ScriptApp.getOAuthToken();

// 改善後: 呼び出し元で1回取得、引数で渡す
const token = ScriptApp.getOAuthToken();
const results = fetchSheetsAsCsvBatch(requests, token);
```

#### 期待効果

- API呼び出し回数の削減により処理時間20-30%短縮
- APIクォータ消費の削減

---

### Phase 3: 継続実行パターン（6分制限対策）

#### 重要: GASの6分制限について

**6分制限は絶対的な制限であり、1回の実行で超えることは不可能です。**

継続実行パターンは、**複数回の実行を繋げて擬似的に長時間処理を実現**する手法です。

```
【動作イメージ】
1回目の実行（0〜5分）
  ↓ 5分経過を検知
  ↓ 状態をPropertiesServiceに保存
  ↓ "paused" を返却して終了（6分制限内）

クライアント側で1秒待機

2回目の実行（新しい実行として開始）
  ↓ 保存した状態を読み込み
  ↓ 続きから処理再開
  ↓ 5分経過したら再度保存して終了

...繰り返し...

最終回の実行
  ↓ 全て完了
  ↓ "completed" を返却
```

#### 新規関数（サーバー側）

##### `initializeBatchJob()` - ジョブ初期化

```javascript
function initializeBatchJob(jobId, targets) {
  const props = PropertiesService.getScriptProperties();
  const job = {
    id: jobId,
    status: 'pending',
    targets: targets,          // 処理対象リスト
    currentIndex: 0,           // 現在のインデックス
    completedKeys: [],         // 完了ファイルのキャッシュキー
    errors: [],                // エラー情報
    startTime: Date.now(),
    lastUpdated: Date.now()
  };
  props.setProperty(`job_${jobId}`, JSON.stringify(job));
  return { jobId: jobId, total: targets.length };
}
```

##### `processNextBatch()` - バッチ継続処理

```javascript
function processNextBatch(jobId, sessionId) {
  const props = PropertiesService.getScriptProperties();
  const jobStr = props.getProperty(`job_${jobId}`);
  if (!jobStr) return { error: 'Job not found' };

  const job = JSON.parse(jobStr);
  const BATCH_SIZE = 10;
  const TIME_LIMIT_MS = 5 * 60 * 1000; // 5分（1分のバッファ）
  const startTime = Date.now();

  while (job.currentIndex < job.targets.length) {
    // 時間チェック（5分経過したら一時停止）
    if (Date.now() - startTime > TIME_LIMIT_MS) {
      job.status = 'paused';
      job.lastUpdated = Date.now();
      props.setProperty(`job_${jobId}`, JSON.stringify(job));
      return {
        status: 'paused',
        progress: job.currentIndex,
        total: job.targets.length
      };
    }

    // 中断フラグチェック
    if (checkAbortFlag(sessionId)) {
      job.status = 'aborted';
      props.setProperty(`job_${jobId}`, JSON.stringify(job));
      return { status: 'aborted', progress: job.currentIndex };
    }

    // バッチ処理
    const batch = job.targets.slice(job.currentIndex, job.currentIndex + BATCH_SIZE);
    const results = processBatchItems(batch, sessionId);

    // 結果をCacheServiceに保存（PropertiesServiceの9KB制限回避）
    results.forEach(r => {
      if (r.success) {
        const key = `file_${jobId}_${job.currentIndex}`;
        CacheService.getScriptCache().put(key, JSON.stringify(r), 600);
        job.completedKeys.push(key);
      } else {
        job.errors.push(r.error);
      }
    });

    job.currentIndex += batch.length;
    job.lastUpdated = Date.now();
    props.setProperty(`job_${jobId}`, JSON.stringify(job));
  }

  job.status = 'completed';
  props.setProperty(`job_${jobId}`, JSON.stringify(job));
  return { status: 'completed', progress: job.currentIndex, total: job.targets.length };
}
```

##### `getJobStatus()` - 状態取得

```javascript
function getJobStatus(jobId) {
  const props = PropertiesService.getScriptProperties();
  const jobStr = props.getProperty(`job_${jobId}`);
  if (!jobStr) return null;

  const job = JSON.parse(jobStr);
  return {
    status: job.status,
    progress: job.currentIndex,
    total: job.targets.length,
    errors: job.errors.length
  };
}
```

##### `finalizeJob()` - 完了処理

```javascript
function finalizeJob(jobId) {
  const props = PropertiesService.getScriptProperties();
  const cache = CacheService.getScriptCache();
  const jobStr = props.getProperty(`job_${jobId}`);
  if (!jobStr) return { error: 'Job not found' };

  const job = JSON.parse(jobStr);

  if (job.status !== 'completed') {
    return { error: 'Job not completed', status: job.status };
  }

  // キャッシュから完了ファイルを収集
  const blobs = [];
  job.completedKeys.forEach(key => {
    const dataStr = cache.get(key);
    if (dataStr) {
      const data = JSON.parse(dataStr);
      blobs.push(Utilities.newBlob(
        Utilities.base64Decode(data.content),
        'text/csv',
        data.fileName
      ));
    }
  });

  // ZIP作成
  const zipFileName = `batch_${jobId}.zip`;
  const zip = Utilities.zip(blobs, zipFileName);

  // クリーンアップ
  props.deleteProperty(`job_${jobId}`);
  job.completedKeys.forEach(key => cache.remove(key));

  return {
    data: Utilities.base64Encode(zip.getBytes()),
    fileName: zipFileName,
    fileCount: blobs.length,
    errors: job.errors
  };
}
```

#### Index.html の変更点

##### 継続実行対応のJavaScript

```javascript
let currentJobId = null;

// バッチジョブ開始
function startBatchJob(targets) {
  currentJobId = 'job_' + Date.now();

  google.script.run
    .withSuccessHandler(result => {
      showStatus(`ジョブ開始: ${result.total}件`, 'info');
      processJob();
    })
    .withFailureHandler(onError)
    .initializeBatchJob(currentJobId, targets);
}

// ジョブ処理（再帰的に継続）
function processJob() {
  google.script.run
    .withSuccessHandler(onBatchResult)
    .withFailureHandler(onError)
    .processNextBatch(currentJobId, sessionId);
}

// バッチ結果コールバック
function onBatchResult(result) {
  updateProgress(result.progress, result.total);

  if (result.status === 'paused') {
    // 一時停止 → 1秒後に自動再開
    showStatus(`処理中... (${result.progress}/${result.total})`, 'info');
    setTimeout(processJob, 1000);
  } else if (result.status === 'completed') {
    // 完了 → ZIPダウンロード
    showStatus('ZIP作成中...', 'info');
    finalizeAndDownload();
  } else if (result.status === 'aborted') {
    showStatus('処理が中断されました', 'warn');
  }
}

// 最終処理
function finalizeAndDownload() {
  google.script.run
    .withSuccessHandler(result => {
      if (result.data) {
        downloadZip(result);
        showStatus(`完了！ ${result.fileCount}ファイル`, 'success');
      } else {
        showStatus('エラー: ' + result.error, 'error');
      }
    })
    .withFailureHandler(onError)
    .finalizeJob(currentJobId);
}

// 進捗更新
function updateProgress(current, total) {
  const percent = Math.round((current / total) * 100);
  document.getElementById('progress-bar').style.width = percent + '%';
  document.getElementById('progress-percent').innerText = percent + '%';
  document.getElementById('progress-text').innerText = `${current}/${total}件処理済み`;
}
```

##### 進捗バーHTML

```html
<div id="progress-container" style="display: none; margin-top: 20px;">
  <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
    <span id="progress-text">処理中...</span>
    <span id="progress-percent">0%</span>
  </div>
  <div style="width: 100%; height: 20px; background: #e0e0e0; border-radius: 10px; overflow: hidden;">
    <div id="progress-bar" style="width: 0%; height: 100%; background: #4285f4; transition: width 0.3s;"></div>
  </div>
</div>
```

#### 注意点・制限事項

| 制限 | 内容 | 対策 |
|------|------|------|
| PropertiesService | 1プロパティ9KB | ファイル内容はCacheServiceに保存 |
| CacheService | 1キー100KB、10分間 | ファイルごとに分割保存 |
| トリガー | 時間ベーストリガーも6分制限 | クライアント再呼び出し方式を採用 |

---

## 実装優先度

| 優先度 | Phase | 内容 | 効果 | 複雑さ |
|--------|-------|------|------|--------|
| 🔴 高 | 1 | 並列化（fetchAll） | 5-10倍高速化 | 中 |
| 🟡 中 | 2 | API効率化（DriveApp） | 20-30%改善 | 低 |
| 🟢 低 | 3 | 継続実行パターン | 6分超え対応 | 高 |

**推奨**: Phase 1-2 のみ実装でも大幅な改善が見込めます。
Phase 3 は、Phase 1-2 適用後もなお6分制限に引っかかる場合に検討してください。

---

## テスト方法

### Phase 1 テスト

```javascript
function testBatchFetch() {
  const testSsId = 'テストスプレッドシートID';
  const token = ScriptApp.getOAuthToken();

  const requests = [
    { ssId: testSsId, sheetId: 0, fileName: 'sheet1.csv' },
    { ssId: testSsId, sheetId: 123456, fileName: 'sheet2.csv' }
  ];

  console.time('batch');
  const results = fetchSheetsAsCsvBatch(requests, token);
  console.timeEnd('batch');

  results.forEach(r => Logger.log(`${r.fileName}: ${r.success ? 'OK' : r.error}`));
}
```

### パフォーマンス比較テスト

```javascript
function benchmarkComparison() {
  const testUrl = 'テストスプレッドシートURL';

  // 改善前
  console.time('legacy');
  const result1 = downloadSingleSpreadsheetLegacy(testUrl, 'bench1');
  console.timeEnd('legacy');

  // 改善後
  console.time('optimized');
  const result2 = downloadSingleSpreadsheet(testUrl, 'bench2');
  console.timeEnd('optimized');
}
```

---

## まとめ

1. **Phase 1（並列化）** が最も効果が高く、実装もそこまで複雑ではない
2. **Phase 2（API効率化）** は簡単に実装でき、追加の改善が見込める
3. **Phase 3（継続実行）** は複雑だが、大量処理が必要な場合は有効

Phase 1-2 を適用することで、現状の5-10倍のシート数を6分以内に処理可能になります。

---

**作成日**: 2026-01-22
**対象**: Spreadsheet CSV Exporter
