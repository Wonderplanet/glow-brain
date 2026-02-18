// ==================== VDシート自動作成 ====================
// 「VD_シート自動作成」シートの定義に基づいてシートを一括作成する
//
// シート仕様:
//   D列（index 3）: 作成するシート名
//   E列（index 4）: コピー元テンプレートシート名
//   F列（index 5）: 作成後のシートURL（書き込み先）
// 1行目はヘッダー行として扱う

function createSheetsFromVdList() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const listSheet = ss.getSheetByName('VD_シート自動作成');
  if (!listSheet) {
    SpreadsheetApp.getUi().alert('「VD_シート自動作成」シートが見つかりません。');
    return;
  }

  const data = listSheet.getDataRange().getValues();
  let createdCount = 0;
  let skippedCount = 0;
  const errors = [];

  // 1行目はヘッダーなので i=1 からスタート
  for (let i = 1; i < data.length; i++) {
    const sheetName    = data[i][3]; // D列（0-based: index 3）
    const templateName = data[i][4]; // E列（0-based: index 4）

    if (!sheetName) continue; // シート名が空ならスキップ

    // 既に存在するシートはスキップ（F列URLも触らない）
    const existingSheet = ss.getSheetByName(sheetName);
    if (existingSheet) {
      skippedCount++;
      continue;
    }

    // テンプレートシートを取得
    const templateSheet = ss.getSheetByName(templateName);
    if (!templateSheet) {
      errors.push(`行${i + 1}: テンプレート "${templateName}" が見つかりません`);
      continue;
    }

    // テンプレートをコピーしてシート名を変更
    const newSheet = templateSheet.copyTo(ss);
    newSheet.setName(sheetName);

    // シートURLを生成してF列に書き込む（1-basedで6列目）
    const sheetUrl = `https://docs.google.com/spreadsheets/d/${ss.getId()}/edit#gid=${newSheet.getSheetId()}`;
    listSheet.getRange(i + 1, 6).setValue(sheetUrl);

    createdCount++;
  }

  let msg = `完了!\n作成: ${createdCount}件\nスキップ（既存）: ${skippedCount}件`;
  if (errors.length > 0) {
    msg += `\n\nエラー:\n${errors.join('\n')}`;
  }
  SpreadsheetApp.getUi().alert(msg);
}
