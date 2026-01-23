"""Slackスレッドバッチエクスポート"""

import time
from pathlib import Path

from slack_tools.common.config import Config
from slack_tools.common.file_utils import save_json
from slack_tools.common.models import SearchResult
from slack_tools.exporters.thread_exporter.exporter import ThreadExporter


class BatchExporter:
    """Slackスレッドバッチエクスポーター"""

    def __init__(
        self,
        config: Config,
        search_result: SearchResult,
        output_dir: Path,
        delay: float = 1.0,
    ):
        """初期化

        Args:
            config: 設定オブジェクト
            search_result: 検索結果
            output_dir: 出力ディレクトリ
            delay: スレッド間のディレイ（秒）
        """
        self.config = config
        self.search_result = search_result
        self.output_dir = output_dir
        self.delay = delay
        self.exporter = ThreadExporter(config, skip_attachments=False)

    def export(self, dry_run: bool = False) -> None:
        """検索結果のスレッドをバッチエクスポート

        Args:
            dry_run: ドライラン（ファイル保存をスキップ）
        """
        threads = self.search_result.threads
        matching_threads = [t for t in threads if t.matching_users]

        if not matching_threads:
            print("\nエクスポート対象のスレッドがありません")
            return

        print("\n" + "=" * 60)
        print(f"バッチエクスポート開始: {len(matching_threads)}件")
        print("=" * 60)

        # 統計情報
        stats = {
            "total": len(matching_threads),
            "success": 0,
            "failed": 0,
            "skipped": 0,
        }

        failed_threads = []

        # 各スレッドをエクスポート
        for i, thread in enumerate(matching_threads, 1):
            print(f"\n[{i}/{len(matching_threads)}] エクスポート中...")
            print(f"  チャンネル: #{thread.channel_name}")
            print(f"  スレッドTS: {thread.thread_ts}")
            print(f"  URL: {thread.url}")

            try:
                # キャッシュキーを生成
                cache_key = f"{thread.channel_id}_{thread.thread_ts}"

                # キャッシュされたメッセージを取得
                cached_messages = self.search_result.raw_messages.get(cache_key)

                # エクスポート実行（キャッシュがあれば使用）
                thread_output_dir = self.exporter.export(
                    thread.url,
                    dry_run=dry_run,
                    cached_messages=cached_messages,
                    cached_channel_name=thread.channel_name,
                )

                stats["success"] += 1

            except Exception as e:
                print(f"  エラー: {e}")
                stats["failed"] += 1
                failed_threads.append(
                    {
                        "channel_name": thread.channel_name,
                        "thread_ts": thread.thread_ts,
                        "url": thread.url,
                        "error": str(e),
                    }
                )

            # ディレイ（最後のスレッドでは不要）
            if i < len(matching_threads):
                if not dry_run:
                    print(f"  {self.delay}秒待機中...")
                    time.sleep(self.delay)

        # サマリー保存
        summary = {
            "exported_at": self.search_result.searched_at,
            "workspace": self.search_result.workspace,
            "stats": stats,
            "failed_threads": failed_threads,
        }

        if not dry_run:
            summary_path = self.output_dir / "export_summary.json"
            save_json(summary_path, summary)
            print(f"\nエクスポートサマリーを保存しました: {summary_path}")
        else:
            print(f"\n[ドライラン] エクスポートサマリーの保存先: {self.output_dir / 'export_summary.json'}")

        # 結果表示
        print("\n" + "=" * 60)
        print("バッチエクスポート完了")
        print("=" * 60)
        print(f"総数: {stats['total']}")
        print(f"成功: {stats['success']}")
        print(f"失敗: {stats['failed']}")
        print(f"スキップ: {stats['skipped']}")

        if failed_threads:
            print("\n失敗したスレッド:")
            for ft in failed_threads:
                print(f"  - {ft['channel_name']}/{ft['thread_ts']}: {ft['error']}")
