#!/usr/bin/env python3
"""
データQAまとめからマスタデータ書一括ダウンロードスクリプト

GASの処理をPythonで再実装したCLIツールです。
一覧シートから詳細シート→設計書を辿り、全シートをHTML/CSV形式でZIPダウンロードします。
"""

import argparse
import os
import re
import sys
import time
import zipfile
from io import BytesIO
from pathlib import Path
from typing import List, Dict, Optional, Tuple
from urllib.parse import urlparse, parse_qs

import gspread
import requests
from google.oauth2 import service_account
from googleapiclient.discovery import build


class MasterdataDocsDownloader:
    """マスタデータ設計書一括ダウンローダー"""

    # Google Sheets URLパターン
    URL_PATTERN = re.compile(r'https://docs\.google\.com/spreadsheets/d/[a-zA-Z0-9_-]+')

    # OAuth2スコープ
    SCOPES = [
        'https://www.googleapis.com/auth/spreadsheets.readonly',
        'https://www.googleapis.com/auth/drive.readonly'
    ]

    def __init__(self, credentials_path: str):
        """
        初期化

        Args:
            credentials_path: サービスアカウントのcredentials.jsonパス
        """
        self.credentials = service_account.Credentials.from_service_account_file(
            credentials_path,
            scopes=self.SCOPES
        )
        self.gc = gspread.authorize(self.credentials)
        self.sheets_service = build('sheets', 'v4', credentials=self.credentials)
        self.stats = {
            'detail_sheets_total': 0,
            'detail_sheets_success': 0,
            'design_docs_total': 0,
            'files_generated': 0,
            'errors': 0
        }

    def log(self, level: str, message: str, url: Optional[str] = None, name: Optional[str] = None):
        """ログ出力"""
        prefix = f"[{level.upper()}]"
        if url:
            if name:
                print(f"{prefix} {message}: {name} ({url})")
            else:
                print(f"{prefix} {message}: {url}")
        else:
            print(f"{prefix} {message}")

    def sanitize_filename(self, name: str) -> str:
        """
        ファイル名をサニタイズ

        Args:
            name: 元のファイル名

        Returns:
            サニタイズされたファイル名
        """
        # ZIPで使えない文字 \ / : * ? " < > | を _ に置換
        return re.sub(r'[\\/:*?"<>|]', '_', name).strip()

    def normalize_spreadsheet_url(self, url: str) -> str:
        """
        スプレッドシートURLを正規化

        Args:
            url: 元のURL

        Returns:
            正規化されたURL
        """
        # 不可視文字を削除
        cleaned = re.sub(r'[\u200B-\u200D\uFEFF\u00A0]', '', url).strip()

        # IDだけのURL（/edit がない）を完全な形式に正規化
        match = re.search(r'/spreadsheets/d/([a-zA-Z0-9_-]+)', cleaned)
        if match:
            return f"https://docs.google.com/spreadsheets/d/{match.group(1)}/edit"
        return cleaned

    def extract_spreadsheet_id(self, url: str) -> Optional[str]:
        """
        URLからスプレッドシートIDを抽出

        Args:
            url: スプレッドシートURL

        Returns:
            スプレッドシートID、抽出失敗時はNone
        """
        match = re.search(r'/spreadsheets/d/([a-zA-Z0-9_-]+)', url)
        return match.group(1) if match else None

    def get_urls_from_sheet(
        self,
        url: str,
        sheet_name: Optional[str] = None,
        target_column: Optional[int] = None
    ) -> Tuple[List[str], Dict]:
        """
        シート内の全てのURLを抽出

        Args:
            url: スプレッドシートURL
            sheet_name: シート名（Noneの場合は最初のシート）
            target_column: 対象列番号（1=A列, 2=B列, ... Noneの場合は全列）

        Returns:
            (URLリスト, デバッグ情報の辞書)
        """
        debug = {
            'spreadsheet_name': '',
            'requested_sheet_name': sheet_name or '(未指定 - 最初のシート)',
            'actual_sheet_name': '',
            'available_sheets': [],
            'target_column': f'{target_column}列目' if target_column else '全列',
            'extraction_method': 'ハイパーリンクURL' if target_column else 'テキスト内URL',
            'error': None
        }

        if not url or not url.startswith('http'):
            debug['error'] = '無効なURLが渡されました'
            return [], debug

        try:
            ss_id = self.extract_spreadsheet_id(url)
            if not ss_id:
                debug['error'] = 'スプレッドシートIDを抽出できません'
                return [], debug

            spreadsheet = self.gc.open_by_key(ss_id)
            debug['spreadsheet_name'] = spreadsheet.title

            # 利用可能なシート一覧を取得
            worksheets = spreadsheet.worksheets()
            debug['available_sheets'] = [ws.title for ws in worksheets]

            # シート取得
            if sheet_name:
                try:
                    worksheet = spreadsheet.worksheet(sheet_name)
                except gspread.exceptions.WorksheetNotFound:
                    debug['error'] = f'指定されたシート「{sheet_name}」が見つかりません'
                    debug['actual_sheet_name'] = '(見つからない)'
                    return [], debug
            else:
                worksheet = worksheets[0]

            debug['actual_sheet_name'] = worksheet.title
            urls = []

            # 列指定がある場合: ハイパーリンクURLを取得
            if target_column:
                # Google Sheets API v4を使用してリッチテキスト（ハイパーリンク）を取得
                result = self.sheets_service.spreadsheets().get(
                    spreadsheetId=ss_id,
                    ranges=f'{worksheet.title}!{chr(64+target_column)}:{chr(64+target_column)}',
                    fields='sheets.data.rowData.values.hyperlink'
                ).execute()

                sheets = result.get('sheets', [])
                for sheet in sheets:
                    for row_data in sheet.get('data', [{}])[0].get('rowData', []):
                        for value in row_data.get('values', []):
                            link_url = value.get('hyperlink')
                            if link_url and 'docs.google.com/spreadsheets' in link_url:
                                urls.append(link_url.strip())
            else:
                # 列指定なし: テキスト内のURLを正規表現で抽出
                all_values = worksheet.get_all_values()
                for row in all_values:
                    for cell in row:
                        if cell and isinstance(cell, str):
                            matches = self.URL_PATTERN.findall(cell)
                            for match in matches:
                                trimmed = match.strip()
                                if len(trimmed) > 15:
                                    urls.append(trimmed)

            # 重複削除
            return list(set(urls)), debug

        except Exception as e:
            debug['error'] = str(e)
            return [], debug

    def fetch_sheet_as_export(
        self,
        ss_id: str,
        sheet_id: int,
        file_name: str,
        export_format: str = 'html',
        retry_count: int = 3
    ) -> bytes:
        """
        指定したシートを指定形式でエクスポート（リトライ・レートリミット対策）

        Args:
            ss_id: スプレッドシートID
            sheet_id: シートID（gid）
            file_name: 出力ファイル名（ログ用）
            export_format: 出力形式（'html' または 'csv'）
            retry_count: リトライ回数

        Returns:
            エクスポートされたバイトデータ
        """
        export_url = f"https://docs.google.com/spreadsheets/d/{ss_id}/export?format={export_format}&gid={sheet_id}"

        for retry in range(retry_count):
            try:
                # OAuth2トークンを使用してリクエスト
                headers = {
                    'Authorization': f'Bearer {self.credentials.token}'
                }

                # トークンが期限切れの場合は更新
                if not self.credentials.valid:
                    self.credentials.refresh(requests.Request())
                    headers['Authorization'] = f'Bearer {self.credentials.token}'

                response = requests.get(export_url, headers=headers, timeout=60)

                # 成功
                if response.status_code == 200:
                    return response.content

                # レートリミット（429）またはサーバーエラー（5xx）の場合は待機してリトライ
                if response.status_code == 429 or response.status_code >= 500:
                    wait_sec = 3 * (retry + 1)  # 3秒、6秒、9秒と増加
                    self.log('warn', f'[リトライ] {file_name}: HTTPコード{response.status_code}、{wait_sec}秒待機後に再試行...')
                    time.sleep(wait_sec)
                    continue

                # その他のエラーはそのまま返す
                self.log('error', f'{file_name}: HTTPコード{response.status_code}')
                return response.content

            except Exception as e:
                if retry < retry_count - 1:
                    wait_sec = 3 * (retry + 1)
                    self.log('warn', f'[リトライ] {file_name}: エラー発生、{wait_sec}秒待機後に再試行... ({e})')
                    time.sleep(wait_sec)
                else:
                    raise

        # リトライ上限到達時
        raise Exception(f'シート取得に失敗しました（リトライ上限）: {file_name}')

    def process_spreadsheets(
        self,
        list_sheet_url: str,
        export_format: str = 'html',
        delay_ms: int = 1000,
        debug_limit: Optional[int] = None
    ) -> Tuple[BytesIO, str]:
        """
        メイン処理：URLから設計書を探索しZIPを作成

        Args:
            list_sheet_url: 一覧シートのURL
            export_format: 出力形式（'html' または 'csv'）
            delay_ms: 各エクスポート間のディレイ（ミリ秒）
            debug_limit: デバッグ用の処理件数制限

        Returns:
            (ZIPファイルのBytesIO, ZIPファイル名)
        """
        zip_buffer = BytesIO()
        processed_urls = set()  # 重複処理防止

        self.log('info', '一覧シートからURLを取得中...')

        # 1. 一覧シートから詳細シートURLを取得（B列のハイパーリンクから）
        detail_urls, detail_debug = self.get_urls_from_sheet(list_sheet_url, '総合進捗管理', 2)

        # デバッグ情報を表示
        self.log('info', f'スプレッドシート: {detail_debug["spreadsheet_name"]}')
        self.log('info', f'要求シート: {detail_debug["requested_sheet_name"]}')
        self.log('info', f'実際のシート: {detail_debug["actual_sheet_name"]}')
        self.log('info', f'対象列: {detail_debug["target_column"]}')
        self.log('info', f'抽出方法: {detail_debug["extraction_method"]}')
        self.log('info', f'利用可能なシート: {", ".join(detail_debug["available_sheets"])}')

        if detail_debug['error']:
            self.log('error', f'エラー: {detail_debug["error"]}')

        self.stats['detail_sheets_total'] = len(detail_urls)
        self.log('info', f'詳細シートを{len(detail_urls)}件検出しました')

        # デバッグ: 取得したURL一覧を表示
        for index, url in enumerate(detail_urls):
            self.log('info', f'  [{index + 1}] 検出URL', url=url)

        # デバッグリミット適用
        urls_to_process = detail_urls[:debug_limit] if debug_limit else detail_urls

        if debug_limit and debug_limit < len(detail_urls):
            self.log('warn', f'⚠️ デバッグモード：最初の{debug_limit}件のみ処理します（全{len(detail_urls)}件中）')
        else:
            self.log('info', f'全{len(detail_urls)}件を処理します')

        # フォーマット設定
        format_label = 'CSV' if export_format == 'csv' else 'HTML'
        file_extension = '.csv' if export_format == 'csv' else '.html'

        with zipfile.ZipFile(zip_buffer, 'w', zipfile.ZIP_DEFLATED) as zip_file:
            for index, detail_url in enumerate(urls_to_process):
                trimmed_detail_url = detail_url.strip()

                self.log('info', f'\n--- [{index + 1}/{len(urls_to_process)}] 詳細シート処理開始 ---')
                self.log('info', '処理対象URL', url=trimmed_detail_url)

                # URL検証
                if not trimmed_detail_url or not trimmed_detail_url.startswith('https://docs.google.com/spreadsheets/'):
                    self.log('warn', 'URL検証失敗: 無効な形式', url=trimmed_detail_url)
                    self.stats['errors'] += 1
                    continue

                self.log('info', 'URL検証: OK')

                # 2. 詳細シートから「進捗管理表」シートを特定
                self.log('info', 'スプレッドシートにアクセス中...')

                try:
                    ss_id = self.extract_spreadsheet_id(trimmed_detail_url)
                    if not ss_id:
                        self.log('error', 'スプレッドシートIDを抽出できません', url=trimmed_detail_url)
                        self.stats['errors'] += 1
                        continue

                    spreadsheet = self.gc.open_by_key(ss_id)
                    ss_name = spreadsheet.title
                    self.log('success', 'アクセス成功', url=trimmed_detail_url, name=ss_name)

                    # 「進捗管理表」シートを探す
                    progress_sheet = None
                    try:
                        progress_sheet = spreadsheet.worksheet('進捗管理表')
                    except gspread.exceptions.WorksheetNotFound:
                        pass

                    if progress_sheet:
                        self.stats['detail_sheets_success'] += 1
                        self.log('info', '「進捗管理表」シート: 見つかりました')

                        # 3. 進捗管理表から設計書URLを取得
                        self.log('info', '「進捗管理表」から設計書URLを抽出中...')
                        design_urls, design_debug = self.get_urls_from_sheet(trimmed_detail_url, '進捗管理表')
                        self.log('success', f'設計書URLを{len(design_urls)}件検出しました')
                        self.stats['design_docs_total'] += len(design_urls)

                        for d_index, design_url in enumerate(design_urls):
                            trimmed_design_url = design_url.strip()

                            self.log('info', f'  --- 設計書 [{d_index + 1}/{len(design_urls)}] ---')
                            self.log('info', '  処理対象URL', url=trimmed_design_url)

                            # URL検証
                            if not trimmed_design_url or not trimmed_design_url.startswith('https://docs.google.com/spreadsheets/'):
                                self.log('warn', '  設計書URL検証失敗: 無効な形式', url=trimmed_design_url)
                                self.stats['errors'] += 1
                                continue

                            self.log('info', '  設計書URL検証: OK')

                            if trimmed_design_url in processed_urls:
                                self.log('info', '  スキップ（重複）', url=trimmed_design_url)
                                continue

                            processed_urls.add(trimmed_design_url)

                            # 4. 設計書スプシの全シートをHTML化
                            self.log('info', '  設計書にアクセス中...')

                            try:
                                normalized_url = self.normalize_spreadsheet_url(trimmed_design_url)
                                self.log('info', '  正規化URL', url=normalized_url)

                                design_ss_id = self.extract_spreadsheet_id(normalized_url)
                                if not design_ss_id:
                                    self.log('error', '  スプレッドシートIDを抽出できません', url=normalized_url)
                                    self.stats['errors'] += 1
                                    continue

                                design_spreadsheet = self.gc.open_by_key(design_ss_id)
                                design_ss_name = design_spreadsheet.title
                                design_worksheets = design_spreadsheet.worksheets()

                                self.log('success', '  アクセス成功', url=trimmed_design_url, name=design_ss_name)
                                self.log('info', f'  シート数: {len(design_worksheets)}枚')

                                # フォルダ名（設計書ごと）
                                folder_name = self.sanitize_filename(design_ss_name)

                                for sheet_index, worksheet in enumerate(design_worksheets):
                                    sheet_id = worksheet.id
                                    sheet_name = worksheet.title
                                    # フォルダ構成でファイル名を設定
                                    file_name = f"{folder_name}/{self.sanitize_filename(sheet_name)}{file_extension}"

                                    self.log('info', f'    シート「{sheet_name}」を{format_label}化中...')

                                    try:
                                        content = self.fetch_sheet_as_export(design_ss_id, sheet_id, file_name, export_format)
                                        zip_file.writestr(file_name, content)
                                        self.stats['files_generated'] += 1
                                        self.log('success', f'    {format_label}生成成功: {file_name}')

                                        # レートリミット対策：各エクスポート後にディレイ（最後のシートは不要）
                                        if sheet_index < len(design_worksheets) - 1:
                                            time.sleep(delay_ms / 1000.0)

                                    except Exception as e:
                                        self.log('error', f'    {format_label}生成失敗: {file_name} - {e}')
                                        self.stats['errors'] += 1

                            except Exception as e:
                                self.log('error', '  設計書アクセス失敗', url=trimmed_design_url)
                                self.log('error', f'  エラー詳細: {e}')
                                self.stats['errors'] += 1
                    else:
                        self.log('warn', '「進捗管理表」シートが見つかりません', url=trimmed_detail_url, name=ss_name)

                except Exception as e:
                    self.log('error', '詳細シートアクセス失敗', url=trimmed_detail_url)
                    self.log('error', f'エラー詳細: {e}')
                    self.stats['errors'] += 1

        if self.stats['files_generated'] == 0:
            raise Exception('設計書が1件も見つかりませんでした。アクセス権限を確認してください。')

        # ZIP作成完了
        self.log('success', f'ZIP作成完了！ ({self.stats["files_generated"]}ファイル)')

        # ZIPファイル名
        zip_file_name = f"{self.sanitize_filename(detail_debug['spreadsheet_name'])}.zip"

        zip_buffer.seek(0)
        return zip_buffer, zip_file_name


def main():
    """メイン関数"""
    # コマンドライン引数のパース
    parser = argparse.ArgumentParser(
        description='データQAまとめからマスタデータ書一括ダウンロードスクリプト',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
使用例:
  python download_masterdata_docs.py \\
    --url "https://docs.google.com/spreadsheets/d/..." \\
    --output output.zip \\
    --format html

注意事項:
  - credentials.jsonはスクリプトと同じディレクトリに配置してください
  - または --credentials オプションでパスを指定してください
        """
    )

    parser.add_argument(
        '--url',
        required=True,
        help='一覧シート（データQAまとめ）のURL'
    )

    parser.add_argument(
        '--output',
        default='output.zip',
        help='出力ZIPファイル名（デフォルト: output.zip）'
    )

    parser.add_argument(
        '--format',
        choices=['html', 'csv'],
        default='html',
        help='出力形式（デフォルト: html）'
    )

    parser.add_argument(
        '--credentials',
        help='サービスアカウントのcredentials.jsonパス（デフォルト: スクリプトと同じディレクトリ）'
    )

    parser.add_argument(
        '--delay',
        type=int,
        default=1000,
        help='各エクスポート間のディレイ（ミリ秒、デフォルト: 1000）'
    )

    parser.add_argument(
        '--debug-limit',
        type=int,
        help='デバッグ用の処理件数制限'
    )

    args = parser.parse_args()

    # credentials.jsonのパスを解決
    if args.credentials:
        credentials_path = args.credentials
    else:
        # スクリプトと同じディレクトリ
        script_dir = Path(__file__).parent
        credentials_path = script_dir / 'credentials.json'

    if not os.path.exists(credentials_path):
        print(f'[ERROR] credentials.jsonが見つかりません: {credentials_path}', file=sys.stderr)
        print(f'[INFO] --credentials オプションでパスを指定するか、{Path(__file__).parent}/credentials.json に配置してください', file=sys.stderr)
        sys.exit(1)

    try:
        # ダウンローダー初期化
        downloader = MasterdataDocsDownloader(str(credentials_path))

        # 処理実行
        zip_buffer, zip_file_name = downloader.process_spreadsheets(
            args.url,
            export_format=args.format,
            delay_ms=args.delay,
            debug_limit=args.debug_limit
        )

        # ファイル保存
        output_path = args.output
        with open(output_path, 'wb') as f:
            f.write(zip_buffer.read())

        # 統計情報表示
        stats = downloader.stats
        print('\n' + '='*60)
        print('処理結果：')
        print(f'  詳細シート: {stats["detail_sheets_success"]}/{stats["detail_sheets_total"]}件成功')
        print(f'  設計書: {stats["design_docs_total"]}件検出')
        print(f'  生成ファイル: {stats["files_generated"]}件')
        print(f'  エラー/警告: {stats["errors"]}件')
        print(f'  出力ファイル: {output_path}')
        print('='*60)

    except Exception as e:
        print(f'[ERROR] 処理中にエラーが発生: {e}', file=sys.stderr)
        sys.exit(1)


if __name__ == '__main__':
    main()
