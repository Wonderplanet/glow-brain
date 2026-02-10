/**
 * 単一スプレッドシートダウンロードコマンド
 */

import * as path from 'path';
import { exportSpreadsheetToZip } from '../exporter';
import { extractSpreadsheetId, sanitizeFileName, createLogger } from '../utils';
import { getSpreadsheetMetadata } from '../exporter';
import { ExportOptions } from '../types';

export async function singleCommand(url: string, options: ExportOptions): Promise<void> {
  const logger = createLogger(options.verbose);

  logger.info('=== 単一スプレッドシートダウンロード ===');
  logger.info(`URL: ${url}`);

  const spreadsheetId = extractSpreadsheetId(url);
  if (!spreadsheetId) {
    logger.error('無効なスプレッドシートURLです');
    process.exit(1);
  }

  logger.info(`スプレッドシートID: ${spreadsheetId}`);

  try {
    // スプレッドシート名を取得
    const { name } = await getSpreadsheetMetadata(spreadsheetId);
    const outputFileName = `${sanitizeFileName(name)}.zip`;
    const outputPath = path.join(options.outputDir, outputFileName);

    logger.info(`出力先: ${outputPath}`);

    await exportSpreadsheetToZip(spreadsheetId, outputPath, logger);

    logger.success('ダウンロード完了！');
  } catch (error: any) {
    logger.error(`エラー: ${error.message}`);
    process.exit(1);
  }
}
