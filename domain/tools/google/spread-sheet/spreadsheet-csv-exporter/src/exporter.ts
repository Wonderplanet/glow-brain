/**
 * スプレッドシート→CSV→ZIPエクスポート処理
 */

import { google } from 'googleapis';
import archiver from 'archiver';
import * as fs from 'fs';
import * as path from 'path';
import { getSheetsClient, getDriveClient } from './auth';
import { sanitizeFileName, sleep } from './utils';
import { Logger, SheetInfo, SpreadsheetInfo } from './types';

/**
 * スプレッドシートのメタデータを取得
 */
export async function getSpreadsheetMetadata(spreadsheetId: string): Promise<{
  name: string;
  sheets: SheetInfo[];
}> {
  const sheets = await getSheetsClient();

  const response = await sheets.spreadsheets.get({
    spreadsheetId,
    fields: 'properties.title,sheets(properties(sheetId,title,hidden))',
  });

  const name = response.data.properties?.title || 'Untitled';
  const sheetList: SheetInfo[] = (response.data.sheets || []).map(sheet => ({
    sheetId: sheet.properties?.sheetId || 0,
    title: sheet.properties?.title || 'Untitled',
    hidden: sheet.properties?.hidden || false,
  }));

  return { name, sheets: sheetList };
}

/**
 * シートをCSVとしてエクスポート（リトライ付き）
 */
export async function exportSheetAsCsv(
  spreadsheetId: string,
  sheetId: number,
  logger: Logger,
  maxRetries: number = 3
): Promise<string> {
  const sheets = await getSheetsClient();

  for (let retry = 0; retry < maxRetries; retry++) {
    try {
      // Google Sheets APIでシートのデータを取得
      const response = await sheets.spreadsheets.get({
        spreadsheetId,
        ranges: [`'${sheetId}'!A:ZZZ`], // シートID指定は不可なのでタイトルで指定が必要
        includeGridData: true,
      });

      // データをCSV形式に変換
      const sheet = response.data.sheets?.[0];
      if (!sheet || !sheet.data?.[0]?.rowData) {
        return '';
      }

      const rows = sheet.data[0].rowData;
      const csvLines: string[] = [];

      for (const row of rows) {
        const cells = row.values || [];
        const cellValues = cells.map(cell => {
          const value = cell.formattedValue || '';
          // CSV形式にエスケープ（ダブルクォートとカンマを含む場合）
          if (value.includes(',') || value.includes('"') || value.includes('\n')) {
            return `"${value.replace(/"/g, '""')}"`;
          }
          return value;
        });
        csvLines.push(cellValues.join(','));
      }

      return csvLines.join('\n');
    } catch (error: any) {
      if (error.code === 429 || error.code >= 500) {
        const waitMs = 3000 * (retry + 1); // 3秒、6秒、9秒
        logger.warn(`レートリミットエラー、${waitMs}ms待機後に再試行...`);
        await sleep(waitMs);
        continue;
      }
      throw error;
    }
  }

  throw new Error(`シートのエクスポートに失敗しました（リトライ上限到達）`);
}

/**
 * 別の方法: Export API を使用してCSVを取得（より簡単）
 */
export async function exportSheetAsCsvViaExport(
  spreadsheetId: string,
  sheetId: number,
  logger: Logger,
  maxRetries: number = 3
): Promise<string> {
  const auth = await getSheetsClient();
  const authClient = await auth.auth.getClient();

  const url = `https://docs.google.com/spreadsheets/d/${spreadsheetId}/export?format=csv&gid=${sheetId}`;

  for (let retry = 0; retry < maxRetries; retry++) {
    try {
      const response = await authClient.request({
        url,
        method: 'GET',
        responseType: 'text',
      });

      return response.data as string;
    } catch (error: any) {
      if (error.response?.status === 429 || error.response?.status >= 500) {
        const waitMs = 3000 * (retry + 1);
        logger.warn(`レートリミットエラー、${waitMs}ms待機後に再試行...`);
        await sleep(waitMs);
        continue;
      }
      throw error;
    }
  }

  throw new Error(`シートのエクスポートに失敗しました（リトライ上限到達）`);
}

/**
 * スプレッドシート全体をZIPにエクスポート
 */
export async function exportSpreadsheetToZip(
  spreadsheetId: string,
  outputPath: string,
  logger: Logger
): Promise<void> {
  logger.info('スプレッドシートのメタデータを取得中...');
  const { name, sheets } = await getSpreadsheetMetadata(spreadsheetId);

  logger.success(`スプレッドシート: ${name}`);
  logger.info(`シート数: ${sheets.length}枚`);

  // 非表示シートを除外
  const visibleSheets = sheets.filter(sheet => !sheet.hidden);
  logger.info(`処理対象: ${visibleSheets.length}枚（非表示シート除外）`);

  // ZIPファイル作成
  const output = fs.createWriteStream(outputPath);
  const archive = archiver('zip', { zlib: { level: 9 } });

  archive.pipe(output);

  const folderName = sanitizeFileName(name);

  // 各シートをCSVに変換してZIPに追加
  for (let i = 0; i < visibleSheets.length; i++) {
    const sheet = visibleSheets[i];
    logger.info(`[${i + 1}/${visibleSheets.length}] シート「${sheet.title}」をCSV化中...`);

    try {
      const csvContent = await exportSheetAsCsvViaExport(spreadsheetId, sheet.sheetId, logger);
      const fileName = `${folderName}/${sanitizeFileName(sheet.title)}.csv`;

      // UTF-8 BOM付きで追加
      const bom = '\uFEFF';
      archive.append(bom + csvContent, { name: fileName });

      logger.success(`CSV生成成功: ${fileName}`);

      // レートリミット対策（最後以外は500msスリープ）
      if (i < visibleSheets.length - 1) {
        await sleep(500);
      }
    } catch (error: any) {
      logger.error(`CSV生成失敗: ${sheet.title} - ${error.message}`);
    }
  }

  await archive.finalize();

  return new Promise((resolve, reject) => {
    output.on('close', () => {
      logger.success(`ZIP作成完了: ${outputPath} (${archive.pointer()} bytes)`);
      resolve();
    });
    archive.on('error', reject);
  });
}

/**
 * フォルダ内のスプレッドシート一覧を取得
 */
export async function listSpreadsheetsInFolder(folderId: string, logger: Logger): Promise<SpreadsheetInfo[]> {
  logger.info('フォルダにアクセス中...');

  const drive = await getDriveClient();

  const response = await drive.files.list({
    q: `'${folderId}' in parents and mimeType='application/vnd.google-apps.spreadsheet' and trashed=false`,
    fields: 'files(id, name, webViewLink)',
  });

  const spreadsheets: SpreadsheetInfo[] = (response.data.files || []).map(file => ({
    id: file.id || '',
    name: file.name || 'Untitled',
    url: file.webViewLink || '',
  }));

  logger.success(`${spreadsheets.length}件のスプレッドシートを検出`);

  return spreadsheets;
}

/**
 * フォルダ内のスプレッドシートから特定シート名のみを抽出してZIP化
 */
export async function exportFilteredSheets(
  folderId: string,
  targetSheetName: string,
  outputPath: string,
  logger: Logger
): Promise<number> {
  logger.info(`シート名「${targetSheetName}」でフィルタ中...`);

  const spreadsheets = await listSpreadsheetsInFolder(folderId, logger);

  const output = fs.createWriteStream(outputPath);
  const archive = archiver('zip', { zlib: { level: 9 } });
  archive.pipe(output);

  let matchCount = 0;

  for (let i = 0; i < spreadsheets.length; i++) {
    const ss = spreadsheets[i];
    logger.info(`[${i + 1}/${spreadsheets.length}] ${ss.name} を処理中...`);

    try {
      const { sheets } = await getSpreadsheetMetadata(ss.id);
      const matchingSheets = sheets.filter(
        sheet => sheet.title === targetSheetName && !sheet.hidden
      );

      for (const sheet of matchingSheets) {
        logger.info(`  シート「${sheet.title}」をCSV化中...`);
        const csvContent = await exportSheetAsCsvViaExport(ss.id, sheet.sheetId, logger);
        const fileName = `${sanitizeFileName(ss.name)}_${sanitizeFileName(targetSheetName)}.csv`;

        const bom = '\uFEFF';
        archive.append(bom + csvContent, { name: fileName });

        logger.success(`  CSV生成成功: ${fileName}`);
        matchCount++;
      }

      if (i < spreadsheets.length - 1) {
        await sleep(500);
      }
    } catch (error: any) {
      logger.warn(`  スキップ: ${ss.name} - ${error.message}`);
    }
  }

  await archive.finalize();

  return new Promise((resolve, reject) => {
    output.on('close', () => {
      logger.success(`ZIP作成完了: ${outputPath}`);
      resolve(matchCount);
    });
    archive.on('error', reject);
  });
}
