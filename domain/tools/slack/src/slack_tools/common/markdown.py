"""Markdown生成モジュール"""

from datetime import datetime

from .models import SlackThread


def format_timestamp(ts: str) -> str:
    """Slackタイムスタンプを可読形式に変換

    Args:
        ts: Slackタイムスタンプ (例: "1704067200.123456")

    Returns:
        フォーマットされた日時文字列
    """
    timestamp = float(ts)
    dt = datetime.fromtimestamp(timestamp)
    return dt.strftime("%Y-%m-%d %H:%M:%S")


def generate_thread_markdown(thread: SlackThread) -> str:
    """スレッドをMarkdown形式に変換

    Args:
        thread: Slackスレッド情報

    Returns:
        Markdown形式の文字列
    """
    lines = []

    # ヘッダー
    lines.append(f"# Slack Thread Export")
    lines.append("")
    lines.append(f"**Channel:** {thread.channel_name} (`{thread.channel_id}`)")
    lines.append(f"**Thread TS:** {thread.thread_ts}")
    lines.append(f"**Exported at:** {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    lines.append("")
    lines.append("---")
    lines.append("")

    # 親メッセージ
    parent = thread.get_parent_message()
    if parent:
        lines.append("## Parent Message")
        lines.append("")
        _append_message(lines, parent, thread)
        lines.append("")

    # 返信メッセージ
    replies = thread.get_replies()
    if replies:
        lines.append("## Replies")
        lines.append("")
        for i, reply in enumerate(replies, 1):
            lines.append(f"### Reply {i}")
            lines.append("")
            _append_message(lines, reply, thread)
            lines.append("")

    return "\n".join(lines)


def _append_message(lines: list[str], message, thread: SlackThread) -> None:
    """メッセージ情報をMarkdownに追加

    Args:
        lines: 出力先リスト
        message: SlackMessageオブジェクト
        thread: SlackThreadオブジェクト
    """
    user_name = thread.get_user_name(message.user)
    timestamp = format_timestamp(message.ts)

    lines.append(f"**Author:** {user_name} (`{message.user}`)")
    lines.append(f"**Timestamp:** {timestamp} (`{message.ts}`)")
    lines.append("")
    lines.append("**Message:**")
    lines.append("")
    lines.append("```")
    lines.append(message.text)
    lines.append("```")
    lines.append("")

    # 添付ファイル
    if message.files:
        lines.append("**Attachments:**")
        lines.append("")
        for file in message.files:
            file_info = f"- `{file.name}` ({file.mimetype}, {file.size} bytes)"
            if file.title and file.title != file.name:
                file_info += f" - {file.title}"
            lines.append(file_info)
        lines.append("")
