#!/usr/bin/env node
/**
 * Spreadsheet CSV Exporter - CLI エントリーポイント
 */

import { Command } from 'commander';
import * as path from 'path';
import * as fs from 'fs';
import chalk from 'chalk';
import { singleCommand } from './commands/single';
import { folderCommand } from './commands/folder';
import { filterCommand } from './commands/filter';
import { multipleCommand } from './commands/multiple';
import { scanCommand } from './commands/scan';

const program = new Command();

// デフォルト出力ディレクトリ
const DEFAULT_OUTPUT_DIR = path.join(process.cwd(), 'output');

// 出力ディレクトリを確保
function ensureOutputDir(dir: string): void {
  if (!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
  }
}

program
  .name('spreadsheet-csv-exporter')
  .description('Google Spreadsheetをローカルスクリプトで一括CSVエクスポート')
  .version('1.0.0');

// 共通オプション
program
  .option('-o, --output-dir <dir>', '出力ディレクトリ', DEFAULT_OUTPUT_DIR)
  .option('-v, --verbose', '詳細ログを表示', false);

// 単一スプレッドシートダウンロード
program
  .command('single <url>')
  .description('単一スプレッドシートをCSV ZIPとしてダウンロード')
  .action(async (url: string) => {
    const options = program.opts();
    ensureOutputDir(options.outputDir);
    await singleCommand(url, { outputDir: options.outputDir, verbose: options.verbose });
  });

// フォルダ一括ダウンロード
program
  .command('folder <folderUrl>')
  .description('フォルダ内のスプレッドシート一覧を表示し、選択してダウンロード')
  .action(async (folderUrl: string) => {
    const options = program.opts();
    ensureOutputDir(options.outputDir);
    await folderCommand(folderUrl, { outputDir: options.outputDir, verbose: options.verbose });
  });

// シート名フィルタダウンロード
program
  .command('filter <folderUrl> <sheetName>')
  .description('フォルダ内のスプレッドシートから指定シート名のみをダウンロード')
  .action(async (folderUrl: string, sheetName: string) => {
    const options = program.opts();
    ensureOutputDir(options.outputDir);
    await filterCommand(folderUrl, sheetName, { outputDir: options.outputDir, verbose: options.verbose });
  });

// 複数スプレッドシート一括ダウンロード
program
  .command('multiple <urlsFile>')
  .description('複数のスプレッドシートURLを記載したファイルから一括ダウンロード')
  .action(async (urlsFile: string) => {
    const options = program.opts();
    ensureOutputDir(options.outputDir);
    await multipleCommand(urlsFile, { outputDir: options.outputDir, verbose: options.verbose });
  });

// 進捗管理表スキャンダウンロード
program
  .command('scan <folderUrl>')
  .description('フォルダ内の「進捗管理表」シートからスプレッドシートURLを抽出してダウンロード')
  .action(async (folderUrl: string) => {
    const options = program.opts();
    ensureOutputDir(options.outputDir);
    await scanCommand(folderUrl, { outputDir: options.outputDir, verbose: options.verbose });
  });

// ヘルプテキストのカスタマイズ
program.addHelpText('after', `

例:
  # 単一スプレッドシートをダウンロード
  $ npm run export -- single "https://docs.google.com/spreadsheets/d/ABC123..."

  # フォルダ一括ダウンロード（インタラクティブ選択）
  $ npm run export -- folder "https://drive.google.com/drive/folders/XYZ789..."

  # シート名フィルタ（例: MstEvent シートのみ抽出）
  $ npm run export -- filter "https://drive.google.com/drive/folders/XYZ789..." "MstEvent"

  # 複数URLを一括ダウンロード（urls.txt に改行区切りでURLを記載）
  $ npm run export -- multiple urls.txt

  # 進捗管理表スキャン
  $ npm run export -- scan "https://drive.google.com/drive/folders/XYZ789..."

オプション:
  -o, --output-dir <dir>  出力ディレクトリ（デフォルト: ./output）
  -v, --verbose           詳細ログを表示

注意:
  - サービスアカウントのcredentials.jsonが credentials/ に必要です
  - スプレッドシートは参照のみ、編集は行いません
`);

// コマンドが指定されていない場合はヘルプを表示
if (process.argv.length <= 2) {
  program.help();
}

program.parse(process.argv);
