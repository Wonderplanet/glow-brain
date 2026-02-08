"""リスト内のチケットエクスポーター CLI"""

import argparse
import sys
from pathlib import Path

from clickup_tools.common import Config, ClickUpClient
from .exporter import ListExporter


def main():
    """CLI エントリーポイント"""
    parser = argparse.ArgumentParser(
        description="ClickUp リスト内のチケットをエクスポート",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
使用例:
  # 基本的な使い方（全ステータス）
  python -m clickup_tools.exporters.list_exporter \\
    --url "https://app.clickup.com/12345678/v/li/987654321"

  # closed のみ
  python -m clickup_tools.exporters.list_exporter \\
    --url "https://app.clickup.com/12345678/v/li/987654321" \\
    --status closed

  # 出力先を指定
  python -m clickup_tools.exporters.list_exporter \\
    --url "https://app.clickup.com/12345678/v/li/987654321" \\
    --output "./exports"

  # 添付ファイルをスキップ
  python -m clickup_tools.exporters.list_exporter \\
    --url "https://app.clickup.com/12345678/v/li/987654321" \\
    --skip-attachments

  # デバッグモード（最初の3件のみ）
  python -m clickup_tools.exporters.list_exporter \\
    --url "https://app.clickup.com/12345678/v/li/987654321" \\
    --debug-limit 3

  # ドライラン（ファイル出力なし）
  python -m clickup_tools.exporters.list_exporter \\
    --url "https://app.clickup.com/12345678/v/li/987654321" \\
    --dry-run

  # サブタスクも含めてエクスポート（休日は自動的に除外される）
  python -m clickup_tools.exporters.list_exporter \\
    --url "https://app.clickup.com/12345678/v/li/987654321" \\
    --include-subtasks

  # 「休日」タスクも含めてエクスポート
  python -m clickup_tools.exporters.list_exporter \\
    --url "https://app.clickup.com/12345678/v/li/987654321" \\
    --include-subtasks \\
    --include-holiday-tasks
        """,
    )

    parser.add_argument(
        "--url",
        "-u",
        required=True,
        help="ClickUp リスト URL",
    )

    parser.add_argument(
        "--status",
        "-s",
        type=str,
        help="フィルタするステータス（例: closed）。未指定なら全ステータス",
    )

    parser.add_argument(
        "--output",
        "-o",
        type=Path,
        help="出力先ディレクトリ（デフォルト: domain/raw-data/clickup/...）",
    )

    parser.add_argument(
        "--skip-attachments",
        action="store_true",
        help="添付ファイルのダウンロードをスキップ",
    )

    parser.add_argument(
        "--debug-limit",
        type=int,
        metavar="N",
        help="デバッグ用: 最初の N 件のみ処理",
    )

    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="ドライラン（ファイル出力しない）",
    )

    parser.add_argument(
        "--include-subtasks",
        action="store_true",
        help="サブタスクも含めてエクスポート（デフォルト: 含めない）",
    )

    parser.add_argument(
        "--include-holiday-tasks",
        dest="exclude_holiday_tasks",
        action="store_false",
        help="「休日」タスクも含めてエクスポート（デフォルト: 除外する）",
    )

    # デフォルト値を設定
    parser.set_defaults(exclude_holiday_tasks=True)

    args = parser.parse_args()

    try:
        # 設定を読み込み
        config = Config()
        config.validate()

        # ClickUp クライアントを初期化
        client = ClickUpClient(config.api_key)

        # エクスポーターを作成
        exporter = ListExporter(client, config)

        # エクスポート実行
        print("=" * 60)
        print("ClickUp List Exporter")
        print("=" * 60)

        # ステータスフィルタを準備
        statuses = [args.status] if args.status else None

        result = exporter.export(
            list_url=args.url,
            statuses=statuses,
            output_dir=args.output,
            skip_attachments=args.skip_attachments,
            debug_limit=args.debug_limit,
            dry_run=args.dry_run,
            include_subtasks=args.include_subtasks,
            exclude_holiday_tasks=args.exclude_holiday_tasks,
        )

        # 結果を表示
        print("\n" + "=" * 60)
        print("エクスポート完了")
        print("=" * 60)
        print(f"総タスク数:     {result.total_tasks}")
        print(f"エクスポート:   {result.exported_tasks}")
        print(f"スキップ:       {result.skipped_tasks}")
        print(f"出力先:         {result.output_dir}")

        return 0

    except KeyboardInterrupt:
        print("\n\n中断されました。")
        return 130

    except Exception as e:
        print(f"\n❌ エラー: {e}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
