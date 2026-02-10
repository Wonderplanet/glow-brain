/**
 * 型定義
 */

export interface SpreadsheetInfo {
  id: string;
  name: string;
  url: string;
}

export interface SheetInfo {
  sheetId: number;
  title: string;
  hidden: boolean;
}

export interface ExportOptions {
  outputDir: string;
  verbose?: boolean;
}

export interface SpreadsheetUrlData {
  url: string;
  name: string;
  sourceSs: string;
}

export interface Logger {
  info(message: string): void;
  success(message: string): void;
  warn(message: string): void;
  error(message: string): void;
}
