"""Slackスレッドエクスポーター CLI"""

import argparse
import sys

from slack_tools.common.config import Config

from .exporter import ThreadExporter


def main() -> int:
    """CLIエントリーポイント

    Returns:
        終了コード（0: 成功、1: 失敗）
    """
    parser = argparse.ArgumentParser(
        description="Slackスレッドをエクスポート（メッセージ + 添付ファイル）",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
使用例:
  # 基本実行
  uv run python -m slack_tools.exporters.thread_exporter \\
    --url "https://glow-team.slack.com/archives/C123456/p1704067200123456?thread_ts=1704067200.123456"

  # 添付ファイルをスキップ
  uv run python -m slack_tools.exporters.thread_exporter \\
    --url "..." --skip-attachments

  # ドライラン（ファイル保存なし）
  uv run python -m slack_tools.exporters.thread_exporter \\
    --url "..." --dry-run
        """,
    )

    parser.add_argument(
        "--url",
        required=True,
        help="SlackスレッドURL（例: https://workspace.slack.com/archives/C.../p...?thread_ts=...）",
    )
    parser.add_argument(
        "--skip-attachments",
        action="store_true",
        help="添付ファイルのダウンロードをスキップ",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="ドライラン（API呼び出しは実行するがファイル保存をスキップ）",
    )

    args = parser.parse_args()

    try:
        # 設定読み込み
        config = Config()
        config.validate()

        # エクスポート実行
        exporter = ThreadExporter(config, skip_attachments=args.skip_attachments)
        exporter.export(args.url, dry_run=args.dry_run)

        return 0

    except ValueError as e:
        print(f"エラー: {e}", file=sys.stderr)
        return 1
    except Exception as e:
        print(f"予期しないエラー: {e}", file=sys.stderr)
        import traceback

        traceback.print_exc()
        return 1


if __name__ == "__main__":
    sys.exit(main())
