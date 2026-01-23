"""Markdown 生成ヘルパー"""

from typing import List, Optional
from datetime import datetime

from .models import Task, Comment, Attachment, CustomField


class MarkdownBuilder:
    """Markdown 生成ヘルパー"""

    @staticmethod
    def heading(text: str, level: int = 1) -> str:
        """見出しを生成

        Args:
            text: 見出しテキスト
            level: 見出しレベル（1-6）

        Returns:
            Markdown 見出し
        """
        return f"{'#' * level} {text}\n\n"

    @staticmethod
    def table(headers: List[str], rows: List[List[str]]) -> str:
        """テーブルを生成

        Args:
            headers: ヘッダー行
            rows: データ行

        Returns:
            Markdown テーブル
        """
        lines = []

        # ヘッダー
        lines.append("| " + " | ".join(headers) + " |")

        # 区切り線
        lines.append("| " + " | ".join(["---"] * len(headers)) + " |")

        # データ行
        for row in rows:
            lines.append("| " + " | ".join(row) + " |")

        return "\n".join(lines) + "\n\n"

    @staticmethod
    def link(text: str, url: str) -> str:
        """リンクを生成

        Args:
            text: リンクテキスト
            url: URL

        Returns:
            Markdown リンク
        """
        return f"[{text}]({url})"

    @staticmethod
    def horizontal_rule() -> str:
        """水平線を生成

        Returns:
            Markdown 水平線
        """
        return "---\n\n"

    @staticmethod
    def format_datetime(dt: Optional[datetime]) -> str:
        """日時をフォーマット

        Args:
            dt: 日時

        Returns:
            フォーマットされた日時文字列
        """
        if dt is None:
            return "なし"
        return dt.strftime("%Y-%m-%d %H:%M:%S")

    def task_basic_info_table(self, task: Task) -> str:
        """タスク基本情報テーブルを生成

        Args:
            task: タスク

        Returns:
            Markdown テーブル
        """
        rows = [
            ["タスク ID", task.id],
            ["ステータス", task.status],
            ["優先度", task.priority or "なし"],
            ["作成者", task.creator_name],
            [
                "担当者",
                ", ".join(task.assignees) if task.assignees else "未割り当て",
            ],
            ["タグ", ", ".join(task.tags) if task.tags else "なし"],
            ["作成日時", self.format_datetime(task.created_date)],
            ["更新日時", self.format_datetime(task.updated_date)],
            ["期限", self.format_datetime(task.due_date)],
            ["URL", self.link("ClickUp で開く", task.url)],
        ]
        return self.table(["項目", "値"], rows)

    def custom_fields_table(self, fields: List[CustomField]) -> str:
        """カスタムフィールドテーブルを生成

        Args:
            fields: カスタムフィールドのリスト

        Returns:
            Markdown テーブル
        """
        if not fields:
            return "カスタムフィールドはありません。\n\n"

        rows = []
        for field in fields:
            value_str = str(field.value) if field.value is not None else "未設定"
            if isinstance(field.value, datetime):
                value_str = self.format_datetime(field.value)
            rows.append([field.name, value_str, field.type])

        return self.table(["フィールド名", "値", "型"], rows)

    def comments_section(self, comments: List[Comment]) -> str:
        """コメントセクションを生成

        Args:
            comments: コメントのリスト

        Returns:
            Markdown コメントセクション
        """
        if not comments:
            return "コメントはありません。\n\n"

        lines = []
        for i, comment in enumerate(comments, 1):
            lines.append(f"**コメント #{i}** by {comment.user_name} ")
            lines.append(f"({self.format_datetime(comment.date)})\n")
            lines.append(f"{comment.text}\n")
            if i < len(comments):
                lines.append("")  # 空行

        return "\n".join(lines) + "\n\n"

    def attachments_table(self, attachments: List[Attachment]) -> str:
        """添付ファイルテーブルを生成

        Args:
            attachments: 添付ファイルのリスト

        Returns:
            Markdown テーブル
        """
        if not attachments:
            return "添付ファイルはありません。\n\n"

        rows = []
        for att in attachments:
            size_mb = att.size / (1024 * 1024) if att.size else 0
            rows.append(
                [
                    att.filename,
                    f"{size_mb:.2f} MB",
                    att.mimetype,
                    self.format_datetime(att.date),
                ]
            )

        return self.table(["ファイル名", "サイズ", "形式", "アップロード日時"], rows)
