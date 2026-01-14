/**
 * WebアプリのUIを表示
 */
function doGet() {
  return HtmlService.createHtmlOutputFromFile('Index')
    .setTitle('設計書HTML一括ダウンローダー')
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
 * メイン処理：URLから設計書を探索しZIPを作成
 */
function processSpreadsheets(listSheetUrl, sessionId, format = 'html') {
  const stats = { detailSheetsTotal: 0, detailSheetsSuccess: 0, designDocsTotal: 0, filesGenerated: 0, errors: 0 };

  // ヘルパー関数：ログを保存
  const addLog = (log) => {
    saveLog(sessionId, log);
  };

  try {
    const zipFiles = [];
    const processedUrls = new Set(); // 重複処理防止

    addLog({ type: 'info', message: '一覧シートからURLを取得中...' });

    // 1. 一覧シートから詳細シートURLを取得（B列のハイパーリンクから）
    const detailResult = getUrlsFromSheet(listSheetUrl, '総合進捗管理', 2);  // B列(2)のハイパーリンク
    const detailUrls = detailResult.urls;
    const detailDebug = detailResult.debug;

    // デバッグ情報を表示
    addLog({ type: 'info', message: `スプレッドシート: ${detailDebug.spreadsheetName}` });
    addLog({ type: 'info', message: `要求シート: ${detailDebug.requestedSheetName}` });
    addLog({ type: 'info', message: `実際のシート: ${detailDebug.actualSheetName}` });
    addLog({ type: 'info', message: `対象列: ${detailDebug.targetColumn}` });
    addLog({ type: 'info', message: `抽出方法: ${detailDebug.extractionMethod}` });
    addLog({ type: 'info', message: `利用可能なシート: ${detailDebug.availableSheets.join(', ')}` });

    if (detailDebug.error) {
      addLog({ type: 'error', message: `エラー: ${detailDebug.error}` });
    }

    stats.detailSheetsTotal = detailUrls.length;
    addLog({ type: 'info', message: `詳細シートを${detailUrls.length}件検出しました` });

    // デバッグ: 取得したURL一覧を表示
    detailUrls.forEach((url, index) => {
      addLog({ type: 'info', message: `  [${index + 1}] 検出URL`, url: url });
    });

    // DEBUG_LIMIT: スクリプトプロパティから取得（未設定なら全件処理）
    const props = PropertiesService.getScriptProperties();
    const debugLimitStr = props.getProperty('DEBUG_LIMIT');
    const DEBUG_LIMIT = debugLimitStr ? parseInt(debugLimitStr, 10) : detailUrls.length;
    const urlsToProcess = detailUrls.slice(0, DEBUG_LIMIT);

    if (DEBUG_LIMIT < detailUrls.length) {
      addLog({ type: 'warn', message: `⚠️ デバッグモード：最初の${DEBUG_LIMIT}件のみ処理します（全${detailUrls.length}件中）` });
    } else {
      addLog({ type: 'info', message: `全${detailUrls.length}件を処理します` });
    }

    urlsToProcess.forEach((detailUrl, index) => {
      // URLをtrim
      const trimmedDetailUrl = detailUrl.trim();

      addLog({ type: 'info', message: `\n--- [${index + 1}/${urlsToProcess.length}] 詳細シート処理開始 ---` });
      addLog({ type: 'info', message: `処理対象URL`, url: trimmedDetailUrl });

      // URL検証
      if (!trimmedDetailUrl || !trimmedDetailUrl.startsWith('https://docs.google.com/spreadsheets/')) {
        addLog({ type: 'warn', message: `URL検証失敗: 無効な形式`, url: trimmedDetailUrl });
        stats.errors++;
        return;
      }
      addLog({ type: 'info', message: `URL検証: OK` });

      // 2. 詳細シートから「進捗管理表」シートを特定
      addLog({ type: 'info', message: `スプレッドシートにアクセス中...` });
      try {
        const ss = SpreadsheetApp.openByUrl(trimmedDetailUrl);
        const ssName = ss.getName();
        addLog({ type: 'success', message: `アクセス成功`, url: trimmedDetailUrl, name: ssName });

        const progressSheet = ss.getSheetByName('進捗管理表');

        if (progressSheet) {
          stats.detailSheetsSuccess++;
          addLog({ type: 'info', message: `「進捗管理表」シート: 見つかりました` });

          // 3. 進捗管理表から設計書URLを取得
          addLog({ type: 'info', message: `「進捗管理表」から設計書URLを抽出中...` });
          const designResult = getUrlsFromSheet(trimmedDetailUrl, '進捗管理表');
          const designUrls = designResult.urls;
          addLog({ type: 'success', message: `設計書URLを${designUrls.length}件検出しました` });
          stats.designDocsTotal += designUrls.length;

          designUrls.forEach((designUrl, dIndex) => {
            // URLをtrim
            const trimmedDesignUrl = designUrl.trim();

            addLog({ type: 'info', message: `  --- 設計書 [${dIndex + 1}/${designUrls.length}] ---` });
            addLog({ type: 'info', message: `  処理対象URL`, url: trimmedDesignUrl });

            // URL検証
            if (!trimmedDesignUrl || !trimmedDesignUrl.startsWith('https://docs.google.com/spreadsheets/')) {
              addLog({ type: 'warn', message: `  設計書URL検証失敗: 無効な形式`, url: trimmedDesignUrl });
              stats.errors++;
              return;
            }
            addLog({ type: 'info', message: `  設計書URL検証: OK` });

            if (processedUrls.has(trimmedDesignUrl)) {
              addLog({ type: 'info', message: `  スキップ（重複）`, url: trimmedDesignUrl });
              return;
            }
            processedUrls.add(trimmedDesignUrl);

            // 4. 設計書スプシの全シートをHTML化
            addLog({ type: 'info', message: `  設計書にアクセス中...` });
            try {
              // URLを正規化してからアクセス
              const normalizedUrl = normalizeSpreadsheetUrl(trimmedDesignUrl);
              addLog({ type: 'info', message: `  正規化URL`, url: normalizedUrl });
              const designSs = SpreadsheetApp.openByUrl(normalizedUrl);
              const designSsName = designSs.getName();
              const designSsId = designSs.getId();
              const sheets = designSs.getSheets();

              addLog({ type: 'success', message: `  アクセス成功`, url: trimmedDesignUrl, name: designSsName });
              addLog({ type: 'info', message: `  シート数: ${sheets.length}枚` });

              // フォルダ名（設計書ごと）
              const folderName = sanitizeFileName(designSsName);

              // ディレイ時間（スクリプトプロパティから取得、デフォルト1秒）
              const delayMs = parseInt(props.getProperty('EXPORT_DELAY_MS') || '1000', 10);

              // フォーマットラベルとファイル拡張子を設定
              const formatLabel = format === 'csv' ? 'CSV' : 'HTML';
              const fileExtension = format === 'csv' ? '.csv' : '.html';

              sheets.forEach((sheet, sheetIndex) => {
                const sheetId = sheet.getSheetId();
                const sheetName = sheet.getName();
                // フォルダ構成でファイル名を設定（設計書名/シート名.拡張子）
                const fileName = `${folderName}/${sanitizeFileName(sheetName)}${fileExtension}`;

                addLog({ type: 'info', message: `    シート「${sheetName}」を${formatLabel}化中...` });

                try {
                  // fetchを用いて指定形式でエクスポート (参照のみ)
                  const exportedBlob = fetchSheetAsExport(designSsId, sheetId, fileName, format);
                  zipFiles.push(exportedBlob);
                  stats.filesGenerated++;
                  addLog({ type: 'success', message: `    ${formatLabel}生成成功: ${fileName}` });

                  // レートリミット対策：各エクスポート後にディレイ（最後のシートは不要）
                  if (sheetIndex < sheets.length - 1) {
                    Utilities.sleep(delayMs);
                  }
                } catch (e) {
                  addLog({ type: 'error', message: `    ${formatLabel}生成失敗: ${fileName} - ${e.message}` });
                  stats.errors++;
                }
              });
            } catch (e) {
              addLog({ type: 'error', message: `  設計書アクセス失敗`, url: trimmedDesignUrl });
              addLog({ type: 'error', message: `  エラー詳細: ${e.message}` });
              stats.errors++;
              return;
            }
          });
        } else {
          addLog({ type: 'warn', message: `「進捗管理表」シートが見つかりません`, url: trimmedDetailUrl, name: ssName });
        }
      } catch (e) {
        addLog({ type: 'error', message: `詳細シートアクセス失敗`, url: trimmedDetailUrl });
        addLog({ type: 'error', message: `エラー詳細: ${e.message}` });
        addLog({ type: 'error', message: `エラースタック: ${e.stack || 'なし'}` });
        stats.errors++;
        return;
      }
    });

    if (zipFiles.length === 0) {
      addLog({ type: 'error', message: '設計書が1件も見つかりませんでした。アクセス権限を確認してください。' });
      throw new Error('設計書が見つかりませんでした。詳細はログを確認してください。');
    }

    // 5. ZIP圧縮
    addLog({ type: 'info', message: `ZIP作成中... (${zipFiles.length}ファイル)` });
    const zipFileName = `${sanitizeFileName(detailDebug.spreadsheetName)}.zip`;
    const zip = Utilities.zip(zipFiles, zipFileName);
    addLog({ type: 'success', message: 'ZIP作成完了！' });

    // 最終結果を返却（ログはCacheから取得）
    return {
      data: Utilities.base64Encode(zip.getBytes()),
      fileName: zipFileName,
      stats: stats
    };

  } catch (e) {
    addLog({ type: 'error', message: `処理中にエラーが発生: ${e.message}` });
    return {
      data: null,
      fileName: null,
      stats: stats,
      error: e.message
    };
  }
}

// /**
//  * シート内の全てのURLを抽出する補助関数
//  */
// function getUrlsFromSheet(url, sheetName = null) {
//   const ss = SpreadsheetApp.openByUrl(url);
//   const sheet = sheetName ? ss.getSheetByName(sheetName) : ss.getSheets()[0];
//   const values = sheet.getDataRange().getValues();
//   const urlRegex = /https:\/\/docs\.google\.com\/spreadsheets\/d\/[a-zA-Z0-9-_]+/g;
//   const urls = [];

//   values.forEach(row => {
//     row.forEach(cell => {
//       if (typeof cell === 'string') {
//         const matches = cell.match(urlRegex);
//         if (matches) urls.push(...matches);
//       }
//     });
//   });
//   return [...new Set(urls)]; // 重複削除
// }

/**
 * ファイル名をサニタイズ（ZIPで使えない文字を置換）
 * @param {string} name - 元のファイル名
 * @returns {string} - サニタイズされたファイル名
 */
function sanitizeFileName(name) {
  // ZIPで使えない文字 \ / : * ? " < > | を _ に置換
  return name.replace(/[\\/:*?"<>|]/g, '_').trim();
}

/**
 * スプレッドシートURLを正規化
 * @param {string} url - 元のURL
 * @returns {string} - 正規化されたURL
 */
function normalizeSpreadsheetUrl(url) {
  // 不可視文字を削除（Zero Width Space, BOM, Non-breaking space等）
  let cleaned = url.replace(/[\u200B-\u200D\uFEFF\u00A0]/g, '').trim();

  // IDだけのURL（/edit がない）を完全な形式に正規化
  const idMatch = cleaned.match(/\/spreadsheets\/d\/([a-zA-Z0-9_-]+)/);
  if (idMatch) {
    return `https://docs.google.com/spreadsheets/d/${idMatch[1]}/edit`;
  }
  return cleaned;
}

/**
 * シート内の全てのURLを抽出する補助関数（デバッグ情報付き）
 * @param {string} url - スプレッドシートURL
 * @param {string|null} sheetName - シート名（null=最初のシート）
 * @param {number|null} targetColumn - 対象列番号（1=A列, 2=B列, ... null=全列）
 */
function getUrlsFromSheet(url, sheetName = null, targetColumn = null) {
  const debug = {
    spreadsheetName: '',
    requestedSheetName: sheetName || '(未指定 - 最初のシート)',
    actualSheetName: '',
    availableSheets: [],
    targetColumn: targetColumn ? `${targetColumn}列目` : '全列',
    extractionMethod: targetColumn ? 'ハイパーリンクURL' : 'テキスト内URL',
    error: null
  };

  if (!url || typeof url !== 'string' || !url.startsWith('http')) {
    debug.error = '無効なURLが渡されました';
    return { urls: [], debug: debug };
  }

  try {
    const ss = SpreadsheetApp.openByUrl(url);
    debug.spreadsheetName = ss.getName();

    // 利用可能なシート一覧を取得
    const allSheets = ss.getSheets();
    debug.availableSheets = allSheets.map(s => s.getName());

    // シート取得
    let sheet;
    if (sheetName) {
      sheet = ss.getSheetByName(sheetName);
      if (!sheet) {
        debug.error = `指定されたシート「${sheetName}」が見つかりません`;
        debug.actualSheetName = '(見つからない)';
        return { urls: [], debug: debug };
      }
    } else {
      sheet = allSheets[0];
    }

    debug.actualSheetName = sheet.getName();
    const urls = [];

    // 列指定がある場合: ハイパーリンクURLを取得
    if (targetColumn) {
      const lastRow = sheet.getLastRow();
      if (lastRow > 0) {
        const range = sheet.getRange(1, targetColumn, lastRow, 1);
        const richTextValues = range.getRichTextValues();

        richTextValues.forEach((row, index) => {
          const richText = row[0];
          if (richText) {
            const linkUrl = richText.getLinkUrl();
            if (linkUrl && linkUrl.includes('docs.google.com/spreadsheets')) {
              urls.push(linkUrl.trim());
            }
          }
        });
      }
    } else {
      // 列指定なし: テキスト内のURLを正規表現で抽出
      const values = sheet.getDataRange().getValues();
      const urlRegex = /https:\/\/docs\.google\.com\/spreadsheets\/d\/[a-zA-Z0-9_-]+/g;

      values.forEach(row => {
        row.forEach(cell => {
          if (cell && typeof cell === 'string') {
            const matches = cell.match(urlRegex);
            if (matches) {
              matches.forEach(match => {
                const trimmedUrl = match.trim();
                if (trimmedUrl.length > 15) {
                  urls.push(trimmedUrl);
                }
              });
            }
          }
        });
      });
    }

    return { urls: [...new Set(urls)], debug: debug };
  } catch (e) {
    debug.error = e.message;
    return { urls: [], debug: debug };
  }
}

/**
 * 指定したシートを指定形式でエクスポート（リトライ・レートリミット対策）
 * @param {string} ssId - スプレッドシートID
 * @param {number} sheetId - シートID（gid）
 * @param {string} fileName - 出力ファイル名
 * @param {string} format - 出力形式（'html' または 'csv'）
 */
function fetchSheetAsExport(ssId, sheetId, fileName, format = 'html') {
  const url = `https://docs.google.com/spreadsheets/d/${ssId}/export?format=${format}&gid=${sheetId}`;
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

    // その他のエラーはそのまま返す（エラーページとして保存）
    Logger.log(`[エラー] ${fileName}: HTTPコード${code}`);
    return response.getBlob().setName(fileName);
  }

  // リトライ上限到達時
  throw new Error(`シート取得に失敗しました（リトライ上限）: ${fileName}`);
}