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
  try {
    const zipFiles = [];
    const processedUrls = new Set(); // 重複処理防止

    // 1. 一覧シートから詳細シートURLを取得
    const detailUrls = getUrlsFromSheet(listSheetUrl);

    detailUrls.forEach(detailUrl => {
      // 2. 詳細シートから「進捗管理表」シートを特定
      const ss = SpreadsheetApp.openByUrl(detailUrl);
      const progressSheet = ss.getSheetByName('進捗管理表');
      
      if (progressSheet) {
        // 3. 進捗管理表から設計書URLを取得
        const designUrls = getUrlsFromSheet(detailUrl, '進捗管理表');

        designUrls.forEach(designUrl => {
          if (processedUrls.has(designUrl)) return;
          processedUrls.add(designUrl);

          // 4. 設計書スプシの全シートをHTML化
          const designSs = SpreadsheetApp.openByUrl(designUrl);
          const designSsId = designSs.getId();
          const sheets = designSs.getSheets();

          sheets.forEach(sheet => {
            const sheetId = sheet.getSheetId();
            const fileName = `${designSs.getName()}_${sheet.getName()}.html`;
            
            // fetchを用いてHTMLとしてエクスポート (参照のみ)
            const htmlBlob = fetchSheetAsHtml(designSsId, sheetId, fileName);
            zipFiles.push(htmlBlob);
          });
        });
      }
    });

    if (zipFiles.length === 0) throw new Error('設計書が見つかりませんでした。');

    // 5. ZIP圧縮
    const zip = Utilities.zip(zipFiles, 'DesignDocuments.zip');
    return {
      data: Utilities.base64Encode(zip.getBytes()),
      fileName: 'DesignDocuments.zip'
    };

  } catch (e) {
    throw new Error('処理中にエラーが発生しました: ' + e.message);
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
 * シート内の全てのURLを抽出する補助関数（エラー対策版）
 */
function getUrlsFromSheet(url, sheetName = null) {
  if (!url || typeof url !== 'string' || !url.startsWith('http')) {
    console.warn('無効なURLが渡されました: ' + url);
    return [];
  }

  try {
    const ss = SpreadsheetApp.openByUrl(url);
    const sheet = sheetName ? ss.getSheetByName(sheetName) : ss.getSheets()[0];
    if (!sheet) return [];

    const values = sheet.getDataRange().getValues();
    const urlRegex = /https:\/\/docs\.google\.com\/spreadsheets\/d\/[a-zA-Z0-9-_]+/g;
    const urls = [];

    values.forEach(row => {
      row.forEach(cell => {
        if (cell && typeof cell === 'string') { // セルが空でないか、文字列かを確認
          const matches = cell.match(urlRegex);
          if (matches) {
            matches.forEach(match => {
              // 抽出されたURLが正しい形式か最終確認
              if (match.length > 15) { 
                urls.push(match);
              }
            });
          }
        }
      });
    });
    return [...new Set(urls)]; 
  } catch (e) {
    console.error('URL解析中にエラーが発生しました: ' + url, e.message);
    return []; // エラーが起きたURLはスキップして継続
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