"""スペース構造エクスポート処理"""

import csv
from dataclasses import dataclass
from datetime import datetime
from pathlib import Path
from typing import Optional, List, Dict, Any

from clickup_tools.common import (
    ClickUpClient,
    Config,
    ClickUpUrlParser,
    sanitize_filename,
    ensure_directory,
)


@dataclass
class ExportResult:
    """エクスポート結果"""

    space_name: str
    space_id: str
    total_folders: int
    total_lists: int
    output_file: Path


class SpaceStructureExporter:
    """スペース構造をエクスポート"""

    def __init__(self, client: ClickUpClient, config: Config):
        """初期化

        Args:
            client: ClickUp API クライアント
            config: 設定
        """
        self.client = client
        self.config = config

    def export(
        self,
        space_url_or_id: str,
    ) -> ExportResult:
        """スペース構造をCSVエクスポート

        処理フロー:
        1. スペース情報を取得
        2. 出力パスを構築（config.get_output_path(space_name)）
        3. フォルダ一覧を取得（アーカイブ済み/未アーカイブ両方）
        4. 各フォルダ配下のリスト一覧を取得（アーカイブ済み/未アーカイブ両方）
        5. フォルダレスリストを取得（アーカイブ済み/未アーカイブ両方）
        6. CSV出力（output_dir / "_space_structure.csv"）

        Args:
            space_url_or_id: ClickUp スペース URL または スペース ID

        Returns:
            エクスポート結果
        """
        # スペースIDを抽出
        space_id = self._extract_space_id(space_url_or_id)

        # スペース情報を取得
        print(f"スペース情報を取得中... (ID: {space_id})")
        space_data = self.client.get_space(space_id)
        space_name = space_data.get("name", "Unknown")
        print(f"スペース名: {space_name}")

        # 出力パスを構築
        output_dir = self.config.get_output_path(sanitize_filename(space_name))
        ensure_directory(output_dir)
        output_file = output_dir / "_space_structure.csv"

        # データ収集
        rows = []

        # フォルダ一覧を取得（アーカイブ済み/未アーカイブ両方）
        print("\nフォルダ一覧を取得中...")
        folders = self._get_all_folders(space_id)
        print(f"  取得フォルダ数: {len(folders)}")

        folder_count = 0
        list_count = 0

        # 各フォルダ配下のリストを取得
        for folder in folders:
            folder_id = folder.get("id")
            folder_name = folder.get("name", "")
            folder_archived = folder.get("archived", False)
            folder_date_created = self._format_timestamp(folder.get("date_created"))

            print(f"\n  フォルダ: {folder_name}")

            # フォルダ配下のリスト一覧を取得（アーカイブ済み/未アーカイブ両方）
            lists = self._get_all_lists_in_folder(folder_id)
            print(f"    リスト数: {len(lists)}")

            folder_count += 1

            for list_data in lists:
                list_name = list_data.get("name", "")
                list_id = list_data.get("id")
                list_archived = list_data.get("archived", False)
                list_date_created = self._format_timestamp(list_data.get("date_created"))

                rows.append({
                    "スペース名": space_name,
                    "スペースID": space_id,
                    "フォルダ名": folder_name,
                    "フォルダID": folder_id,
                    "フォルダアーカイブ": "TRUE" if folder_archived else "FALSE",
                    "フォルダ作成日時": folder_date_created,
                    "リスト名": list_name,
                    "リストID": list_id,
                    "リストアーカイブ": "TRUE" if list_archived else "FALSE",
                    "リスト作成日時": list_date_created,
                })
                list_count += 1

        # フォルダレスリストを取得（アーカイブ済み/未アーカイブ両方）
        print("\nフォルダレスリストを取得中...")
        folderless_lists = self._get_all_folderless_lists(space_id)
        print(f"  フォルダレスリスト数: {len(folderless_lists)}")

        for list_data in folderless_lists:
            list_name = list_data.get("name", "")
            list_id = list_data.get("id")
            list_archived = list_data.get("archived", False)
            list_date_created = self._format_timestamp(list_data.get("date_created"))

            rows.append({
                "スペース名": space_name,
                "スペースID": space_id,
                "フォルダ名": "",
                "フォルダID": "",
                "フォルダアーカイブ": "",
                "フォルダ作成日時": "",
                "リスト名": list_name,
                "リストID": list_id,
                "リストアーカイブ": "TRUE" if list_archived else "FALSE",
                "リスト作成日時": list_date_created,
            })
            list_count += 1

        # CSV出力
        print(f"\nCSV出力中: {output_file}")
        self._write_csv(rows, output_file)
        print(f"  ✓ 出力完了")

        return ExportResult(
            space_name=space_name,
            space_id=space_id,
            total_folders=folder_count,
            total_lists=list_count,
            output_file=output_file,
        )

    def _extract_space_id(self, space_url_or_id: str) -> str:
        """スペースIDを抽出

        Args:
            space_url_or_id: スペースURLまたはID

        Returns:
            スペースID
        """
        # URLの場合は解析
        if "http" in space_url_or_id or "/" in space_url_or_id:
            return ClickUpUrlParser.extract_space_id(space_url_or_id)
        # IDの場合はそのまま返す
        return space_url_or_id

    def _get_all_folders(self, space_id: str) -> List[Dict[str, Any]]:
        """アーカイブ済み/未アーカイブ全てのフォルダを取得

        Args:
            space_id: スペースID

        Returns:
            フォルダリスト
        """
        # 未アーカイブのフォルダ
        active_folders = self.client.get_space_folders(space_id, archived=False)
        # アーカイブ済みのフォルダ
        archived_folders = self.client.get_space_folders(space_id, archived=True)

        # 重複を除いてマージ
        folder_ids = set()
        all_folders = []

        for folder in active_folders + archived_folders:
            folder_id = folder.get("id")
            if folder_id not in folder_ids:
                folder_ids.add(folder_id)
                all_folders.append(folder)

        return all_folders

    def _get_all_lists_in_folder(self, folder_id: str) -> List[Dict[str, Any]]:
        """フォルダ内のアーカイブ済み/未アーカイブ全てのリストを取得

        Args:
            folder_id: フォルダID

        Returns:
            リストリスト
        """
        # 未アーカイブのリスト
        active_lists = self.client.get_folder_lists(folder_id, archived=False)
        # アーカイブ済みのリスト
        archived_lists = self.client.get_folder_lists(folder_id, archived=True)

        # 重複を除いてマージ
        list_ids = set()
        all_lists = []

        for list_data in active_lists + archived_lists:
            list_id = list_data.get("id")
            if list_id not in list_ids:
                list_ids.add(list_id)
                all_lists.append(list_data)

        return all_lists

    def _get_all_folderless_lists(self, space_id: str) -> List[Dict[str, Any]]:
        """スペース直下のアーカイブ済み/未アーカイブ全てのフォルダレスリストを取得

        Args:
            space_id: スペースID

        Returns:
            フォルダレスリストリスト
        """
        # 未アーカイブのリスト
        active_lists = self.client.get_folderless_lists(space_id, archived=False)
        # アーカイブ済みのリスト
        archived_lists = self.client.get_folderless_lists(space_id, archived=True)

        # 重複を除いてマージ
        list_ids = set()
        all_lists = []

        for list_data in active_lists + archived_lists:
            list_id = list_data.get("id")
            if list_id not in list_ids:
                list_ids.add(list_id)
                all_lists.append(list_data)

        return all_lists

    def _format_timestamp(self, timestamp_ms: str | None) -> str:
        """タイムスタンプをISO 8601形式に変換

        Args:
            timestamp_ms: ミリ秒単位のタイムスタンプ文字列

        Returns:
            ISO 8601形式の日時文字列
        """
        if not timestamp_ms:
            return ""

        try:
            # ミリ秒を秒に変換してdatetimeオブジェクトを生成
            timestamp_sec = int(timestamp_ms) / 1000
            dt = datetime.fromtimestamp(timestamp_sec)
            # ISO 8601形式で返す（秒まで、マイクロ秒は不要）
            return dt.strftime("%Y-%m-%dT%H:%M:%S")
        except (ValueError, TypeError):
            return ""

    def _write_csv(self, rows: List[dict], output_file: Path) -> None:
        """CSVファイルに書き込み

        Args:
            rows: 出力する行データ
            output_file: 出力ファイルパス
        """
        headers = [
            "スペース名", "スペースID",
            "フォルダ名", "フォルダID", "フォルダアーカイブ", "フォルダ作成日時",
            "リスト名", "リストID", "リストアーカイブ", "リスト作成日時"
        ]

        with output_file.open('w', newline='', encoding='utf-8-sig') as f:
            writer = csv.DictWriter(f, fieldnames=headers)
            writer.writeheader()
            writer.writerows(rows)
