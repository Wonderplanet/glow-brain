#!/usr/bin/env python3
"""
Google SpreadsheetをXLSX形式でダウンロードするスクリプト

URL/IDを複数指定でき、GoogleドライブのフォルダhierarchyをそのままローカルPCに再現して保存します。

使用例:
  # URLで指定（複数可）
  uv run python gspread_to_xlsx.py \\
    "https://docs.google.com/spreadsheets/d/ABC123/edit" \\
    "https://docs.google.com/spreadsheets/d/XYZ456/edit" \\
    --credentials credentials.json

  # スプシIDで指定
  uv run python gspread_to_xlsx.py ABC123 XYZ456 --credentials credentials.json
"""

import argparse
import io
import os
import re
import sys
import time
from pathlib import Path
from typing import List, Optional

from google.oauth2 import service_account
from googleapiclient.discovery import build
from googleapiclient.http import MediaIoBaseDownload


class SpreadsheetDownloader:
    """Google SpreadsheetをXLSX形式でダウンロードするクラス"""

    SCOPES = ['https://www.googleapis.com/auth/drive.readonly']
    XLSX_MIME = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'

    def __init__(self, credentials_path: str):
        """
        サービスアカウント認証・Drive API初期化

        Args:
            credentials_path: サービスアカウントのcredentials.jsonパス
        """
        self.credentials = service_account.Credentials.from_service_account_file(
            credentials_path,
            scopes=self.SCOPES
        )
        self.drive_service = build('drive', 'v3', credentials=self.credentials)

    def log(self, level: str, message: str):
        """ログ出力"""
        print(f"[{level.upper()}] {message}")

    def extract_file_id(self, url_or_id: str) -> str:
        """
        URLまたはIDからファイルIDを抽出

        Args:
            url_or_id: SpreadsheetのURLまたはID

        Returns:
            ファイルID
        """
        match = re.search(r'/spreadsheets/d/([a-zA-Z0-9_-]+)', url_or_id)
        if match:
            return match.group(1)
        # URLでなければIDとしてそのまま返す
        return url_or_id.strip()

    def get_file_name(self, file_id: str) -> str:
        """
        ファイル名を取得

        Args:
            file_id: ファイルID

        Returns:
            ファイル名
        """
        meta = self.drive_service.files().get(
            fileId=file_id,
            fields='name',
            supportsAllDrives=True
        ).execute()
        return meta['name']

    def get_drive_folder_path(self, file_id: str) -> Path:
        """
        Drive上のフォルダ階層を辿ってパスを構築

        ルートフォルダ（マイドライブ等）は除外し、その直下から始まるパスを返す。

        例: マイドライブ > GLOW > 080_運営 > ガチャ設計書
            → Path("GLOW", "080_運営")

        Args:
            file_id: ファイルID

        Returns:
            Driveのフォルダパス（スプシ名・ルートフォルダ名を含まない）
        """
        parts = []
        current_id = file_id
        first = True

        while True:
            meta = self.drive_service.files().get(
                fileId=current_id,
                fields='name,parents',
                supportsAllDrives=True
            ).execute()
            parents = meta.get('parents', [])

            # スプシ自体（first=True）とルートフォルダ（parents=[]）は除外
            if not first and parents:
                parts.insert(0, meta['name'])

            if not parents:
                break

            current_id = parents[0]
            first = False

        return Path(*parts) if parts else Path('.')

    def export_as_xlsx(self, file_id: str, file_name: str, retry_count: int = 3) -> bytes:
        """
        Drive APIでXLSX形式にエクスポート（リトライ・レートリミット対策）

        Args:
            file_id: ファイルID
            file_name: ファイル名（ログ用）
            retry_count: リトライ回数

        Returns:
            XLSXファイルのバイトデータ
        """
        for retry in range(retry_count):
            try:
                request = self.drive_service.files().export_media(
                    fileId=file_id,
                    mimeType=self.XLSX_MIME
                )
                buf = io.BytesIO()
                downloader = MediaIoBaseDownload(buf, request)
                done = False
                while not done:
                    _, done = downloader.next_chunk()
                return buf.getvalue()

            except Exception as e:
                error_msg = str(e)
                is_rate_limit = '429' in error_msg or 'rateLimitExceeded' in error_msg.lower()

                if retry < retry_count - 1:
                    wait_sec = 3 * (retry + 1)
                    reason = 'レートリミット' if is_rate_limit else 'エラー発生'
                    self.log('warn', f'[リトライ] {file_name}: {reason}、{wait_sec}秒待機後に再試行... ({e})')
                    time.sleep(wait_sec)
                else:
                    raise

        raise Exception(f'XLSXエクスポートに失敗しました（リトライ上限）: {file_name}')

    def download(self, url_or_id: str, output_dir: Path) -> Path:
        """
        1ファイルをダウンロード・保存して保存先パスを返す

        Args:
            url_or_id: SpreadsheetのURLまたはID
            output_dir: 出力先ベースディレクトリ

        Returns:
            保存先パス
        """
        # 1. IDを抽出
        file_id = self.extract_file_id(url_or_id)
        self.log('info', f'ファイルID: {file_id}')

        # 2. ファイル名 + フォルダパスを取得
        file_name = self.get_file_name(file_id)
        self.log('info', f'ファイル名: {file_name}')

        folder_path = self.get_drive_folder_path(file_id)
        self.log('info', f'Driveフォルダパス: {folder_path}')

        # 3. XLSX エクスポート
        self.log('info', 'XLSXエクスポート中...')
        xlsx_data = self.export_as_xlsx(file_id, file_name)

        # 4. output_dir / folder_path / "{name}.xlsx" に保存
        safe_name = re.sub(r'[\\/:*?"<>|]', '_', file_name)
        save_path = output_dir / folder_path / f'{safe_name}.xlsx'
        save_path.parent.mkdir(parents=True, exist_ok=True)
        save_path.write_bytes(xlsx_data)

        self.log('info', f'保存完了: {save_path}')
        return save_path


def main():
    """メイン関数"""
    script_dir = Path(__file__).parent
    # domain/tools/google-drive/gspread-to-xlsx/ → domain/ → raw-data/google-drive/spread-sheet/
    default_output_dir = script_dir.parent.parent.parent / 'raw-data' / 'google-drive' / 'spread-sheet'

    parser = argparse.ArgumentParser(
        description='Google SpreadsheetをXLSX形式でダウンロードするスクリプト',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
使用例:
  # URLで指定（複数可）
  uv run python gspread_to_xlsx.py \\
    "https://docs.google.com/spreadsheets/d/ABC123/edit" \\
    "https://docs.google.com/spreadsheets/d/XYZ456/edit" \\
    --credentials credentials.json

  # スプシIDで指定
  uv run python gspread_to_xlsx.py ABC123 XYZ456 --credentials credentials.json

  # 出力先を明示指定
  uv run python gspread_to_xlsx.py ABC123 --output-dir /path/to/output
        """
    )

    parser.add_argument(
        'targets',
        nargs='+',
        help='SpreadsheetのURLまたはID（複数指定可）'
    )

    parser.add_argument(
        '--credentials',
        help='サービスアカウントのcredentials.jsonパス（デフォルト: スクリプトと同じディレクトリ）'
    )

    parser.add_argument(
        '--output-dir',
        help=f'出力先ディレクトリ（デフォルト: {default_output_dir}）'
    )

    args = parser.parse_args()

    # credentials.jsonのパスを解決
    if args.credentials:
        credentials_path = Path(args.credentials)
    else:
        credentials_path = script_dir / 'credentials.json'

    if not credentials_path.exists():
        print(f'[ERROR] credentials.jsonが見つかりません: {credentials_path}', file=sys.stderr)
        print(
            f'[INFO] --credentials オプションでパスを指定するか、'
            f'{script_dir}/credentials.json に配置してください',
            file=sys.stderr
        )
        sys.exit(1)

    # 出力ディレクトリを解決
    output_dir = Path(args.output_dir) if args.output_dir else default_output_dir
    output_dir.mkdir(parents=True, exist_ok=True)

    try:
        downloader = SpreadsheetDownloader(str(credentials_path))

        success_count = 0
        error_count = 0
        saved_paths: List[Path] = []

        for i, target in enumerate(args.targets):
            print(f'\n--- [{i + 1}/{len(args.targets)}] 処理中 ---')
            print(f'[INFO] 対象: {target}')

            try:
                saved_path = downloader.download(target, output_dir)
                saved_paths.append(saved_path)
                success_count += 1
            except Exception as e:
                print(f'[ERROR] 処理失敗: {target} - {e}', file=sys.stderr)
                error_count += 1

        # 結果表示
        print('\n' + '=' * 60)
        print('処理結果:')
        print(f'  成功: {success_count}件')
        print(f'  失敗: {error_count}件')
        if saved_paths:
            print('  保存先:')
            for p in saved_paths:
                print(f'    {p}')
        print('=' * 60)

        if error_count > 0:
            sys.exit(1)

    except Exception as e:
        print(f'[ERROR] 処理中にエラーが発生: {e}', file=sys.stderr)
        sys.exit(1)


if __name__ == '__main__':
    main()
