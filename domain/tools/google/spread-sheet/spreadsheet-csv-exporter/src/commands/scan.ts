/**
 * 進捗管理表スキャンダウンロードコマンド
 */

import * as path from 'path';
import * as readline from 'readline';
import { google } from 'googleapis';
import { getSheetsClient } from '../auth';
import { listSpreadsheetsInFolder, exportSpreadsheetToZip } from '../exporter';
import { extractFolderId, extractSpreadsheetUrls, extractSpreadsheetId, createLogger, sleep } from '../utils';
import { ExportOptions, SpreadsheetUrlData } from '../types';

/**
 * 進捗管理表シートからスプレッドシートURLを抽出
 */
async function scanProgressSheetsForUrls(folderId: string, logger: any): Promise<SpreadsheetUrlData[]> {
  logger.info('「進捗管理表」シートを検索中...');

  const spreadsheets = await listSpreadsheetsInFolder(folderId, logger);

  const urlMap: { [id: string]: SpreadsheetUrlData } = {};
  const sheets = await getSheetsClient();

  for (let i = 0; i < spreadsheets.length; i++) {
    const ss = spreadsheets[i];

    try {
      logger.info(`[${i + 1}/${spreadsheets.length}] ${ss.name} を解析中...`);

      const response = await sheets.spreadsheets.get({
        spreadsheetId: ss.id,
        fields: 'sheets(properties(title,sheetId))',
      });

      const progressSheet = response.data.sheets?.find(
        sheet => sheet.properties?.title === '進捗管理表'
      );

      if (!progressSheet) {
        continue;
      }

      logger.info(`  「${ss.name}」の進捗管理表を解析中...`);

      // シート全体のデータを取得
      const dataResponse = await sheets.spreadsheets.values.get({
        spreadsheetId: ss.id,
        range: '進捗管理表!A:ZZZ',
      });

      const rows = dataResponse.data.values || [];

      // 各セルからURL抽出
      for (const row of rows) {
        for (const cell of row) {
          if (typeof cell === 'string' && cell.includes('docs.google.com/spreadsheets')) {
            const urls = extractSpreadsheetUrls(cell);

            for (const url of urls) {
              const ssIdFromUrl = extractSpreadsheetId(url);

              if (ssIdFromUrl && !urlMap[ssIdFromUrl]) {
                try {
                  const targetResponse = await sheets.spreadsheets.get({
                    spreadsheetId: ssIdFromUrl,
                    fields: 'properties.title',
                  });

                  urlMap[ssIdFromUrl] = {
                    url,
                    name: targetResponse.data.properties?.title || 'Untitled',
                    sourceSs: ss.name,
                  };
                } catch (error: any) {
                  logger.warn(`  URL取得エラー: ${url} - ${error.message}`);
                }
              }
            }
          }
        }
      }

      logger.success(`  「${ss.name}」の解析完了`);

      if (i < spreadsheets.length - 1) {
        await sleep(300);
      }
    } catch (error: any) {
      logger.warn(`  スキップ: ${ss.name} - ${error.message}`);
    }
  }

  const urlList = Object.values(urlMap);
  logger.success(`合計 ${urlList.length}件のスプレッドシートURLを検出`);

  return urlList;
}

export async function scanCommand(folderInput: string, options: ExportOptions): Promise<void> {
  const logger = createLogger(options.verbose);

  logger.info('=== 進捗管理表スキャンダウンロード ===');
  logger.info(`フォルダ: ${folderInput}`);

  const folderId = extractFolderId(folderInput);

  try {
    const urlDataList = await scanProgressSheetsForUrls(folderId, logger);

    if (urlDataList.length === 0) {
      logger.warn('スプレッドシートURLが見つかりませんでした');
      return;
    }

    // 一覧表示
    console.log('\n=== 検出されたスプレッドシート一覧 ===');
    urlDataList.forEach((urlData, index) => {
      console.log(`[${index + 1}] ${urlData.name} (出典: ${urlData.sourceSs})`);
    });

    // インタラクティブな選択
    const rl = readline.createInterface({
      input: process.stdin,
      output: process.stdout,
    });

    const answer = await new Promise<string>(resolve => {
      rl.question('\nダウンロードする番号をカンマ区切りで入力（全て: all）: ', resolve);
    });
    rl.close();

    let selectedIndices: number[];

    if (answer.trim().toLowerCase() === 'all') {
      selectedIndices = urlDataList.map((_, i) => i);
    } else {
      selectedIndices = answer
        .split(',')
        .map(s => parseInt(s.trim(), 10) - 1)
        .filter(i => i >= 0 && i < urlDataList.length);
    }

    if (selectedIndices.length === 0) {
      logger.warn('選択されたスプレッドシートがありません');
      return;
    }

    logger.info(`${selectedIndices.length}件のスプレッドシートをダウンロードします`);

    // 順次ダウンロード
    for (let i = 0; i < selectedIndices.length; i++) {
      const index = selectedIndices[i];
      const urlData = urlDataList[index];
      const spreadsheetId = extractSpreadsheetId(urlData.url);

      if (!spreadsheetId) {
        logger.warn(`[${i + 1}/${selectedIndices.length}] 無効なURL: ${urlData.url}`);
        continue;
      }

      logger.info(`[${i + 1}/${selectedIndices.length}] ${urlData.name} を処理中...`);

      const outputFileName = `${urlData.name.replace(/[\\/:*?"<>|]/g, '_')}.zip`;
      const outputPath = path.join(options.outputDir, outputFileName);

      try {
        await exportSpreadsheetToZip(spreadsheetId, outputPath, logger);

        if (i < selectedIndices.length - 1) {
          await sleep(500);
        }
      } catch (error: any) {
        logger.error(`エラー: ${error.message}`);
      }
    }

    logger.success('全てのダウンロードが完了しました！');
  } catch (error: any) {
    logger.error(`エラー: ${error.message}`);
    process.exit(1);
  }
}
