/**
 * ユーティリティ関数
 */

import chalk from 'chalk';
import { Logger } from './types';

/**
 * ファイル名をサニタイズ
 */
export function sanitizeFileName(name: string): string {
  return name.replace(/[\\/:*?"<>|]/g, '_').trim();
}

/**
 * スプレッドシートURLを正規化
 */
export function normalizeSpreadsheetUrl(url: string): string {
  // 不可視文字を削除
  let cleaned = url.replace(/[\u200B-\u200D\uFEFF\u00A0]/g, '').trim();

  // IDだけのURL（/edit がない）を完全な形式に正規化
  const idMatch = cleaned.match(/\/spreadsheets\/d\/([a-zA-Z0-9_-]+)/);
  if (idMatch) {
    return `https://docs.google.com/spreadsheets/d/${idMatch[1]}/edit`;
  }
  return cleaned;
}

/**
 * スプレッドシートURLからIDを抽出
 */
export function extractSpreadsheetId(url: string): string | null {
  const match = url.match(/\/spreadsheets\/d\/([a-zA-Z0-9_-]+)/);
  return match ? match[1] : null;
}

/**
 * フォルダURL/IDからIDを抽出
 */
export function extractFolderId(input: string): string {
  // 既にIDの場合はそのまま返す
  if (!input.includes('/')) {
    return input.trim();
  }

  // URLからID抽出
  const match = input.match(/\/folders\/([a-zA-Z0-9_-]+)/);
  if (match) {
    return match[1];
  }

  // 抽出できない場合はそのまま返す
  return input.trim();
}

/**
 * 文字列からスプレッドシートURLを抽出
 */
export function extractSpreadsheetUrls(text: string): string[] {
  const pattern = /https:\/\/docs\.google\.com\/spreadsheets\/d\/[a-zA-Z0-9_-]+(?:\/[^\s"'<>]*)?/g;
  return text.match(pattern) || [];
}

/**
 * スリープ
 */
export function sleep(ms: number): Promise<void> {
  return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * ロガー作成
 */
export function createLogger(verbose: boolean = false): Logger {
  return {
    info: (message: string) => {
      if (verbose) {
        console.log(chalk.blue('[INFO]'), message);
      }
    },
    success: (message: string) => {
      console.log(chalk.green('[SUCCESS]'), message);
    },
    warn: (message: string) => {
      console.log(chalk.yellow('[WARN]'), message);
    },
    error: (message: string) => {
      console.log(chalk.red('[ERROR]'), message);
    },
  };
}

/**
 * タイムスタンプ付きファイル名生成
 */
export function generateTimestampedFileName(prefix: string, ext: string = 'zip'): string {
  const now = new Date();
  const timestamp = now.toISOString()
    .replace(/T/, '_')
    .replace(/:/g, '-')
    .replace(/\..+/, '');
  return `${prefix}_${timestamp}.${ext}`;
}
