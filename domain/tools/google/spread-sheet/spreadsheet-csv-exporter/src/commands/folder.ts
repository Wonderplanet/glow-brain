/**
 * フォルダ一括ダウンロードコマンド
 */

import * as path from 'path';
import * as readline from 'readline';
import { listSpreadsheetsInFolder, exportSpreadsheetToZip } from '../exporter';
import { extractFolderId, sanitizeFileName, createLogger, sleep } from '../utils';
import { ExportOptions } from '../types';

export async function folderCommand(folderInput: string, options: ExportOptions): Promise<void> {
  const logger = createLogger(options.verbose);

  logger.info('=== フォルダ一括ダウンロード ===');
  logger.info(`フォルダ: ${folderInput}`);

  const folderId = extractFolderId(folderInput);
  logger.info(`フォルダID: ${folderId}`);

  try {
    const spreadsheets = await listSpreadsheetsInFolder(folderId, logger);

    if (spreadsheets.length === 0) {
      logger.warn('スプレッドシートが見つかりませんでした');
      return;
    }

    // 一覧表示
    console.log('\n=== スプレッドシート一覧 ===');
    spreadsheets.forEach((ss, index) => {
      console.log(`[${index + 1}] ${ss.name}`);
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
      selectedIndices = spreadsheets.map((_, i) => i);
    } else {
      selectedIndices = answer
        .split(',')
        .map(s => parseInt(s.trim(), 10) - 1)
        .filter(i => i >= 0 && i < spreadsheets.length);
    }

    if (selectedIndices.length === 0) {
      logger.warn('選択されたスプレッドシートがありません');
      return;
    }

    logger.info(`${selectedIndices.length}件のスプレッドシートをダウンロードします`);

    // 順次ダウンロード
    for (let i = 0; i < selectedIndices.length; i++) {
      const index = selectedIndices[i];
      const ss = spreadsheets[index];

      logger.info(`[${i + 1}/${selectedIndices.length}] ${ss.name} を処理中...`);

      const outputFileName = `${sanitizeFileName(ss.name)}.zip`;
      const outputPath = path.join(options.outputDir, outputFileName);

      try {
        await exportSpreadsheetToZip(ss.id, outputPath, logger);

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
