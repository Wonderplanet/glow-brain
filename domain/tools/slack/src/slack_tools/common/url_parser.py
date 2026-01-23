"""Slack URL解析モジュール"""

import re
from dataclasses import dataclass


@dataclass
class SlackThreadInfo:
    """SlackスレッドURL解析結果"""

    workspace: str  # ワークスペースID (例: "glow-team")
    channel_id: str  # チャンネルID (例: "C123456")
    thread_ts: str  # スレッドタイムスタンプ (例: "1704067200.123456")
    message_ts: str | None = None  # メッセージタイムスタンプ（URLにpが含まれる場合）


def parse_slack_url(url: str) -> SlackThreadInfo:
    """Slack URLを解析してスレッド情報を抽出

    Args:
        url: SlackスレッドURL
            例: https://glow-team.slack.com/archives/C123456/p1704067200123456?thread_ts=1704067200.123456

    Returns:
        解析結果

    Raises:
        ValueError: URLが不正な形式の場合
    """
    # URL形式の検証とパース
    # https://{workspace}.slack.com/archives/{channel_id}/p{timestamp}?thread_ts={thread_ts}
    pattern = r"https://([^.]+)\.slack\.com/archives/([A-Z0-9]+)/p(\d+)(?:\?thread_ts=([0-9.]+))?"

    match = re.match(pattern, url)
    if not match:
        raise ValueError(f"不正なSlack URL形式です: {url}")

    workspace, channel_id, message_ts_raw, thread_ts = match.groups()

    # pのタイムスタンプを通常形式に変換 (p1704067200123456 -> 1704067200.123456)
    message_ts = f"{message_ts_raw[:10]}.{message_ts_raw[10:]}"

    # thread_tsが指定されていない場合はmessage_tsを使用（親メッセージ）
    if not thread_ts:
        thread_ts = message_ts

    return SlackThreadInfo(
        workspace=workspace,
        channel_id=channel_id,
        thread_ts=thread_ts,
        message_ts=message_ts if thread_ts else None,
    )
