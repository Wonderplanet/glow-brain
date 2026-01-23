"""Slackスレッドエクスポーター本体"""

from pathlib import Path

from slack_tools.common.client import SlackClient
from slack_tools.common.config import Config
from slack_tools.common.file_utils import sanitize_filename, save_binary, save_json, save_text
from slack_tools.common.markdown import generate_thread_markdown
from slack_tools.common.models import SlackMessage, SlackThread
from slack_tools.common.url_parser import parse_slack_url


class ThreadExporter:
    """Slackスレッドエクスポーター"""

    def __init__(self, config: Config, skip_attachments: bool = False):
        """初期化

        Args:
            config: 設定オブジェクト
            skip_attachments: 添付ファイルのダウンロードをスキップするか
        """
        self.config = config
        self.skip_attachments = skip_attachments
        self.client = SlackClient(config.slack_token)

    def export(self, url: str, dry_run: bool = False) -> Path:
        """スレッドをエクスポート

        Args:
            url: SlackスレッドURL
            dry_run: ドライラン（ファイル保存をスキップ）

        Returns:
            出力ディレクトリパス
        """
        print(f"URL解析中: {url}")
        thread_info = parse_slack_url(url)

        print(f"ワークスペース: {thread_info.workspace}")
        print(f"チャンネルID: {thread_info.channel_id}")
        print(f"スレッドTS: {thread_info.thread_ts}")

        # チャンネル情報取得
        print("チャンネル情報を取得中...")
        channel_info = self.client.get_channel_info(thread_info.channel_id)
        channel_name = channel_info.get("name", thread_info.channel_id)
        print(f"チャンネル名: {channel_name}")

        # スレッドメッセージ取得
        print("スレッドメッセージを取得中...")
        messages_raw = self.client.get_thread_messages(
            thread_info.channel_id, thread_info.thread_ts
        )
        messages = [SlackMessage.from_api_response(msg) for msg in messages_raw]
        print(f"メッセージ数: {len(messages)}")

        # ユーザー名取得
        print("ユーザー情報を取得中...")
        user_cache = {}
        unique_users = {msg.user for msg in messages if msg.user != "unknown"}
        for user_id in unique_users:
            try:
                user_info = self.client.get_user_info(user_id)
                user_cache[user_id] = user_info.get("real_name") or user_info.get("name", user_id)
            except Exception as e:
                print(f"警告: ユーザー {user_id} の情報取得に失敗: {e}")
                user_cache[user_id] = user_id

        # スレッドオブジェクト構築
        thread = SlackThread(
            channel_id=thread_info.channel_id,
            channel_name=channel_name,
            thread_ts=thread_info.thread_ts,
            messages=messages,
            user_cache=user_cache,
        )

        # 出力パス構築
        safe_channel_name = sanitize_filename(channel_name)
        safe_thread_ts = thread_info.thread_ts.replace(".", "_")
        output_dir = self.config.get_output_path(
            thread_info.workspace, safe_channel_name, safe_thread_ts
        )

        if dry_run:
            print(f"\n[ドライラン] 出力先: {output_dir}")
            print("[ドライラン] ファイル保存をスキップ")
            return output_dir

        print(f"\n出力先: {output_dir}")

        # raw.json保存
        print("raw.jsonを保存中...")
        save_json(output_dir / "raw.json", messages_raw)

        # thread.md保存
        print("thread.mdを保存中...")
        markdown = generate_thread_markdown(thread)
        save_text(output_dir / "thread.md", markdown)

        # meta.json保存
        print("meta.jsonを保存中...")
        meta = {
            "workspace": thread_info.workspace,
            "channel_id": thread_info.channel_id,
            "channel_name": channel_name,
            "thread_ts": thread_info.thread_ts,
            "message_count": len(messages),
            "user_count": len(user_cache),
        }
        save_json(output_dir / "meta.json", meta)

        # 添付ファイルダウンロード
        if not self.skip_attachments:
            files = thread.get_all_files()
            if files:
                print(f"\n添付ファイルをダウンロード中 ({len(files)} 件)...")
                attachments_dir = output_dir / "attachments"
                for msg, file in files:
                    try:
                        safe_filename = sanitize_filename(file.name)
                        file_path = attachments_dir / f"{file.id}_{safe_filename}"
                        print(f"  - {file.name} -> {file_path.name}")
                        content = self.client.download_file(file.url_private_download)
                        save_binary(file_path, content)
                    except Exception as e:
                        print(f"警告: ファイル {file.name} のダウンロードに失敗: {e}")
        else:
            print("\n添付ファイルのダウンロードをスキップ")

        print(f"\n✓ エクスポート完了: {output_dir}")
        return output_dir
