/**
 * Spreadsheet CSV Exporter - サーバーサイド
 * スプレッドシートをCSVとしてダウンロードする汎用GAS Webアプリ
 */

/**
 * WebアプリのUIを表示
 */
function doGet() {
  return HtmlService.createHtmlOutputFromFile('Index')
    .setTitle('Spreadsheet CSV Exporter')
    .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL);
}

/**
 * ログ管理用関数（リアルタイム表示のため）
 */
function saveLog(sessionId, log) {
  const cache = CacheService.getScriptCache();
  const key = `logs_${sessionId}`;
  const existing = cache.get(key);
  const logs = existing ? JSON.parse(existing) : [];
  logs.push(log);
  // 10分間保持（600秒）
  cache.put(key, JSON.stringify(logs), 600);
}

function getLogs(sessionId) {
  const cache = CacheService.getScriptCache();
  const logsStr = cache.get(`logs_${sessionId}`);
  return logsStr ? JSON.parse(logsStr) : [];
}

function clearLogs(sessionId) {
  CacheService.getScriptCache().remove(`logs_${sessionId}`);
}

/**
 * ログ取得用（クライアントからポーリングされる）
 */
function fetchLogs(sessionId) {
  return getLogs(sessionId);
}

/**
 * 単一スプレッドシートをCSV ZIPとしてダウンロード
 * @param {string} url - スプレッドシートURL
 * @param {string} sessionId - セッションID
 * @returns {object} - { data: base64文字列, fileName: ZIP名 }
 */
function downloadSingleSpreadsheet(url, sessionId) {
  const addLog = (log) => {
    Logger.log(`[${log.type.toUpperCase()}] ${log.message}`);
    saveLog(sessionId, log);
  };

  try {
    addLog({ type: 'info', message: 'スプレッドシートにアクセス中...' });

    // URL正規化
    const normalizedUrl = normalizeSpreadsheetUrl(url);
    const ss = SpreadsheetApp.openByUrl(normalizedUrl);
    const ssName = ss.getName();
    const ssId = ss.getId();
    const sheets = ss.getSheets();

    addLog({ type: 'success', message: `アクセス成功: ${ssName}` });
    addLog({ type: 'info', message: `シート数: ${sheets.length}枚` });

    const zipFiles = [];
    const folderName = sanitizeFileName(ssName);

    // 各シートをCSVエクスポート
    sheets.forEach((sheet, index) => {
      const sheetId = sheet.getSheetId();
      const sheetName = sheet.getName();
      const fileName = `${folderName}/${sanitizeFileName(sheetName)}.csv`;

      addLog({ type: 'info', message: `シート「${sheetName}」をCSV化中...` });

      try {
        const csvBlob = fetchSheetAsCsv(ssId, sheetId, fileName);
        zipFiles.push(csvBlob);
        addLog({ type: 'success', message: `CSV生成成功: ${fileName}` });

        // レートリミット対策（最後のシート以外は500msスリープ）
        if (index < sheets.length - 1) {
          Utilities.sleep(500);
        }
      } catch (e) {
        addLog({ type: 'error', message: `CSV生成失敗: ${fileName} - ${e.message}` });
      }
    });

    if (zipFiles.length === 0) {
      throw new Error('CSVファイルが1件も生成されませんでした');
    }

    // ZIP作成
    addLog({ type: 'info', message: `ZIP作成中... (${zipFiles.length}ファイル)` });
    const zipFileName = `${folderName}.zip`;
    const zip = Utilities.zip(zipFiles, zipFileName);
    addLog({ type: 'success', message: 'ZIP作成完了！' });

    return {
      data: Utilities.base64Encode(zip.getBytes()),
      fileName: zipFileName
    };

  } catch (e) {
    addLog({ type: 'error', message: `エラー: ${e.message}` });
    return {
      data: null,
      fileName: null,
      error: e.message
    };
  }
}

/**
 * フォルダ内のスプレッドシート一覧を取得
 * @param {string} folderInput - フォルダURL または フォルダID
 * @param {string} sessionId - セッションID
 * @returns {object} - { spreadsheets: [{id, name, url}], error }
 */
function listSpreadsheetsInFolder(folderInput, sessionId) {
  const addLog = (log) => {
    Logger.log(`[${log.type.toUpperCase()}] ${log.message}`);
    saveLog(sessionId, log);
  };

  try {
    addLog({ type: 'info', message: 'フォルダにアクセス中...' });

    // フォルダID抽出
    const folderId = extractFolderId(folderInput);
    const folder = DriveApp.getFolderById(folderId);
    const folderName = folder.getName();

    addLog({ type: 'success', message: `フォルダアクセス成功: ${folderName}` });
    addLog({ type: 'info', message: 'スプレッドシートを検索中...' });

    // スプレッドシート一覧取得
    const spreadsheets = [];
    const files = folder.getFilesByType(MimeType.GOOGLE_SHEETS);

    while (files.hasNext()) {
      const file = files.next();
      spreadsheets.push({
        id: file.getId(),
        name: file.getName(),
        url: file.getUrl()
      });
    }

    addLog({ type: 'success', message: `${spreadsheets.length}件のスプレッドシートを検出` });

    if (spreadsheets.length === 0) {
      addLog({ type: 'warn', message: 'スプレッドシートが見つかりませんでした' });
    }

    return {
      spreadsheets: spreadsheets,
      error: null
    };

  } catch (e) {
    addLog({ type: 'error', message: `エラー: ${e.message}` });
    return {
      spreadsheets: [],
      error: e.message
    };
  }
}

/**
 * 指定IDのスプレッドシートをCSV ZIPとしてダウンロード
 * @param {string} ssId - スプレッドシートID
 * @param {string} sessionId - セッションID
 * @returns {object} - { data: base64文字列, fileName: ZIP名 }
 */
function downloadSpreadsheetById(ssId, sessionId) {
  const addLog = (log) => {
    Logger.log(`[${log.type.toUpperCase()}] ${log.message}`);
    saveLog(sessionId, log);
  };

  try {
    addLog({ type: 'info', message: 'スプレッドシートにアクセス中...' });

    const ss = SpreadsheetApp.openById(ssId);
    const ssName = ss.getName();
    const sheets = ss.getSheets();

    addLog({ type: 'success', message: `アクセス成功: ${ssName}` });
    addLog({ type: 'info', message: `シート数: ${sheets.length}枚` });

    const zipFiles = [];
    const folderName = sanitizeFileName(ssName);

    // 各シートをCSVエクスポート
    sheets.forEach((sheet, index) => {
      const sheetId = sheet.getSheetId();
      const sheetName = sheet.getName();
      const fileName = `${folderName}/${sanitizeFileName(sheetName)}.csv`;

      addLog({ type: 'info', message: `  シート「${sheetName}」をCSV化中...` });

      try {
        const csvBlob = fetchSheetAsCsv(ssId, sheetId, fileName);
        zipFiles.push(csvBlob);
        addLog({ type: 'success', message: `  CSV生成成功: ${fileName}` });

        // レートリミット対策（最後のシート以外は500msスリープ）
        if (index < sheets.length - 1) {
          Utilities.sleep(500);
        }
      } catch (e) {
        addLog({ type: 'error', message: `  CSV生成失敗: ${fileName} - ${e.message}` });
      }
    });

    if (zipFiles.length === 0) {
      throw new Error('CSVファイルが1件も生成されませんでした');
    }

    // ZIP作成
    addLog({ type: 'info', message: `ZIP作成完了: ${zipFiles.length}ファイル` });
    const zipFileName = `${folderName}.zip`;
    const zip = Utilities.zip(zipFiles, zipFileName);

    return {
      data: Utilities.base64Encode(zip.getBytes()),
      fileName: zipFileName
    };

  } catch (e) {
    addLog({ type: 'error', message: `エラー: ${e.message}` });
    return {
      data: null,
      fileName: null,
      error: e.message
    };
  }
}

/**
 * ファイル名をサニタイズ（ZIPで使えない文字を置換）
 */
function sanitizeFileName(name) {
  return name.replace(/[\\/:*?"<>|]/g, '_').trim();
}

/**
 * スプレッドシートURLを正規化
 */
function normalizeSpreadsheetUrl(url) {
  // 不可視文字を削除
  let cleaned = url.replace(/[\u200B-\u200D\uFEFF\u00A0]/g, '').trim();

  // IDだけのURL（/edit がない）を完全な形式に正規化
  const idMatch = cleaned.match(/\/spreadsheets\/d\/([a-zA-Z0-9_-]+)/);
  if (idMatch) {
    return `https://docs.google.com/spreadsheets/d/${idMatch[1]}/edit`;
  }
  return cleaned;
}

/**
 * フォルダID抽出
 * @param {string} input - フォルダURL または ID
 * @returns {string} - フォルダID
 */
function extractFolderId(input) {
  // 既にIDの場合はそのまま返す
  if (!input.includes('/')) {
    return input.trim();
  }

  // URLからID抽出
  const match = input.match(/\/folders\/([a-zA-Z0-9_-]+)/);
  if (match) {
    return match[1];
  }

  // 抽出できない場合はそのまま返す（エラーは呼び出し元で処理）
  return input.trim();
}

/**
 * 指定したシートをCSVでエクスポート（リトライ付き）
 * @param {string} ssId - スプレッドシートID
 * @param {number} sheetId - シートID（gid）
 * @param {string} fileName - 出力ファイル名
 * @returns {Blob} - CSVファイルBlob
 */
function fetchSheetAsCsv(ssId, sheetId, fileName) {
  const url = `https://docs.google.com/spreadsheets/d/${ssId}/export?format=csv&gid=${sheetId}`;
  const token = ScriptApp.getOAuthToken();

  // リトライロジック（最大3回）
  for (let retry = 0; retry < 3; retry++) {
    const response = UrlFetchApp.fetch(url, {
      headers: { 'Authorization': 'Bearer ' + token },
      muteHttpExceptions: true
    });

    const code = response.getResponseCode();

    // 成功
    if (code === 200) {
      return response.getBlob().setName(fileName);
    }

    // レートリミット（429）またはサーバーエラー（5xx）の場合は待機してリトライ
    if (code === 429 || code >= 500) {
      const waitMs = 3000 * (retry + 1); // 3秒、6秒、9秒と増加
      Logger.log(`[リトライ] ${fileName}: HTTPコード${code}、${waitMs}ms待機後に再試行...`);
      Utilities.sleep(waitMs);
      continue;
    }

    // その他のエラーはそのまま返す
    Logger.log(`[エラー] ${fileName}: HTTPコード${code}`);
    return response.getBlob().setName(fileName);
  }

  // リトライ上限到達時
  throw new Error(`シート取得に失敗しました（リトライ上限）: ${fileName}`);
}

/**
 * フォルダ内のスプレッドシートから指定シート名のみをCSVダウンロード
 * @param {string} folderInput - フォルダURL または ID
 * @param {string} targetSheetName - フィルタするシート名（完全一致）
 * @param {string} sessionId - セッションID
 * @returns {object} - { data: base64文字列, fileName: ZIP名, matchCount: 件数 }
 */
function downloadFilteredSheets(folderInput, targetSheetName, sessionId) {
  const addLog = (log) => {
    Logger.log(`[${log.type.toUpperCase()}] ${log.message}`);
    saveLog(sessionId, log);
  };

  try {
    addLog({ type: 'info', message: 'フォルダにアクセス中...' });

    // フォルダID抽出
    const folderId = extractFolderId(folderInput);
    const folder = DriveApp.getFolderById(folderId);
    const folderName = folder.getName();

    addLog({ type: 'success', message: `フォルダアクセス成功: ${folderName}` });
    addLog({ type: 'info', message: 'スプレッドシートを検索中...' });

    // スプレッドシート一覧取得
    const files = folder.getFilesByType(MimeType.GOOGLE_SHEETS);
    const spreadsheets = [];
    while (files.hasNext()) {
      spreadsheets.push(files.next());
    }

    addLog({ type: 'info', message: `${spreadsheets.length}件のスプレッドシートを検出` });
    addLog({ type: 'info', message: `シート名「${targetSheetName}」でフィルタ中...` });

    const zipFiles = [];
    let matchCount = 0;

    // 各スプレッドシートを処理
    spreadsheets.forEach((file, ssIndex) => {
      const ssId = file.getId();
      const ssName = file.getName();

      try {
        const ss = SpreadsheetApp.openById(ssId);
        const sheets = ss.getSheets();

        // シート名でフィルタ（完全一致）
        const matchingSheets = sheets.filter(sheet => sheet.getName() === targetSheetName);

        if (matchingSheets.length > 0) {
          matchingSheets.forEach(sheet => {
            const sheetId = sheet.getSheetId();
            const fileName = `${sanitizeFileName(ssName)}_${sanitizeFileName(targetSheetName)}.csv`;

            addLog({ type: 'info', message: `CSV化中: ${ssName} / ${targetSheetName}` });

            const csvBlob = fetchSheetAsCsv(ssId, sheetId, fileName);
            zipFiles.push(csvBlob);
            matchCount++;

            addLog({ type: 'success', message: `CSV生成成功: ${fileName}` });
          });
        }

        // レートリミット対策（最後以外は500msスリープ）
        if (ssIndex < spreadsheets.length - 1) {
          Utilities.sleep(500);
        }

      } catch (e) {
        addLog({ type: 'warn', message: `スキップ: ${ssName} - ${e.message}` });
      }
    });

    if (matchCount === 0) {
      addLog({ type: 'warn', message: `シート「${targetSheetName}」が見つかりませんでした` });
      return {
        data: null,
        fileName: null,
        matchCount: 0,
        message: `シート「${targetSheetName}」が見つかりませんでした`
      };
    }

    // ZIP作成
    addLog({ type: 'info', message: `ZIP作成中... (${zipFiles.length}ファイル)` });
    const zipFileName = `${sanitizeFileName(targetSheetName)}_filtered.zip`;
    const zip = Utilities.zip(zipFiles, zipFileName);
    addLog({ type: 'success', message: `完了！${matchCount}件のシートをダウンロード` });

    return {
      data: Utilities.base64Encode(zip.getBytes()),
      fileName: zipFileName,
      matchCount: matchCount,
      message: `${matchCount}件のシートをダウンロードしました`
    };

  } catch (e) {
    addLog({ type: 'error', message: `エラー: ${e.message}` });
    return {
      data: null,
      fileName: null,
      matchCount: 0,
      error: e.message
    };
  }
}
