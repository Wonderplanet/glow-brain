"""Slackスレッド検索CLI"""

import argparse
import sys
from datetime import date, datetime
from pathlib import Path

from slack_tools.common.client import SlackClient
from slack_tools.common.config import Config
from slack_tools.common.file_utils import save_json
from slack_tools.common.models import SearchParams

from .finder import ThreadFinder


def parse_date(date_str: str) -> date:
    """日付文字列をパース

    Args:
        date_str: 日付文字列 (YYYY-MM-DD)

    Returns:
        日付オブジェクト

    Raises:
        ValueError: 日付形式が不正な場合
    """
    try:
        dt = datetime.strptime(date_str, "%Y-%m-%d")
        return dt.date()
    except ValueError as e:
        raise ValueError(f"日付形式が不正です: {date_str} (正しい形式: YYYY-MM-DD)") from e


def resolve_channel_ids(client: SlackClient, identifiers: list[str]) -> tuple[list[str], list[str]]:
    """チャンネル識別子からIDと名前を解決

    Args:
        client: Slack APIクライアント
        identifiers: チャンネルID or チャンネル名のリスト

    Returns:
        (チャンネルID一覧, チャンネル名一覧)
    """
    channel_ids = []
    channel_names = []

    for identifier in identifiers:
        # Cで始まる場合はチャンネルID
        if identifier.startswith("C"):
            try:
                info = client.get_channel_info(identifier)
                channel_ids.append(identifier)
                channel_names.append(info.get("name", identifier))
            except Exception as e:
                print(f"警告: チャンネル {identifier} の情報取得に失敗: {e}")
                channel_ids.append(identifier)
                channel_names.append(identifier)
        else:
            # チャンネル名として扱う（簡易実装: IDとして使用）
            print(f"警告: チャンネル名からIDへの解決は未実装です。IDを直接指定してください: {identifier}")
            channel_ids.append(identifier)
            channel_names.append(identifier)

    return channel_ids, channel_names


def resolve_user_ids(client: SlackClient, identifiers: list[str]) -> tuple[list[str], list[str]]:
    """ユーザー識別子からIDと名前を解決

    Args:
        client: Slack APIクライアント
        identifiers: ユーザーID or @ユーザー名のリスト

    Returns:
        (ユーザーID一覧, ユーザー名一覧)
    """
    user_ids = []
    user_names = []

    for identifier in identifiers:
        # @で始まる場合は除去
        user_id = identifier.lstrip("@")

        # Uで始まる場合はユーザーID
        if user_id.startswith("U"):
            try:
                info = client.get_user_info(user_id)
                user_ids.append(user_id)
                user_names.append(info.get("real_name") or info.get("name", user_id))
            except Exception as e:
                print(f"警告: ユーザー {user_id} の情報取得に失敗: {e}")
                user_ids.append(user_id)
                user_names.append(user_id)
        else:
            # ユーザー名として扱う（簡易実装: IDとして使用）
            print(f"警告: ユーザー名からIDへの解決は未実装です。IDを直接指定してください: @{user_id}")
            user_ids.append(user_id)
            user_names.append(user_id)

    return user_ids, user_names


def main():
    """メインエントリーポイント"""
    parser = argparse.ArgumentParser(description="Slackスレッドを検索")
    parser.add_argument(
        "--date",
        required=True,
        help="検索日 (YYYY-MM-DD形式)",
    )
    parser.add_argument(
        "--end-date",
        help="検索終了日 (YYYY-MM-DD形式、指定しない場合は--dateと同じ)",
    )
    parser.add_argument(
        "--channels",
        nargs="+",
        required=True,
        help="チャンネルID or チャンネル名（複数指定可）",
    )
    parser.add_argument(
        "--users",
        nargs="+",
        required=True,
        help="ユーザーID or @ユーザー名（複数指定可）",
    )
    parser.add_argument(
        "--export",
        action="store_true",
        help="検索結果をエクスポート",
    )
    parser.add_argument(
        "--delay",
        type=float,
        default=1.0,
        help="エクスポート時のスレッド間のディレイ（秒）",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="ドライラン（ファイル保存をスキップ）",
    )

    args = parser.parse_args()

    try:
        # 日付パース
        start_date = parse_date(args.date)
        end_date = parse_date(args.end_date) if args.end_date else start_date

        if end_date < start_date:
            print("エラー: 終了日は開始日以降である必要があります", file=sys.stderr)
            sys.exit(1)

        # 設定読み込み
        config = Config.from_env()
        client = SlackClient(config.slack_token)

        # チャンネル解決
        print("チャンネル情報を取得中...")
        channel_ids, channel_names = resolve_channel_ids(client, args.channels)

        # ユーザー解決
        print("ユーザー情報を取得中...")
        user_ids, user_names = resolve_user_ids(client, args.users)

        # 検索パラメータ構築
        params = SearchParams(
            channels=channel_ids,
            channel_names=channel_names,
            users=user_ids,
            user_names=user_names,
            start_date=start_date,
            end_date=end_date,
        )

        # 検索実行
        finder = ThreadFinder(client, config.workspace)
        result = finder.search(params)

        # 出力ディレクトリ構築
        search_id = datetime.now().strftime("%Y%m%d_%H%M%S")
        output_dir = Path(config.raw_data_dir) / config.workspace / "_searches" / search_id

        if not args.dry_run:
            output_dir.mkdir(parents=True, exist_ok=True)
            result_path = output_dir / "search_result.json"
            save_json(result_path, result.to_dict())
            print(f"\n検索結果を保存しました: {result_path}")
        else:
            print(f"\n[ドライラン] 検索結果の保存先: {output_dir / 'search_result.json'}")

        # エクスポート
        if args.export:
            from .batch_exporter import BatchExporter

            exporter = BatchExporter(config, result, output_dir, delay=args.delay)
            exporter.export(dry_run=args.dry_run)

    except Exception as e:
        print(f"エラー: {e}", file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    main()
