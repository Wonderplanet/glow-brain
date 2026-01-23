"""Slackデータモデル定義"""

from dataclasses import dataclass
from typing import Any


@dataclass
class SlackFile:
    """Slack添付ファイル情報"""

    id: str  # ファイルID
    name: str  # ファイル名
    url_private: str  # プライベートURL（cookie認証用）
    url_private_download: str  # プライベートダウンロードURL（Authorizationヘッダー認証用）
    mimetype: str  # MIMEタイプ
    size: int  # ファイルサイズ（バイト）
    title: str | None = None  # ファイルタイトル

    @classmethod
    def from_api_response(cls, data: dict[str, Any]) -> "SlackFile":
        """APIレスポンスからインスタンスを生成

        Args:
            data: Slack API files配列の要素

        Returns:
            SlackFileインスタンス
        """
        return cls(
            id=data["id"],
            name=data["name"],
            url_private=data["url_private"],
            url_private_download=data["url_private_download"],
            mimetype=data["mimetype"],
            size=data["size"],
            title=data.get("title"),
        )


@dataclass
class SlackMessage:
    """Slackメッセージ情報"""

    ts: str  # メッセージタイムスタンプ
    user: str  # ユーザーID
    text: str  # メッセージテキスト
    thread_ts: str | None = None  # スレッドタイムスタンプ
    files: list[SlackFile] | None = None  # 添付ファイル
    raw_data: dict[str, Any] | None = None  # 生データ（デバッグ用）

    @classmethod
    def from_api_response(cls, data: dict[str, Any]) -> "SlackMessage":
        """APIレスポンスからインスタンスを生成

        Args:
            data: Slack API messagesメッセージオブジェクト

        Returns:
            SlackMessageインスタンス
        """
        files = None
        if "files" in data:
            files = [SlackFile.from_api_response(f) for f in data["files"]]

        return cls(
            ts=data["ts"],
            user=data.get("user", "unknown"),
            text=data.get("text", ""),
            thread_ts=data.get("thread_ts"),
            files=files,
            raw_data=data,
        )


@dataclass
class SlackThread:
    """Slackスレッド情報"""

    channel_id: str  # チャンネルID
    channel_name: str  # チャンネル名
    thread_ts: str  # スレッドタイムスタンプ
    messages: list[SlackMessage]  # メッセージ一覧
    user_cache: dict[str, str] | None = None  # ユーザーID→名前マッピング

    def get_parent_message(self) -> SlackMessage | None:
        """親メッセージを取得

        Returns:
            親メッセージ（存在しない場合はNone）
        """
        for msg in self.messages:
            if msg.ts == self.thread_ts:
                return msg
        return None

    def get_replies(self) -> list[SlackMessage]:
        """返信メッセージ一覧を取得

        Returns:
            返信メッセージ一覧（親メッセージを除く）
        """
        return [msg for msg in self.messages if msg.ts != self.thread_ts]

    def get_user_name(self, user_id: str) -> str:
        """ユーザーIDから表示名を取得

        Args:
            user_id: ユーザーID

        Returns:
            ユーザー名（キャッシュがない場合はID）
        """
        if self.user_cache and user_id in self.user_cache:
            return self.user_cache[user_id]
        return user_id

    def get_all_files(self) -> list[tuple[SlackMessage, SlackFile]]:
        """全ての添付ファイルを取得

        Returns:
            (メッセージ, ファイル)のタプルリスト
        """
        result = []
        for msg in self.messages:
            if msg.files:
                for file in msg.files:
                    result.append((msg, file))
        return result
