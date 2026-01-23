"""Slackデータモデル定義"""

from dataclasses import dataclass, field
from datetime import date
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


@dataclass
class SearchParams:
    """スレッド検索パラメータ"""

    channels: list[str]  # チャンネルID一覧
    channel_names: list[str]  # チャンネル名一覧（表示用）
    users: list[str]  # ユーザーID一覧
    user_names: list[str]  # ユーザー名一覧（表示用）
    start_date: date  # 検索開始日
    end_date: date  # 検索終了日


@dataclass
class ThreadInfo:
    """スレッド情報（検索結果用）"""

    channel_id: str  # チャンネルID
    channel_name: str  # チャンネル名
    thread_ts: str  # スレッドタイムスタンプ
    parent_user_id: str  # 親メッセージのユーザーID
    parent_text: str  # 親メッセージのテキスト
    reply_count: int  # 返信数
    matching_users: list[str]  # マッチしたユーザーID一覧
    url: str  # スレッドURL

    def to_dict(self) -> dict[str, Any]:
        """辞書形式に変換

        Returns:
            辞書表現
        """
        return {
            "channel_id": self.channel_id,
            "channel_name": self.channel_name,
            "thread_ts": self.thread_ts,
            "parent_user_id": self.parent_user_id,
            "parent_text": self.parent_text,
            "reply_count": self.reply_count,
            "matching_users": self.matching_users,
            "url": self.url,
        }


@dataclass
class SearchResult:
    """スレッド検索結果"""

    version: str  # 検索結果フォーマットバージョン
    searched_at: str  # 検索実行日時（ISO8601）
    workspace: str  # ワークスペース名
    params: dict[str, Any]  # 検索パラメータ
    stats: dict[str, int]  # 統計情報
    threads: list[ThreadInfo]  # 見つかったスレッド一覧
    raw_messages: dict[str, list[dict[str, Any]]] = field(default_factory=dict)  # キャッシュされたスレッドメッセージ（キー: "channel_id_thread_ts"）

    def to_dict(self) -> dict[str, Any]:
        """辞書形式に変換

        Returns:
            辞書表現
        """
        return {
            "version": self.version,
            "searched_at": self.searched_at,
            "workspace": self.workspace,
            "params": self.params,
            "stats": self.stats,
            "threads": [t.to_dict() for t in self.threads],
        }
