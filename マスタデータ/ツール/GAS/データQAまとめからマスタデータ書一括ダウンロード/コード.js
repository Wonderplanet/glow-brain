/**
 * WebアプリのUIを表示
 */
function doGet() {
  return HtmlService.createHtmlOutputFromFile('Index')
    .setTitle('設計書HTML一括ダウンローダー')
    .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL);
}

/**
 * メイン処理：URLから設計書を探索しZIPを作成
 */
function processSpreadsheets(listSheetUrl) {
  const logs = []; // ログ収集用
  const stats = { detailSheetsTotal: 0, detailSheetsSuccess: 0, designDocsTotal: 0, filesGenerated: 0, errors: 0 };

  try {
    const zipFiles = [];
    const processedUrls = new Set(); // 重複処理防止

    logs.push({ type: 'info', message: '一覧シートからURLを取得中...' });

    // 1. 一覧シートから詳細シートURLを取得（B列のハイパーリンクから）
    const detailResult = getUrlsFromSheet(listSheetUrl, '総合進捗管理', 2);  // B列(2)のハイパーリンク
    const detailUrls = detailResult.urls;
    const detailDebug = detailResult.debug;

    // デバッグ情報を表示
    logs.push({ type: 'info', message: `スプレッドシート: ${detailDebug.spreadsheetName}` });
    logs.push({ type: 'info', message: `要求シート: ${detailDebug.requestedSheetName}` });
    logs.push({ type: 'info', message: `実際のシート: ${detailDebug.actualSheetName}` });
    logs.push({ type: 'info', message: `対象列: ${detailDebug.targetColumn}` });
    logs.push({ type: 'info', message: `抽出方法: ${detailDebug.extractionMethod}` });
    logs.push({ type: 'info', message: `利用可能なシート: ${detailDebug.availableSheets.join(', ')}` });

    if (detailDebug.error) {
      logs.push({ type: 'error', message: `エラー: ${detailDebug.error}` });
    }

    stats.detailSheetsTotal = detailUrls.length;
    logs.push({ type: 'info', message: `詳細シートを${detailUrls.length}件検出しました` });

    // デバッグ: 取得したURL一覧を表示
    detailUrls.forEach((url, index) => {
      logs.push({ type: 'info', message: `  [${index + 1}] 検出URL`, url: url });
    });

    // デバッグ用：最初の1件のみ処理
    const DEBUG_LIMIT = detailUrls.length; // 一時的なデバッグ用制限
    const urlsToProcess = detailUrls.slice(0, DEBUG_LIMIT);
    logs.push({ type: 'warn', message: `⚠️ デバッグモード：最初の${DEBUG_LIMIT}件のみ処理します` });

    urlsToProcess.forEach((detailUrl, index) => {
      // URLをtrim
      const trimmedDetailUrl = detailUrl.trim();

      logs.push({ type: 'info', message: `\n--- [${index + 1}/${urlsToProcess.length}] 詳細シート処理開始 ---` });
      logs.push({ type: 'info', message: `処理対象URL`, url: trimmedDetailUrl });

      // URL検証
      if (!trimmedDetailUrl || !trimmedDetailUrl.startsWith('https://docs.google.com/spreadsheets/')) {
        logs.push({ type: 'warn', message: `URL検証失敗: 無効な形式`, url: trimmedDetailUrl });
        stats.errors++;
        return;
      }
      logs.push({ type: 'info', message: `URL検証: OK` });

      // 2. 詳細シートから「進捗管理表」シートを特定
      logs.push({ type: 'info', message: `スプレッドシートにアクセス中...` });
      try {
        const ss = SpreadsheetApp.openByUrl(trimmedDetailUrl);
        const ssName = ss.getName();
        logs.push({ type: 'success', message: `アクセス成功`, url: trimmedDetailUrl, name: ssName });

        const progressSheet = ss.getSheetByName('進捗管理表');

        if (progressSheet) {
          stats.detailSheetsSuccess++;
          logs.push({ type: 'info', message: `「進捗管理表」シート: 見つかりました` });

          // 3. 進捗管理表から設計書URLを取得
          logs.push({ type: 'info', message: `「進捗管理表」から設計書URLを抽出中...` });
          const designResult = getUrlsFromSheet(trimmedDetailUrl, '進捗管理表');
          const designUrls = designResult.urls;
          logs.push({ type: 'success', message: `設計書URLを${designUrls.length}件検出しました` });
          stats.designDocsTotal += designUrls.length;

          designUrls.forEach((designUrl, dIndex) => {
            // URLをtrim
            const trimmedDesignUrl = designUrl.trim();

            logs.push({ type: 'info', message: `  --- 設計書 [${dIndex + 1}/${designUrls.length}] ---` });
            logs.push({ type: 'info', message: `  処理対象URL`, url: trimmedDesignUrl });

            // URL検証
            if (!trimmedDesignUrl || !trimmedDesignUrl.startsWith('https://docs.google.com/spreadsheets/')) {
              logs.push({ type: 'warn', message: `  設計書URL検証失敗: 無効な形式`, url: trimmedDesignUrl });
              stats.errors++;
              return;
            }
            logs.push({ type: 'info', message: `  設計書URL検証: OK` });

            if (processedUrls.has(trimmedDesignUrl)) {
              logs.push({ type: 'info', message: `  スキップ（重複）`, url: trimmedDesignUrl });
              return;
            }
            processedUrls.add(trimmedDesignUrl);

            // 4. 設計書スプシの全シートをHTML化
            logs.push({ type: 'info', message: `  設計書にアクセス中...` });
            try {
              // URLを正規化してからアクセス
              const normalizedUrl = normalizeSpreadsheetUrl(trimmedDesignUrl);
              logs.push({ type: 'info', message: `  正規化URL`, url: normalizedUrl });
              const designSs = SpreadsheetApp.openByUrl(normalizedUrl);
              const designSsName = designSs.getName();
              const designSsId = designSs.getId();
              const sheets = designSs.getSheets();

              logs.push({ type: 'success', message: `  アクセス成功`, url: trimmedDesignUrl, name: designSsName });
              logs.push({ type: 'info', message: `  シート数: ${sheets.length}枚` });

              // フォルダ名（設計書ごと）
              const folderName = sanitizeFileName(designSsName);

              sheets.forEach(sheet => {
                const sheetId = sheet.getSheetId();
                const sheetName = sheet.getName();
                // フォルダ構成でファイル名を設定（設計書名/シート名.html）
                const fileName = `${folderName}/${sanitizeFileName(sheetName)}.html`;

                logs.push({ type: 'info', message: `    シート「${sheetName}」をHTML化中...` });
                // fetchを用いてHTMLとしてエクスポート (参照のみ)
                const htmlBlob = fetchSheetAsHtml(designSsId, sheetId, fileName);
                zipFiles.push(htmlBlob);
                stats.filesGenerated++;
                logs.push({ type: 'success', message: `    HTML生成成功: ${fileName}` });
              });
            } catch (e) {
              logs.push({ type: 'error', message: `  設計書アクセス失敗`, url: trimmedDesignUrl });
              logs.push({ type: 'error', message: `  エラー詳細: ${e.message}` });
              stats.errors++;
              return;
            }
          });
        } else {
          logs.push({ type: 'warn', message: `「進捗管理表」シートが見つかりません`, url: trimmedDetailUrl, name: ssName });
        }
      } catch (e) {
        logs.push({ type: 'error', message: `詳細シートアクセス失敗`, url: trimmedDetailUrl });
        logs.push({ type: 'error', message: `エラー詳細: ${e.message}` });
        logs.push({ type: 'error', message: `エラースタック: ${e.stack || 'なし'}` });
        stats.errors++;
        return;
      }
    });

    if (zipFiles.length === 0) {
      logs.push({ type: 'error', message: '設計書が1件も見つかりませんでした。アクセス権限を確認してください。' });
      throw new Error('設計書が見つかりませんでした。詳細はログを確認してください。');
    }

    // 5. ZIP圧縮
    logs.push({ type: 'info', message: `ZIP作成中... (${zipFiles.length}ファイル)` });
    const zip = Utilities.zip(zipFiles, 'DesignDocuments.zip');
    logs.push({ type: 'success', message: 'ZIP作成完了！' });

    return {
      data: Utilities.base64Encode(zip.getBytes()),
      fileName: 'DesignDocuments.zip',
      logs: logs,
      stats: stats
    };

  } catch (e) {
    logs.push({ type: 'error', message: `処理中にエラーが発生: ${e.message}` });
    return {
      data: null,
      fileName: null,
      logs: logs,
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
 * 指定したシートをHTMLとして取得
 */
function fetchSheetAsHtml(ssId, sheetId, fileName) {
  const url = `https://docs.google.com/spreadsheets/d/${ssId}/export?format=html&gid=${sheetId}`;
  const token = ScriptApp.getOAuthToken();
  const response = UrlFetchApp.fetch(url, {
    headers: { 'Authorization': 'Bearer ' + token },
    muteHttpExceptions: true
  });
  return response.getBlob().setName(fileName);
}