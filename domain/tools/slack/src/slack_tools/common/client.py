"""Slack API クライアント"""

import time
from typing import Any

import requests


class SlackClient:
    """Slack API クライアント（再利用可能）"""

    BASE_URL = "https://slack.com/api"

    def __init__(self, token: str):
        """初期化

        Args:
            token: Slack API トークン (xoxp-, xoxb- など)
        """
        self.token = token
        self.session = requests.Session()
        self.session.headers.update({"Authorization": f"Bearer {token}"})

        # レート制限管理
        self._last_request_time = 0.0
        self._min_interval = 0.05  # 50ms（Tier 2: 20 req/s）

    def _rate_limit_wait(self) -> None:
        """レート制限対策の待機"""
        elapsed = time.time() - self._last_request_time
        if elapsed < self._min_interval:
            time.sleep(self._min_interval - elapsed)
        self._last_request_time = time.time()

    def _request(
        self, method: str, endpoint: str, max_retries: int = 3, **kwargs
    ) -> dict[str, Any]:
        """リトライ付きリクエスト

        Args:
            method: HTTP メソッド
            endpoint: エンドポイント (例: "conversations.replies")
            max_retries: 最大リトライ回数
            **kwargs: requests へ渡す引数

        Returns:
            レスポンス JSON

        Raises:
            RuntimeError: Slack APIエラー、またはリトライ超過
        """
        url = f"{self.BASE_URL}/{endpoint}"

        for attempt in range(max_retries):
            try:
                self._rate_limit_wait()
                response = self.session.request(method, url, **kwargs)
                response.raise_for_status()
                data = response.json()

                # Slack APIはHTTP 200でもエラーを返すことがある
                if not data.get("ok"):
                    error = data.get("error", "unknown_error")
                    raise RuntimeError(f"Slack APIエラー: {error}")

                return data

            except (requests.HTTPError, RuntimeError) as e:
                # 429 (Too Many Requests) または 5xx エラーの場合はリトライ
                if isinstance(e, requests.HTTPError) and e.response.status_code in (
                    429,
                    500,
                    502,
                    503,
                    504,
                ):
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

    def get_thread_messages(self, channel_id: str, thread_ts: str) -> list[dict[str, Any]]:
        """スレッドの全メッセージを取得

        Args:
            channel_id: チャンネルID
            thread_ts: スレッドタイムスタンプ

        Returns:
            メッセージ一覧（親メッセージ含む）
        """
        data = self._request(
            "GET",
            "conversations.replies",
            params={"channel": channel_id, "ts": thread_ts},
        )
        return data.get("messages", [])

    def get_channel_info(self, channel_id: str) -> dict[str, Any]:
        """チャンネル情報を取得

        Args:
            channel_id: チャンネルID

        Returns:
            チャンネル情報
        """
        data = self._request("GET", "conversations.info", params={"channel": channel_id})
        return data.get("channel", {})

    def get_user_info(self, user_id: str) -> dict[str, Any]:
        """ユーザー情報を取得

        Args:
            user_id: ユーザーID

        Returns:
            ユーザー情報
        """
        data = self._request("GET", "users.info", params={"user": user_id})
        return data.get("user", {})

    def download_file(self, url: str) -> bytes:
        """ファイルをダウンロード

        Args:
            url: ファイルURL (url_private)

        Returns:
            ファイルの内容（バイナリ）
        """
        self._rate_limit_wait()
        response = self.session.get(url)
        response.raise_for_status()
        return response.content

    def get_workspace_info(self) -> dict[str, Any]:
        """ワークスペース情報を取得（auth.test）

        Returns:
            ワークスペース情報
            - url: ワークスペースURL (例: "https://wonderplanet-glow.slack.com/")
            - team: ワークスペース名
            - team_id: ワークスペースID
        """
        data = self._request("GET", "auth.test")
        return {
            "url": data.get("url", ""),
            "team": data.get("team", ""),
            "team_id": data.get("team_id", ""),
        }

    def get_channel_history(
        self,
        channel_id: str,
        oldest: str | None = None,
        latest: str | None = None,
        limit: int = 100,
    ) -> list[dict[str, Any]]:
        """チャンネル履歴を取得（ページネーション対応）

        Args:
            channel_id: チャンネルID
            oldest: 最古のタイムスタンプ（この値以降のメッセージを取得）
            latest: 最新のタイムスタンプ（この値以前のメッセージを取得）
            limit: 1回のリクエストで取得するメッセージ数（最大1000）

        Returns:
            メッセージ一覧（古い順）
        """
        all_messages = []
        cursor = None

        while True:
            params: dict[str, Any] = {
                "channel": channel_id,
                "limit": min(limit, 1000),  # API上限は1000
            }
            if oldest:
                params["oldest"] = oldest
            if latest:
                params["latest"] = latest
            if cursor:
                params["cursor"] = cursor

            data = self._request("GET", "conversations.history", params=params)
            messages = data.get("messages", [])
            all_messages.extend(messages)

            # 次のページがあるかチェック
            cursor = data.get("response_metadata", {}).get("next_cursor")
            if not cursor:
                break

        # 古い順にソート（APIは新しい順で返す）
        all_messages.sort(key=lambda msg: float(msg.get("ts", "0")))
        return all_messages
