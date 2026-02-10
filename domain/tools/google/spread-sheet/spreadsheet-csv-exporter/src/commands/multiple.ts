/**
 * 複数スプレッドシート一括ダウンロードコマンド
 */

import * as path from 'path';
import * as fs from 'fs';
import archiver from 'archiver';
import { exportSheetAsCsvViaExport, getSpreadsheetMetadata } from '../exporter';
import { extractSpreadsheetId, sanitizeFileName, createLogger, generateTimestampedFileName, sleep } from '../utils';
import { ExportOptions } from '../types';

export async function multipleCommand(urlsFile: string, options: ExportOptions): Promise<void> {
  const logger = createLogger(options.verbose);

  logger.info('=== 複数スプレッドシート一括ダウンロード ===');
  logger.info(`URLファイル: ${urlsFile}`);

  if (!fs.existsSync(urlsFile)) {
    logger.error('URLファイルが見つかりません');
    process.exit(1);
  }

  const content = fs.readFileSync(urlsFile, 'utf-8');
  const urls = content
    .split('\n')
    .map(line => line.trim())
    .filter(line => line.length > 0 && line.includes('spreadsheets'));

  logger.info(`${urls.length}件のURLを検出`);

  if (urls.length === 0) {
    logger.warn('有効なURLがありません');
    return;
  }

  try {
    const outputFileName = generateTimestampedFileName('spreadsheets');
    const outputPath = path.join(options.outputDir, outputFileName);

    logger.info(`出力先: ${outputPath}`);

    const output = fs.createWriteStream(outputPath);
    const archive = archiver('zip', { zlib: { level: 9 } });
    archive.pipe(output);

    let successCount = 0;

    for (let i = 0; i < urls.length; i++) {
      const url = urls[i];
      const spreadsheetId = extractSpreadsheetId(url);

      if (!spreadsheetId) {
        logger.warn(`[${i + 1}/${urls.length}] 無効なURL: ${url}`);
        continue;
      }

      logger.info(`[${i + 1}/${urls.length}] 処理中...`);

      try {
        const { name, sheets } = await getSpreadsheetMetadata(spreadsheetId);
        logger.success(`  アクセス成功: ${name} (${sheets.length}シート)`);

        const visibleSheets = sheets.filter(sheet => !sheet.hidden);

        for (let j = 0; j < visibleSheets.length; j++) {
          const sheet = visibleSheets[j];

          try {
            const csvContent = await exportSheetAsCsvViaExport(spreadsheetId, sheet.sheetId, logger);
            const fileName = `${sanitizeFileName(name)}/${sanitizeFileName(sheet.title)}.csv`;

            const bom = '\uFEFF';
            archive.append(bom + csvContent, { name: fileName });

            logger.info(`  CSV生成: ${sheet.title}`);

            if (j < visibleSheets.length - 1) {
              await sleep(300);
            }
          } catch (error: any) {
            logger.warn(`  スキップ: ${sheet.title} - ${error.message}`);
          }
        }

        successCount++;

        if (i < urls.length - 1) {
          await sleep(500);
        }
      } catch (error: any) {
        logger.warn(`[${i + 1}/${urls.length}] スキップ: ${error.message}`);
      }
    }

    await archive.finalize();

    await new Promise<void>((resolve, reject) => {
      output.on('close', () => {
        logger.success(`ZIP作成完了: ${outputPath}`);
        logger.success(`${successCount}件のスプレッドシートをダウンロードしました！`);
        resolve();
      });
      archive.on('error', reject);
    });
  } catch (error: any) {
    logger.error(`エラー: ${error.message}`);
    process.exit(1);
  }
}
