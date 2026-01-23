"""Slackスレッド検索機能"""

from collections import defaultdict
from datetime import date, datetime, timedelta
from typing import Any

from slack_tools.common.client import SlackClient
from slack_tools.common.models import SearchParams, SearchResult, ThreadInfo


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
        """スレッドを検索

        Args:
            params: 検索パラメータ

        Returns:
            検索結果
        """
        print("=" * 60)
        print("スレッド検索を開始")
        print("=" * 60)

        # 日時範囲をタイムスタンプに変換
        oldest = self._date_to_timestamp(params.start_date)
        latest = self._date_to_timestamp(params.end_date + timedelta(days=1))

        print(f"\n検索条件:")
        print(f"  期間: {params.start_date} 〜 {params.end_date}")
        print(f"  チャンネル: {', '.join(params.channel_names)} ({len(params.channels)}件)")
        print(f"  ユーザー: {', '.join(params.user_names)} ({len(params.users)}件)")

        # 統計情報
        stats = {
            "channels_searched": 0,
            "messages_scanned": 0,
            "threads_found": 0,
            "matching_threads": 0,
        }

        # 全スレッド情報を格納
        all_threads: list[ThreadInfo] = []

        # チャンネルごとに検索
        for i, channel_id in enumerate(params.channels, 1):
            channel_name = (
                params.channel_names[i - 1] if i - 1 < len(params.channel_names) else channel_id
            )
            print(f"\n[{i}/{len(params.channels)}] #{channel_name} を検索中...")

            try:
                # チャンネル履歴を取得
                messages = self.client.get_channel_history(
                    channel_id=channel_id,
                    oldest=oldest,
                    latest=latest,
                )
                print(f"  取得メッセージ数: {len(messages)}")
                stats["messages_scanned"] += len(messages)

                # スレッドを抽出
                threads = self._extract_threads(messages, params.users, channel_id, channel_name)
                print(f"  見つかったスレッド数: {len(threads)}")
                stats["threads_found"] += len(threads)

                # マッチしたスレッドをカウント
                matching = sum(1 for t in threads if t.matching_users)
                stats["matching_threads"] += matching
                print(f"  条件マッチ: {matching}件")

                all_threads.extend(threads)

            except Exception as e:
                print(f"  エラー: {e}")
                continue

            stats["channels_searched"] += 1

        # 検索結果を構築
        result = SearchResult(
            version="1.0.0",
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
        )

        print("\n" + "=" * 60)
        print("検索完了")
        print("=" * 60)
        print(f"検索チャンネル数: {stats['channels_searched']}")
        print(f"スキャンメッセージ数: {stats['messages_scanned']}")
        print(f"見つかったスレッド数: {stats['threads_found']}")
        print(f"条件マッチスレッド数: {stats['matching_threads']}")

        return result

    def _date_to_timestamp(self, d: date) -> str:
        """日付をSlackタイムスタンプに変換

        Args:
            d: 日付

        Returns:
            Slackタイムスタンプ文字列
        """
        dt = datetime.combine(d, datetime.min.time())
        return str(int(dt.timestamp()))

    def _extract_threads(
        self, messages: list[dict[str, Any]], target_users: list[str], channel_id: str, channel_name: str
    ) -> list[ThreadInfo]:
        """メッセージからスレッドを抽出

        Args:
            messages: メッセージ一覧
            target_users: 検索対象ユーザーID一覧
            channel_id: チャンネルID
            channel_name: チャンネル名

        Returns:
            スレッド情報一覧
        """
        # thread_tsごとにメッセージをグループ化
        thread_messages: dict[str, list[dict[str, Any]]] = defaultdict(list)

        for msg in messages:
            # thread_tsがない場合は親メッセージ
            thread_ts = msg.get("thread_ts", msg.get("ts"))
            if thread_ts:
                thread_messages[thread_ts].append(msg)

        # スレッド情報を構築
        threads: list[ThreadInfo] = []

        for thread_ts, msgs in thread_messages.items():
            # 親メッセージを探す
            parent = None
            for msg in msgs:
                if msg.get("ts") == thread_ts:
                    parent = msg
                    break

            if not parent:
                continue

            # 返信数（親メッセージを除く）
            reply_count = len(msgs) - 1

            # 条件マッチ: スレッド内に対象ユーザーが含まれるか
            matching_users = []
            for msg in msgs:
                user_id = msg.get("user")
                if user_id and user_id in target_users and user_id not in matching_users:
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

            threads.append(thread_info)

        return threads

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
