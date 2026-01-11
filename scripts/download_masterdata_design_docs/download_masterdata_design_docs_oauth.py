#!/usr/bin/env python3
"""
GLOWマスタデータ設計書ダウンローダー（OAuth版）

Googleスプレッドシートから設計書をHTMLでダウンロードし、
glow-brainリポジトリにPRを作成します。

OAuth 2.0認証を使用します。
初回実行時にブラウザで認証し、token.jsonにトークンを保存します。
"""

import argparse
import json
import os
import pickle
import re
import subprocess
import sys
import tempfile
import zipfile
from pathlib import Path
from typing import List, Dict, Any, Optional

from google.auth.transport.requests import Request
from google.oauth2.credentials import Credentials
from google_auth_oauthlib.flow import InstalledAppFlow
from googleapiclient.discovery import build
import requests


# 設定
SCOPES = [
    'https://www.googleapis.com/auth/spreadsheets.readonly',
    'https://www.googleapis.com/auth/drive.readonly'
]

TOKEN_FILE = 'token.pickle'


class MasterdataDownloader:
    """マスタデータ設計書ダウンローダー（OAuth版）"""

    def __init__(self, credentials_path: str, repo_path: str):
        """
        初期化

        Args:
            credentials_path: OAuth 2.0クライアントIDのJSONファイルパス
            repo_path: glow-brainリポジトリのパス
        """
        self.repo_path = Path(repo_path)
        self.credentials = self._get_oauth_credentials(credentials_path)

        # Google API クライアント
        self.sheets_service = build('sheets', 'v4', credentials=self.credentials)
        self.drive_service = build('drive', 'v3', credentials=self.credentials)

    def _get_oauth_credentials(self, credentials_path: str) -> Credentials:
        """
        OAuth 2.0認証を実行

        初回実行時はブラウザで認証し、トークンをtoken.pickleに保存。
        2回目以降は保存されたトークンを使用。

        Args:
            credentials_path: OAuth 2.0クライアントID JSONファイルパス

        Returns:
            認証情報
        """
        creds = None
        token_path = Path(TOKEN_FILE)

        # 保存済みトークンがあれば読み込む
        if token_path.exists():
            print("💾 保存済みのトークンを読み込んでいます...")
            with open(token_path, 'rb') as token:
                creds = pickle.load(token)

        # 有効な認証情報がない場合
        if not creds or not creds.valid:
            if creds and creds.expired and creds.refresh_token:
                # トークンをリフレッシュ
                print("🔄 トークンを更新しています...")
                creds.refresh(Request())
            else:
                # 新規認証
                print("🌐 ブラウザで認証を行います...")
                print("   ブラウザが自動的に開きます。Googleアカウントでログインしてください。")
                flow = InstalledAppFlow.from_client_secrets_file(
                    credentials_path, SCOPES)
                creds = flow.run_local_server(port=0)

            # 認証情報を保存
            print("💾 トークンを保存しています...")
            with open(token_path, 'wb') as token:
                pickle.dump(creds, token)
            print(f"   ✅ トークンを {token_path} に保存しました")

        return creds

    def extract_spreadsheet_id(self, url: str) -> str:
        """
        スプレッドシートURLからIDを抽出

        Args:
            url: スプレッドシートURL

        Returns:
            スプレッドシートID
        """
        match = re.search(r'/d/([a-zA-Z0-9-_]+)', url)
        if not match:
            raise ValueError(f"無効なスプレッドシートURL: {url}")
        return match.group(1)

    def get_cell_value(self, spreadsheet_id: str, range_name: str) -> str:
        """
        セルの値を取得

        Args:
            spreadsheet_id: スプレッドシートID
            range_name: セル範囲（例: "B3"）

        Returns:
            セルの値
        """
        result = self.sheets_service.spreadsheets().values().get(
            spreadsheetId=spreadsheet_id,
            range=range_name
        ).execute()

        values = result.get('values', [])
        if not values or not values[0]:
            return ""

        return values[0][0]

    def extract_release_key(self, cell_value: str) -> str:
        """
        セル値からリリースキーを抽出

        Args:
            cell_value: セルの値（例: "リリースキー:20251202"）

        Returns:
            リリースキー
        """
        match = re.search(r'リリースキー[:\s]*([0-9]+)', cell_value)
        if not match:
            raise ValueError(
                f"リリースキーが見つかりませんでした。セル値: '{cell_value}'\n"
                f"形式を確認してください。例: リリースキー:20251202"
            )
        return match.group(1)

    def get_all_rows(self, spreadsheet_id: str, sheet_name: str = None) -> List[List[str]]:
        """
        シートの全行を取得

        Args:
            spreadsheet_id: スプレッドシートID
            sheet_name: シート名（Noneの場合は最初のシート）

        Returns:
            行データのリスト
        """
        range_name = f"{sheet_name}!A:ZZ" if sheet_name else "A:ZZ"

        result = self.sheets_service.spreadsheets().values().get(
            spreadsheetId=spreadsheet_id,
            range=range_name
        ).execute()

        return result.get('values', [])

    def extract_urls_from_rows(self, rows: List[List[str]]) -> List[str]:
        """
        行データからスプレッドシートURLを抽出

        Args:
            rows: 行データのリスト

        Returns:
            重複を除いたURLのリスト
        """
        urls = set()

        for row in rows:
            for cell in row:
                if isinstance(cell, str) and 'docs.google.com/spreadsheets' in cell:
                    match = re.search(
                        r'https://docs\.google\.com/spreadsheets/d/([a-zA-Z0-9-_]+)',
                        cell
                    )
                    if match:
                        urls.add(cell)

        return list(urls)

    def get_spreadsheet_metadata(self, spreadsheet_id: str) -> Dict[str, Any]:
        """
        スプレッドシートのメタデータを取得

        Args:
            spreadsheet_id: スプレッドシートID

        Returns:
            メタデータ
        """
        return self.sheets_service.spreadsheets().get(
            spreadsheetId=spreadsheet_id
        ).execute()

    def find_sheet_by_name(self, metadata: Dict[str, Any], sheet_name: str) -> Optional[Dict[str, Any]]:
        """
        名前でシートを検索

        Args:
            metadata: スプレッドシートのメタデータ
            sheet_name: シート名

        Returns:
            シート情報（見つからない場合はNone）
        """
        sheets = metadata.get('sheets', [])
        for sheet in sheets:
            properties = sheet.get('properties', {})
            if properties.get('title') == sheet_name:
                return sheet
        return None

    def download_sheet_as_html(
        self,
        spreadsheet_id: str,
        sheet_id: int,
        output_dir: Path
    ) -> List[Path]:
        """
        シートをHTML形式でダウンロード

        Args:
            spreadsheet_id: スプレッドシートID
            sheet_id: シートID
            output_dir: 出力ディレクトリ

        Returns:
            保存したHTMLファイルのパスリスト
        """
        # Google Drive API でエクスポート（ZIP形式）
        url = (
            f'https://www.googleapis.com/drive/v3/files/{spreadsheet_id}/export'
            f'?mimeType=application/zip&gid={sheet_id}'
        )

        # 認証ヘッダー
        self.credentials.refresh(Request())
        headers = {'Authorization': f'Bearer {self.credentials.token}'}

        # ダウンロード
        response = requests.get(url, headers=headers)
        response.raise_for_status()

        # 一時ファイルに保存
        with tempfile.NamedTemporaryFile(suffix='.zip', delete=False) as tmp_file:
            tmp_file.write(response.content)
            tmp_zip_path = tmp_file.name

        try:
            # ZIP展開
            html_files = []
            with zipfile.ZipFile(tmp_zip_path, 'r') as zip_ref:
                for file_info in zip_ref.filelist:
                    if file_info.filename.endswith('.html'):
                        # HTMLファイルを出力ディレクトリに展開
                        extracted_path = output_dir / Path(file_info.filename).name
                        with zip_ref.open(file_info) as source:
                            with open(extracted_path, 'wb') as target:
                                target.write(source.read())
                        html_files.append(extracted_path)
                        print(f"  保存: {extracted_path}")

            return html_files

        finally:
            # 一時ファイル削除
            os.unlink(tmp_zip_path)

    def sanitize_filename(self, name: str) -> str:
        """
        ファイル名として使用可能な形式に変換

        Args:
            name: 変換前の名前

        Returns:
            変換後の名前
        """
        return re.sub(r'[/:*?"<>|]', '_', name).replace(' ', '_')

    def create_git_branch(self, release_key: str) -> str:
        """
        Gitブランチを作成

        Args:
            release_key: リリースキー

        Returns:
            ブランチ名
        """
        branch_name = f"masterdata-docs-{release_key}"

        # リポジトリディレクトリに移動
        original_dir = os.getcwd()
        os.chdir(self.repo_path)

        try:
            # ブランチ作成または切り替え
            result = subprocess.run(
                ['git', 'checkout', '-b', branch_name],
                capture_output=True,
                text=True
            )

            # 既にブランチが存在する場合は切り替え
            if result.returncode != 0:
                subprocess.run(
                    ['git', 'checkout', branch_name],
                    check=True,
                    capture_output=True,
                    text=True
                )

            return branch_name

        finally:
            os.chdir(original_dir)

    def commit_and_push(self, release_key: str, branch_name: str):
        """
        変更をコミットしてプッシュ

        Args:
            release_key: リリースキー
            branch_name: ブランチ名
        """
        original_dir = os.getcwd()
        os.chdir(self.repo_path)

        try:
            # ファイルを追加
            add_path = f"マスターデータ/リリース/{release_key}/raw/"
            subprocess.run(
                ['git', 'add', add_path],
                check=True,
                capture_output=True,
                text=True
            )

            # コミット
            commit_message = (
                f"マスタデータ設計書追加: リリースキー {release_key}\n\n"
                f"自動生成されたコミットです。"
            )
            subprocess.run(
                ['git', 'commit', '-m', commit_message],
                check=True,
                capture_output=True,
                text=True
            )

            # プッシュ
            subprocess.run(
                ['git', 'push', '-u', 'origin', branch_name],
                check=True,
                capture_output=True,
                text=True
            )

        finally:
            os.chdir(original_dir)

    def create_pull_request(self, release_key: str, branch_name: str):
        """
        Pull Requestを作成

        Args:
            release_key: リリースキー
            branch_name: ブランチ名
        """
        original_dir = os.getcwd()
        os.chdir(self.repo_path)

        try:
            title = f"マスタデータ設計書追加: リリースキー {release_key}"
            body = (
                f"リリースキー {release_key} の設計書HTMLファイルを追加しました。\n\n"
                f"自動生成されたPRです。"
            )

            subprocess.run(
                [
                    'gh', 'pr', 'create',
                    '--title', title,
                    '--body', body,
                    '--base', 'main',
                    '--head', branch_name
                ],
                check=True,
                capture_output=True,
                text=True
            )

            print(f"\n✅ Pull Request作成完了")

        finally:
            os.chdir(original_dir)

    def run(self, index_sheet_url: str):
        """
        メイン処理を実行

        Args:
            index_sheet_url: 一覧シートのURL
        """
        print("=" * 80)
        print("GLOWマスタデータ設計書ダウンローダー（OAuth版）")
        print("=" * 80)
        print()

        # 1. 一覧シートからリリースキーを取得
        print("📋 一覧シートからリリースキーを取得中...")
        index_sheet_id = self.extract_spreadsheet_id(index_sheet_url)
        cell_value = self.get_cell_value(index_sheet_id, "B3")
        release_key = self.extract_release_key(cell_value)
        print(f"  リリースキー: {release_key}")
        print()

        # 出力ディレクトリ
        output_base_dir = self.repo_path / "マスターデータ" / "リリース" / release_key / "raw"
        output_base_dir.mkdir(parents=True, exist_ok=True)

        # 2. 一覧シートから詳細シートURLを取得
        print("🔍 一覧シートから詳細シートURLを取得中...")
        index_rows = self.get_all_rows(index_sheet_id)
        detail_sheet_urls = self.extract_urls_from_rows(index_rows)
        print(f"  詳細シート数: {len(detail_sheet_urls)}")
        print()

        # 3. 各詳細シートを処理
        all_design_doc_urls = set()

        for idx, detail_url in enumerate(detail_sheet_urls, 1):
            print(f"📄 詳細シート {idx}/{len(detail_sheet_urls)} を処理中...")
            detail_sheet_id = self.extract_spreadsheet_id(detail_url)

            # 進捗管理表シートを探す
            detail_metadata = self.get_spreadsheet_metadata(detail_sheet_id)
            progress_sheet = self.find_sheet_by_name(detail_metadata, "進捗管理表")

            if not progress_sheet:
                print(f"  ⚠️  進捗管理表シートが見つかりません。スキップします。")
                continue

            # 進捗管理表からデータ取得
            progress_rows = self.get_all_rows(detail_sheet_id, "進捗管理表")
            design_doc_urls = self.extract_urls_from_rows(progress_rows)

            print(f"  設計書URL数: {len(design_doc_urls)}")
            all_design_doc_urls.update(design_doc_urls)

        print()
        print(f"📚 設計書総数: {len(all_design_doc_urls)}")
        print()

        # 4. 各設計書を処理
        for idx, design_url in enumerate(all_design_doc_urls, 1):
            print(f"📖 設計書 {idx}/{len(all_design_doc_urls)} を処理中...")
            design_sheet_id = self.extract_spreadsheet_id(design_url)

            # メタデータ取得
            design_metadata = self.get_spreadsheet_metadata(design_sheet_id)
            spreadsheet_title = design_metadata.get('properties', {}).get('title', f'spreadsheet_{design_sheet_id}')
            safe_name = self.sanitize_filename(spreadsheet_title)

            print(f"  スプレッドシート: {spreadsheet_title}")

            # 各シートをダウンロード
            sheets = design_metadata.get('sheets', [])
            for sheet in sheets:
                sheet_props = sheet.get('properties', {})
                sheet_name = sheet_props.get('title')
                sheet_id = sheet_props.get('sheetId')

                print(f"  📥 シート: {sheet_name}")

                # 出力ディレクトリ
                sheet_output_dir = output_base_dir / safe_name
                sheet_output_dir.mkdir(parents=True, exist_ok=True)

                # HTMLダウンロード
                self.download_sheet_as_html(design_sheet_id, sheet_id, sheet_output_dir)

            print()

        # 5. Git操作とPR作成
        print("🔧 Gitブランチを作成中...")
        branch_name = self.create_git_branch(release_key)
        print(f"  ブランチ: {branch_name}")
        print()

        print("💾 変更をコミット・プッシュ中...")
        self.commit_and_push(release_key, branch_name)
        print("  ✅ コミット・プッシュ完了")
        print()

        print("🚀 Pull Requestを作成中...")
        self.create_pull_request(release_key, branch_name)
        print()

        print("=" * 80)
        print("✨ 全ての処理が完了しました！")
        print("=" * 80)


def main():
    """メイン関数"""
    parser = argparse.ArgumentParser(
        description='GLOWマスタデータ設計書ダウンローダー（OAuth版）'
    )
    parser.add_argument(
        'index_sheet_url',
        help='一覧シートのURL'
    )
    parser.add_argument(
        '--credentials',
        default='credentials.json',
        help='OAuth 2.0クライアントID JSONファイルパス（デフォルト: credentials.json）'
    )
    parser.add_argument(
        '--repo-path',
        default='/Users/junki.mizutani/Documents/workspace/glow/glow-brain',
        help='glow-brainリポジトリのパス'
    )

    args = parser.parse_args()

    # 認証情報ファイルの存在確認
    if not os.path.exists(args.credentials):
        print(f"❌ エラー: 認証情報ファイルが見つかりません: {args.credentials}", file=sys.stderr)
        print(f"\n📖 OAuth 2.0クライアントIDの作成方法:", file=sys.stderr)
        print(f"   1. Google Cloud Console → APIとサービス → 認証情報", file=sys.stderr)
        print(f"   2. 「認証情報を作成」→「OAuth クライアント ID」", file=sys.stderr)
        print(f"   3. アプリケーションの種類: デスクトップアプリ", file=sys.stderr)
        print(f"   4. JSONをダウンロードして credentials.json として保存", file=sys.stderr)
        sys.exit(1)

    # リポジトリパスの存在確認
    if not os.path.exists(args.repo_path):
        print(f"❌ エラー: リポジトリが見つかりません: {args.repo_path}", file=sys.stderr)
        sys.exit(1)

    try:
        downloader = MasterdataDownloader(args.credentials, args.repo_path)
        downloader.run(args.index_sheet_url)
    except Exception as e:
        print(f"\n❌ エラーが発生しました: {e}", file=sys.stderr)
        import traceback
        traceback.print_exc()
        sys.exit(1)


if __name__ == '__main__':
    main()
