"""ClickUp URL 解析モジュール"""

import re
from .models import UrlInfo


class ClickUpUrlParser:
    """ClickUp URL を解析して情報を抽出する"""

    # URL パターン
    LIST_PATTERN = re.compile(r"/v/li/(\d+)")
    TASK_PATTERN = re.compile(r"/t/([a-z0-9]+)")
    SPACE_PATTERN = re.compile(r"/v/s/(\d+)")

    @staticmethod
    def parse(url: str) -> UrlInfo:
        """URL を解析して種別と ID を抽出

        Args:
            url: ClickUp URL

        Returns:
            URL 情報

        Raises:
            ValueError: サポートされていない URL 形式
        """
        # リスト URL
        if match := ClickUpUrlParser.LIST_PATTERN.search(url):
            return UrlInfo(type="list", id=match.group(1), raw_url=url)

        # タスク URL
        if match := ClickUpUrlParser.TASK_PATTERN.search(url):
            return UrlInfo(type="task", id=match.group(1), raw_url=url)

        # スペース URL
        if match := ClickUpUrlParser.SPACE_PATTERN.search(url):
            return UrlInfo(type="space", id=match.group(1), raw_url=url)

        raise ValueError(f"サポートされていない URL 形式: {url}")

    @staticmethod
    def extract_list_id(url: str) -> str:
        """リスト ID を抽出

        Args:
            url: リスト URL

        Returns:
            リスト ID

        Raises:
            ValueError: リスト URL でない場合
        """
        if match := ClickUpUrlParser.LIST_PATTERN.search(url):
            return match.group(1)
        raise ValueError(f"リスト URL ではありません: {url}")

    @staticmethod
    def extract_task_id(url: str) -> str:
        """タスク ID を抽出

        Args:
            url: タスク URL

        Returns:
            タスク ID

        Raises:
            ValueError: タスク URL でない場合
        """
        if match := ClickUpUrlParser.TASK_PATTERN.search(url):
            return match.group(1)
        raise ValueError(f"タスク URL ではありません: {url}")

    @staticmethod
    def extract_space_id(url: str) -> str:
        """スペース ID を抽出

        Args:
            url: スペース URL

        Returns:
            スペース ID

        Raises:
            ValueError: スペース URL でない場合
        """
        if match := ClickUpUrlParser.SPACE_PATTERN.search(url):
            return match.group(1)
        raise ValueError(f"スペース URL ではありません: {url}")
