"""ClickUp API クライアント"""

import time
from typing import Dict, Any, List, Optional
import requests

from .models import Task, Comment, ListInfo


class ClickUpClient:
    """ClickUp API クライアント（再利用可能）"""

    BASE_URL = "https://api.clickup.com/api/v2"

    def __init__(self, api_key: str):
        """初期化

        Args:
            api_key: ClickUp API キー
        """
        self.api_key = api_key
        self.session = requests.Session()
        self.session.headers.update({"Authorization": api_key})

        # レート制限管理
        self._last_request_time = 0.0
        self._min_interval = 0.1  # 100ms（10 req/s）

    def _rate_limit_wait(self) -> None:
        """レート制限対策の待機"""
        elapsed = time.time() - self._last_request_time
        if elapsed < self._min_interval:
            time.sleep(self._min_interval - elapsed)
        self._last_request_time = time.time()

    def _request(
        self, method: str, endpoint: str, max_retries: int = 3, **kwargs
    ) -> Dict[str, Any]:
        """リトライ付きリクエスト

        Args:
            method: HTTP メソッド
            endpoint: エンドポイント
            max_retries: 最大リトライ回数
            **kwargs: requests へ渡す引数

        Returns:
            レスポンス JSON

        Raises:
            requests.HTTPError: リクエスト失敗
        """
        url = f"{self.BASE_URL}/{endpoint.lstrip('/')}"

        for attempt in range(max_retries):
            try:
                self._rate_limit_wait()
                response = self.session.request(method, url, **kwargs)
                response.raise_for_status()
                return response.json()

            except requests.HTTPError as e:
                # 429 (Too Many Requests) または 5xx エラーの場合はリトライ
                if e.response.status_code in (429, 500, 502, 503, 504):
                    if attempt < max_retries - 1:
                        wait_time = 2**attempt  # Exponential backoff
                        print(
                            f"リトライ {attempt + 1}/{max_retries} "
                            f"(status={e.response.status_code}, wait={wait_time}s)"
                        )
                        time.sleep(wait_time)
                        continue
                raise

        raise RuntimeError(f"最大リトライ回数 {max_retries} を超えました")

    def get_list(self, list_id: str) -> ListInfo:
        """リスト情報を取得

        Args:
            list_id: リスト ID

        Returns:
            リスト情報
        """
        data = self._request("GET", f"/list/{list_id}")
        return ListInfo.from_api(data)

    def get_tasks(
        self,
        list_id: str,
        statuses: Optional[List[str]] = None,
        include_closed: bool = False,
    ) -> List[Task]:
        """リスト内のタスクを取得

        Args:
            list_id: リスト ID
            statuses: フィルタするステータス（指定しない場合は全て）
            include_closed: クローズドタスクを含めるか

        Returns:
            タスクのリスト
        """
        params: Dict[str, Any] = {
            "include_closed": str(include_closed).lower(),
            "include_markdown_description": "true",
        }

        if statuses:
            params["statuses[]"] = statuses

        data = self._request("GET", f"/list/{list_id}/task", params=params)
        return [Task.from_api(task_data) for task_data in data.get("tasks", [])]

    def get_task(self, task_id: str) -> Task:
        """タスク詳細を取得

        Args:
            task_id: タスク ID

        Returns:
            タスク情報
        """
        data = self._request(
            "GET",
            f"/task/{task_id}",
            params={"include_markdown_description": "true"},
        )
        return Task.from_api(data)

    def get_task_raw(self, task_id: str) -> Dict[str, Any]:
        """タスク詳細を生データで取得

        Args:
            task_id: タスク ID

        Returns:
            タスクの生データ（APIレスポンス）
        """
        return self._request(
            "GET",
            f"/task/{task_id}",
            params={"include_markdown_description": "true"},
        )

    def get_comments(self, task_id: str) -> List[Comment]:
        """タスクのコメントを取得

        Args:
            task_id: タスク ID

        Returns:
            コメントのリスト
        """
        data = self._request("GET", f"/task/{task_id}/comment")
        return [Comment.from_api(comment_data) for comment_data in data.get("comments", [])]

    def get_comments_raw(self, task_id: str) -> List[Dict[str, Any]]:
        """タスクのコメントを生データで取得

        Args:
            task_id: タスク ID

        Returns:
            コメントの生データリスト（APIレスポンス）
        """
        data = self._request("GET", f"/task/{task_id}/comment")
        return data.get("comments", [])

    def download_file(self, url: str) -> bytes:
        """ファイルをダウンロード

        Args:
            url: ファイル URL

        Returns:
            ファイルの内容（バイナリ）
        """
        self._rate_limit_wait()
        response = self.session.get(url)
        response.raise_for_status()
        return response.content
