"""Slackスレッド検索機能"""

from datetime import datetime, timedelta
from typing import Any
from urllib.parse import parse_qs, urlparse

from slack_tools.common.client import SlackClient
from slack_tools.common.models import SearchParams, SearchResult, ThreadInfo


def extract_thread_ts_from_permalink(permalink: str) -> str | None:
    """permalinkからthread_tsを抽出

    Args:
        permalink: Slackメッセージのpermalink URL

    Returns:
        thread_ts（抽出できない場合はNone）

    Examples:
        >>> url = "https://workspace.slack.com/archives/C123/p1234567890?thread_ts=1234567.123456&cid=C123"
        >>> extract_thread_ts_from_permalink(url)
        '1234567.123456'
    """
    if not permalink:
        return None

    try:
        parsed = urlparse(permalink)
        params = parse_qs(parsed.query)
        thread_ts_list = params.get("thread_ts", [])
        return thread_ts_list[0] if thread_ts_list else None
    except Exception:
        return None


class ThreadFinder:
    """Slackスレッド検索"""

    def __init__(self, client: SlackClient, workspace: str):
        """初期化

        Args:
            client: Slack APIクライアント
            workspace: ワークスペース名
        """
        self.client = client
        self.workspace = workspace

    def search(self, params: SearchParams) -> SearchResult:
        """スレッドを検索（search.messages API を使用）

        Args:
            params: 検索パラメータ

        Returns:
            検索結果
        """
        print("=" * 60)
        print("スレッド検索を開始")
        print("=" * 60)

        print(f"\n検索条件:")
        print(f"  期間: {params.start_date} 〜 {params.end_date}")
        print(f"  チャンネル: {', '.join(params.channel_names)} ({len(params.channels)}件)")
        print(f"  ユーザー: {', '.join(params.user_names)} ({len(params.users)}件)")

        # 統計情報
        stats = {
            "channels_searched": len(params.channels),
            "messages_scanned": 0,
            "threads_found": 0,
            "matching_threads": 0,
        }

        # 検索クエリを構築してメッセージを検索
        query = self._build_query(params)
        print(f"\n検索クエリ: {query}")
        print("search.messages API で検索中...")

        try:
            messages = self.client.search_messages(query)
            print(f"  検索結果: {len(messages)}件のメッセージ")
            stats["messages_scanned"] = len(messages)
        except Exception as e:
            print(f"  エラー: {e}")
            return SearchResult(
                version="2.0.0",
                searched_at=datetime.now().astimezone().isoformat(),
                workspace=self.workspace,
                params={
                    "channels": params.channels,
                    "channel_names": params.channel_names,
                    "users": params.users,
                    "user_names": params.user_names,
                    "start_date": params.start_date.isoformat(),
                    "end_date": params.end_date.isoformat(),
                },
                stats=stats,
                threads=[],
                raw_messages={},
            )

        # thread_ts でグループ化してスレッドを特定
        print("\nスレッドを特定中...")
        thread_ts_set = set()
        for msg in messages:
            channel_info = msg.get("channel", {})
            channel_id = channel_info.get("id")
            ts = msg.get("ts")
            permalink = msg.get("permalink", "")

            # search.messages APIではthread_ts属性がないため、permalinkから抽出
            thread_ts = extract_thread_ts_from_permalink(permalink) or ts

            if channel_id and thread_ts:
                thread_ts_set.add((channel_id, thread_ts))

        print(f"  見つかったスレッド数: {len(thread_ts_set)}")

        # 各スレッドの全メッセージを取得
        print("\n各スレッドの全メッセージを取得中...")
        thread_data: dict[tuple[str, str], list[dict[str, Any]]] = {}
        raw_messages: dict[str, list[dict[str, Any]]] = {}

        for i, (channel_id, thread_ts) in enumerate(thread_ts_set, 1):
            try:
                print(f"  [{i}/{len(thread_ts_set)}] {channel_id}/{thread_ts}")
                thread_messages = self.client.get_thread_messages(channel_id, thread_ts)
                thread_data[(channel_id, thread_ts)] = thread_messages

                # raw_messages キャッシュに保存
                cache_key = f"{channel_id}_{thread_ts}"
                raw_messages[cache_key] = thread_messages

            except Exception as e:
                print(f"    エラー: {e}")
                continue

        # ThreadInfo を構築
        print("\nスレッド情報を構築中...")
        all_threads: list[ThreadInfo] = []
        channel_name_cache: dict[str, str] = {}

        for (channel_id, thread_ts), thread_messages in thread_data.items():
            # チャンネル名を取得（キャッシュを使用）
            if channel_id not in channel_name_cache:
                try:
                    channel_info = self.client.get_channel_info(channel_id)
                    channel_name_cache[channel_id] = channel_info.get("name", channel_id)
                except Exception:
                    channel_name_cache[channel_id] = channel_id

            channel_name = channel_name_cache[channel_id]

            # 親メッセージを探す
            parent = None
            for msg in thread_messages:
                if msg.get("ts") == thread_ts:
                    parent = msg
                    break

            if not parent:
                continue

            # 返信数（親メッセージを除く）
            reply_count = len(thread_messages) - 1

            # 条件マッチ: スレッド内に対象ユーザーが含まれるか
            matching_users = []
            for msg in thread_messages:
                user_id = msg.get("user")
                if user_id and user_id in params.users and user_id not in matching_users:
                    matching_users.append(user_id)

            # スレッドURLを生成
            thread_url = self._build_thread_url(channel_id, thread_ts)

            thread_info = ThreadInfo(
                channel_id=channel_id,
                channel_name=channel_name,
                thread_ts=thread_ts,
                parent_user_id=parent.get("user", "unknown"),
                parent_text=parent.get("text", "")[:200],  # 最初の200文字のみ
                reply_count=reply_count,
                matching_users=matching_users,
                url=thread_url,
            )

            all_threads.append(thread_info)

        stats["threads_found"] = len(all_threads)
        stats["matching_threads"] = sum(1 for t in all_threads if t.matching_users)

        # 検索結果を構築
        result = SearchResult(
            version="2.0.0",
            searched_at=datetime.now().astimezone().isoformat(),
            workspace=self.workspace,
            params={
                "channels": params.channels,
                "channel_names": params.channel_names,
                "users": params.users,
                "user_names": params.user_names,
                "start_date": params.start_date.isoformat(),
                "end_date": params.end_date.isoformat(),
            },
            stats=stats,
            threads=all_threads,
            raw_messages=raw_messages,
        )

        print("\n" + "=" * 60)
        print("検索完了")
        print("=" * 60)
        print(f"検索チャンネル数: {stats['channels_searched']}")
        print(f"スキャンメッセージ数: {stats['messages_scanned']}")
        print(f"見つかったスレッド数: {stats['threads_found']}")
        print(f"条件マッチスレッド数: {stats['matching_threads']}")

        return result

    def _build_query(self, params: SearchParams) -> str:
        """search.messages API用のクエリを構築

        Args:
            params: 検索パラメータ

        Returns:
            検索クエリ文字列
        """
        query_parts = []

        # ユーザー指定
        for user_id in params.users:
            query_parts.append(f"from:<@{user_id}>")

        # チャンネル指定
        for channel_id in params.channels:
            query_parts.append(f"in:<#{channel_id}>")

        # 日付範囲
        if params.start_date == params.end_date:
            # 同じ日の場合は on: を使用
            query_parts.append(f"on:{params.start_date.isoformat()}")
        else:
            # 期間指定の場合は前後1日ずらす（after/beforeは境界を含まない）
            after_date = params.start_date - timedelta(days=1)
            before_date = params.end_date + timedelta(days=1)
            query_parts.append(f"after:{after_date.isoformat()}")
            query_parts.append(f"before:{before_date.isoformat()}")

        return " ".join(query_parts)

    def _build_thread_url(self, channel_id: str, thread_ts: str) -> str:
        """スレッドURLを構築

        Args:
            channel_id: チャンネルID
            thread_ts: スレッドタイムスタンプ

        Returns:
            スレッドURL
        """
        # タイムスタンプをSlackのURL形式に変換（ドットを削除してpプレフィックス）
        ts_for_url = thread_ts.replace(".", "")
        return f"https://{self.workspace}.slack.com/archives/{channel_id}/p{ts_for_url}?thread_ts={thread_ts}"
