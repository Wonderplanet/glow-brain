/**
 * Google API認証処理（サービスアカウント）
 */

import { google } from 'googleapis';
import { GoogleAuth } from 'google-auth-library';
import * as path from 'path';
import * as fs from 'fs';
import chalk from 'chalk';

const SCOPES = [
  'https://www.googleapis.com/auth/spreadsheets.readonly',
  'https://www.googleapis.com/auth/drive.readonly',
];

/**
 * サービスアカウント認証を取得
 */
export async function getAuth(): Promise<GoogleAuth> {
  const credentialsPath = path.join(__dirname, '../credentials/credentials.json');

  if (!fs.existsSync(credentialsPath)) {
    console.error(chalk.red('[ERROR]'), 'credentials.json が見つかりません');
    console.error(chalk.yellow('パス:'), credentialsPath);
    console.error(chalk.yellow('ヒント:'), 'サービスアカウントのJSONキーを credentials/credentials.json に配置してください');
    process.exit(1);
  }

  const auth = new google.auth.GoogleAuth({
    keyFile: credentialsPath,
    scopes: SCOPES,
  });

  return auth;
}

/**
 * Google Sheets APIクライアントを取得
 */
export async function getSheetsClient() {
  const auth = await getAuth();
  return google.sheets({ version: 'v4', auth });
}

/**
 * Google Drive APIクライアントを取得
 */
export async function getDriveClient() {
  const auth = await getAuth();
  return google.drive({ version: 'v3', auth });
}
