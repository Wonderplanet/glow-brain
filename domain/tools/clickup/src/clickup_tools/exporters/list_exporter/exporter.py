"""リスト内のチケットエクスポート処理"""

import json
from dataclasses import dataclass
from pathlib import Path
from typing import Optional

from clickup_tools.common import (
    ClickUpClient,
    Config,
    MarkdownBuilder,
    ClickUpUrlParser,
    sanitize_filename,
    ensure_directory,
    save_file,
    save_text,
)
from clickup_tools.common.models import Task, ListInfo


@dataclass
class ExportResult:
    """エクスポート結果"""

    total_tasks: int
    exported_tasks: int
    skipped_tasks: int
    output_dir: Path


class ListExporter:
    """リスト内のチケットをエクスポート"""

    def __init__(self, client: ClickUpClient, config: Config):
        """初期化

        Args:
            client: ClickUp API クライアント
            config: 設定
        """
        self.client = client
        self.config = config
        self.markdown = MarkdownBuilder()

    def export(
        self,
        list_url: str,
        statuses: Optional[list[str]] = None,
        output_dir: Optional[Path] = None,
        skip_attachments: bool = False,
        debug_limit: Optional[int] = None,
        dry_run: bool = False,
    ) -> ExportResult:
        """リスト内のチケットをエクスポート

        Args:
            list_url: リスト URL
            statuses: フィルタするステータスのリスト（未指定なら全ステータス）
            output_dir: 出力ディレクトリ（指定しない場合は設定から取得）
            skip_attachments: 添付ファイルをスキップ
            debug_limit: デバッグ用の処理件数制限
            dry_run: ドライラン（ファイル出力しない）

        Returns:
            エクスポート結果
        """
        # リスト ID を抽出
        list_id = ClickUpUrlParser.extract_list_id(list_url)

        # リスト情報を取得
        print(f"リスト情報を取得中... (ID: {list_id})")
        list_info = self.client.get_list(list_id)
        print(f"リスト名: {list_info.name}")
        print(f"スペース: {list_info.space_name}")
        if list_info.folder_name:
            print(f"フォルダ: {list_info.folder_name}")

        # タスクを取得
        if statuses:
            status_text = ", ".join(statuses)
            print(f"\n{status_text} タスクを取得中...")
        else:
            print("\n全タスクを取得中...")

        include_closed = statuses is None or "closed" in statuses
        tasks = self.client.get_tasks(list_id, statuses=statuses, include_closed=include_closed)
        print(f"取得件数: {len(tasks)}")

        # デバッグ制限
        if debug_limit and len(tasks) > debug_limit:
            print(f"\n⚠️ デバッグモード: 最初の {debug_limit} 件のみ処理")
            tasks = tasks[:debug_limit]

        # 出力ディレクトリを決定
        if output_dir is None:
            output_dir = self._build_output_path(list_info)

        print(f"\n出力先: {output_dir}")

        # タスクをエクスポート
        exported = 0
        skipped = 0

        for i, task in enumerate(tasks, 1):
            print(f"\n[{i}/{len(tasks)}] {task.name}")

            try:
                if dry_run:
                    print(f"  → ドライラン: 出力をスキップ")
                else:
                    self._export_task(task, list_info, output_dir, skip_attachments)
                exported += 1
            except Exception as e:
                print(f"  ⚠️ エラー: {e}")
                skipped += 1

        # 結果を返す
        return ExportResult(
            total_tasks=len(tasks),
            exported_tasks=exported,
            skipped_tasks=skipped,
            output_dir=output_dir,
        )

    def _build_output_path(self, list_info: ListInfo) -> Path:
        """出力パスを構築

        Args:
            list_info: リスト情報

        Returns:
            出力パス
        """
        parts = [sanitize_filename(list_info.space_name)]

        if list_info.folder_name:
            parts.append(sanitize_filename(list_info.folder_name))

        parts.append(sanitize_filename(list_info.name))

        return self.config.get_output_path(*parts)

    def _export_task(
        self,
        task: Task,
        list_info: ListInfo,
        base_dir: Path,
        skip_attachments: bool,
    ) -> None:
        """タスクをエクスポート

        Args:
            task: タスク
            list_info: リスト情報
            base_dir: ベースディレクトリ
            skip_attachments: 添付ファイルをスキップ
        """
        # タスクディレクトリ名
        task_dir_name = f"{task.id}_{sanitize_filename(task.name, max_length=50)}"
        task_dir = base_dir / task_dir_name

        # ディレクトリを作成
        ensure_directory(task_dir)

        # コメントを取得
        comments = self.client.get_comments(task.id)

        # 生データを取得
        task_raw = self.client.get_task_raw(task.id)
        comments_raw = self.client.get_comments_raw(task.id)

        # raw.json を保存
        raw_data = {
            "task": task_raw,
            "comments": comments_raw,
        }
        raw_path = task_dir / "raw.json"
        save_text(json.dumps(raw_data, ensure_ascii=False, indent=2), raw_path)
        print(f"  ✓ raw.json を保存")

        # Markdown を生成（コメントは除外）
        markdown_content = self._generate_markdown(task, list_info, comments)

        # ticket.md を保存
        ticket_path = task_dir / "ticket.md"
        save_text(markdown_content, ticket_path)
        print(f"  ✓ ticket.md を保存")

        # activity.md を保存（コメントがある場合）
        if comments:
            activity_content = self._generate_activity_markdown(task, comments)
            activity_path = task_dir / "activity.md"
            save_text(activity_content, activity_path)
            print(f"  ✓ activity.md を保存")

        # 添付ファイルをダウンロード
        if not skip_attachments and task.attachments:
            attachments_dir = task_dir / "attachments"
            ensure_directory(attachments_dir)

            for i, att in enumerate(task.attachments, 1):
                try:
                    print(
                        f"  → 添付ファイル {i}/{len(task.attachments)}: {att.filename}"
                    )
                    content = self.client.download_file(att.url)
                    file_path = attachments_dir / sanitize_filename(att.filename)
                    save_file(content, file_path)
                    print(f"    ✓ 保存完了")
                except Exception as e:
                    print(f"    ⚠️ ダウンロード失敗: {e}")

    def _generate_markdown(
        self, task: Task, list_info: ListInfo, comments
    ) -> str:
        """タスク情報を Markdown 形式で生成

        Args:
            task: タスク
            list_info: リスト情報
            comments: コメントリスト

        Returns:
            Markdown テキスト
        """
        md = self.markdown

        # タイトル
        lines = [md.heading(task.name, level=1)]

        # 基本情報
        lines.append(md.heading("基本情報", level=2))
        lines.append(md.task_basic_info_table(task))

        # リスト情報
        lines.append(md.heading("リスト情報", level=2))
        list_rows = [
            ["リスト名", list_info.name],
            ["スペース", list_info.space_name],
        ]
        if list_info.folder_name:
            list_rows.append(["フォルダ", list_info.folder_name])
        lines.append(md.table(["項目", "値"], list_rows))

        # 説明（Markdown形式を優先）
        description_content = task.markdown_description or task.description
        if description_content:
            lines.append(md.heading("説明", level=2))
            lines.append(f"{description_content}\n\n")

        # カスタムフィールド
        if task.custom_fields:
            lines.append(md.heading("カスタムフィールド", level=2))
            lines.append(md.custom_fields_table(task.custom_fields))

        # 添付ファイル
        if task.attachments:
            lines.append(md.heading("添付ファイル", level=2))
            lines.append(md.attachments_table(task.attachments))

        return "".join(lines)

    def _generate_activity_markdown(self, task: Task, comments) -> str:
        """コメント情報を Markdown 形式で生成

        Args:
            task: タスク
            comments: コメントリスト

        Returns:
            Markdown テキスト
        """
        md = self.markdown
        lines = [md.heading(f"{task.name} - Activity", level=1)]

        if comments:
            lines.append(md.comments_section(comments))
        else:
            lines.append("コメントはありません。\n\n")

        return "".join(lines)
