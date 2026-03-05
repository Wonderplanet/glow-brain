/**
 * シート名フィルタダウンロードコマンド
 */

import * as path from 'path';
import { exportFilteredSheets } from '../exporter';
import { extractFolderId, sanitizeFileName, createLogger } from '../utils';
import { ExportOptions } from '../types';

export async function filterCommand(
  folderInput: string,
  sheetName: string,
  options: ExportOptions
): Promise<void> {
  const logger = createLogger(options.verbose);

  logger.info('=== シート名フィルタダウンロード ===');
  logger.info(`フォルダ: ${folderInput}`);
  logger.info(`シート名: ${sheetName}`);

  const folderId = extractFolderId(folderInput);

  try {
    const outputFileName = `${sanitizeFileName(sheetName)}_filtered.zip`;
    const outputPath = path.join(options.outputDir, outputFileName);

    logger.info(`出力先: ${outputPath}`);

    const matchCount = await exportFilteredSheets(folderId, sheetName, outputPath, logger);

    if (matchCount === 0) {
      logger.warn(`シート「${sheetName}」が見つかりませんでした`);
    } else {
      logger.success(`${matchCount}件のシートをダウンロードしました！`);
    }
  } catch (error: any) {
    logger.error(`エラー: ${error.message}`);
    process.exit(1);
  }
}
