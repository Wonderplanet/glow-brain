"""スペース構造エクスポーター CLI"""

import argparse
import sys

from clickup_tools.common import Config, ClickUpClient
from .exporter import SpaceStructureExporter


def main():
    """CLI エントリーポイント"""
    parser = argparse.ArgumentParser(
        description="ClickUp スペース構造（フォルダ・リスト一覧）をCSVエクスポート",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
使用例:
  # スペースURLで指定
  python -m clickup_tools.exporters.space_structure_exporter \\
    --url "https://app.clickup.com/12345678/v/s/987654321"

  # スペースIDで直接指定
  python -m clickup_tools.exporters.space_structure_exporter \\
    --space-id "987654321"

出力先:
  domain/raw-data/clickup/{スペース名}/_space_structure.csv
        """,
    )

    # URL または スペースID のいずれかが必須
    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument(
        "--url",
        "-u",
        help="ClickUp スペース URL",
    )
    group.add_argument(
        "--space-id",
        "-s",
        help="スペース ID（URLの代わり）",
    )

    args = parser.parse_args()

    try:
        # 設定を読み込み
        config = Config()
        config.validate()

        # ClickUp クライアントを初期化
        client = ClickUpClient(config.api_key)

        # エクスポーターを作成
        exporter = SpaceStructureExporter(client, config)

        # エクスポート実行
        print("=" * 60)
        print("ClickUp Space Structure Exporter")
        print("=" * 60)

        # URL または スペースID を使用
        space_url_or_id = args.url or args.space_id

        result = exporter.export(
            space_url_or_id=space_url_or_id,
        )

        # 結果を表示
        print("\n" + "=" * 60)
        print("エクスポート完了")
        print("=" * 60)
        print(f"スペース名:     {result.space_name}")
        print(f"スペースID:     {result.space_id}")
        print(f"フォルダ数:     {result.total_folders}")
        print(f"リスト数:       {result.total_lists}")
        print(f"出力先:         {result.output_file}")

        return 0

    except KeyboardInterrupt:
        print("\n\n中断されました。")
        return 130

    except Exception as e:
        print(f"\n❌ エラー: {e}", file=sys.stderr)
        import traceback
        traceback.print_exc()
        return 1


if __name__ == "__main__":
    sys.exit(main())
