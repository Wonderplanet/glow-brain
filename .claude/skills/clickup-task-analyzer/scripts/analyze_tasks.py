#!/usr/bin/env python3
"""
ClickUpタスク分析スクリプト

ClickUpリストのタスクJSONから構造化された分析レポートを生成します。
"""

import json
import sys
from pathlib import Path
from datetime import datetime
from collections import defaultdict
from typing import Dict, List, Any, Optional

class ClickUpTaskAnalyzer:
    """ClickUpタスク分析クラス"""

    def __init__(self, json_path: Path):
        """
        Args:
            json_path: _list_tasks_raw_phase2_with_subtasks.jsonのパス
        """
        self.json_path = json_path
        self.tasks = []
        self.load_tasks()

    def load_tasks(self):
        """タスクJSONを読み込む"""
        with open(self.json_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
            self.tasks = data.get('tasks', [])

    def get_version_from_path(self, dir_path: Path) -> str:
        """ディレクトリパスからバージョン名を抽出"""
        # 例: domain/raw-data/clickup/GLOW/GLOW(開発)/v1.5.0 → v1.5.0
        return dir_path.name

    def get_project_and_list_name(self, dir_path: Path) -> tuple:
        """ディレクトリパスからプロジェクト名とリスト名を抽出"""
        # 例: domain/raw-data/clickup/GLOW/GLOW(開発)/v1.5.0
        parts = dir_path.parts
        if 'clickup' in parts:
            idx = parts.index('clickup')
            if len(parts) > idx + 2:
                project = parts[idx + 1]
                list_name = parts[idx + 2]
                return project, list_name
        return "Unknown", "Unknown"

    def calculate_date_range(self) -> tuple:
        """開発期間を算出（最も早い期限日 ～ 最も遅い期限日）"""
        dates = []
        for task in self.tasks:
            if task.get('due_date'):
                try:
                    dt = datetime.fromisoformat(task['due_date'].replace('Z', '+00:00'))
                    dates.append(dt)
                except:
                    pass

        if not dates:
            return None, None

        return min(dates), max(dates)

    def group_tasks_by_assignee(self) -> Dict[str, List[Dict]]:
        """担当者ごとにタスクをグループ化（職種分類なし）"""
        assignee_tasks = defaultdict(list)

        for task in self.tasks:
            assignees = task.get('assignees', ['未割り当て'])
            for assignee in assignees:
                assignee_tasks[assignee].append(task)

        return dict(assignee_tasks)

    def get_parent_tasks(self) -> List[Dict]:
        """親タスク（主要機能）を取得"""
        parent_task_ids = set(task.get('parent') for task in self.tasks if task.get('parent'))
        parent_tasks = [task for task in self.tasks if task['id'] in parent_task_ids]
        return parent_tasks

    def count_subtasks(self, parent_id: str) -> int:
        """指定親タスクのサブタスク数をカウント"""
        return sum(1 for task in self.tasks if task.get('parent') == parent_id)

    def format_date(self, date_str: Optional[str]) -> str:
        """日付文字列をフォーマット"""
        if not date_str:
            return "なし"
        try:
            dt = datetime.fromisoformat(date_str.replace('Z', '+00:00'))
            return dt.strftime('%Y-%m-%d')
        except:
            return date_str

    def format_japanese_date(self, dt: datetime) -> str:
        """datetimeを日本語形式にフォーマット"""
        return dt.strftime('%Y年%m月%d日')

    def calculate_duration(self, start_date: datetime, end_date: datetime) -> str:
        """期間を算出（約X週間/ヶ月）"""
        delta = (end_date - start_date).days

        if delta < 7:
            return f"約{delta}日"
        elif delta < 30:
            weeks = delta // 7
            return f"約{weeks}週間"
        else:
            months = delta // 30
            return f"約{months}ヶ月"

    def generate_report(self, output_path: Path):
        """基本レポートを生成（職種分類なし、AIが後で詳細化）"""
        version = self.get_version_from_path(self.json_path.parent)
        project, list_name = self.get_project_and_list_name(self.json_path.parent)
        start_date, end_date = self.calculate_date_range()

        # 担当者別タスクグループ
        assignee_tasks = self.group_tasks_by_assignee()

        # レポート生成
        report_lines = []

        # タイトル
        report_lines.append(f"# {project} {version} 開発プロジェクト分析レポート\n")

        # プロジェクト概要
        report_lines.append("## プロジェクト概要\n")
        report_lines.append(f"- **バージョン**: {version}")

        if start_date and end_date:
            duration = self.calculate_duration(start_date, end_date)
            report_lines.append(f"- **開発期間**: {self.format_japanese_date(start_date)} ～ {self.format_japanese_date(end_date)}（{duration}）")
            report_lines.append(f"- **リリース日**: {self.format_japanese_date(end_date)}")

        closed_count = sum(1 for task in self.tasks if task.get('status') == 'Closed')
        total_count = len(self.tasks)
        report_lines.append(f"- **タスク総数**: {total_count}タスク（{closed_count}件Closed）\n")

        # 主要機能
        report_lines.append("## 主要機能\n")
        parent_tasks = self.get_parent_tasks()
        for i, parent in enumerate(parent_tasks[:10], 1):  # 主要10件
            report_lines.append(f"### {i}. {parent['name']}")
            if parent.get('description'):
                report_lines.append(f"- {parent['description']}")
            subtask_count = self.count_subtasks(parent['id'])
            report_lines.append(f"- サブタスク: {subtask_count}件\n")

        # タスク一覧（担当者別、職種分類なし）
        report_lines.append("## タスク一覧（担当者別）\n")
        report_lines.append("**注意**: この一覧は担当者ごとにグループ化されています。職種分類はAIが後で行います。\n")

        for assignee, tasks in sorted(assignee_tasks.items()):
            if assignee == '未割り当て':
                continue

            report_lines.append(f"### 担当者: {assignee}\n")
            for task in tasks[:15]:  # 各担当者の上位15タスク
                due_date = self.format_date(task.get('due_date'))
                report_lines.append(f"1. **{task['name']}** - 期限: {due_date}")

            if len(tasks) > 15:
                report_lines.append(f"\n*(他{len(tasks) - 15}タスク)*")

            report_lines.append("")

        # 未割り当てタスク
        if '未割り当て' in assignee_tasks:
            report_lines.append("### 未割り当てタスク\n")
            for task in assignee_tasks['未割り当て'][:10]:
                due_date = self.format_date(task.get('due_date'))
                report_lines.append(f"1. **{task['name']}** - 期限: {due_date}")
            report_lines.append("")

        # 基本統計
        report_lines.append("## 基本統計\n")
        report_lines.append(f"- **担当者数**: {len([a for a in assignee_tasks.keys() if a != '未割り当て'])}名")
        report_lines.append(f"- **タスク総数**: {total_count}タスク")
        report_lines.append(f"- **完了率**: {closed_count}/{total_count}タスク ({100*closed_count//total_count if total_count > 0 else 0}%)\n")

        # フッター
        report_lines.append("---\n")
        report_lines.append(f"*分析日: {datetime.now().strftime('%Y-%m-%d')}*")
        report_lines.append(f"*データソース: ClickUp {version}タスク情報*\n")
        report_lines.append("**次のステップ**: このレポートをAIが読み込み、職種分類と詳細分析を行います。")

        # ファイル書き込み
        output_path.parent.mkdir(parents=True, exist_ok=True)
        with open(output_path, 'w', encoding='utf-8') as f:
            f.write('\n'.join(report_lines))

        print(f"✅ 基本レポート生成完了: {output_path}")
        print(f"📝 次のステップ: AIが職種分類と詳細分析を行います")


def main():
    """メイン関数"""
    if len(sys.argv) < 2:
        print("使用法: python analyze_tasks.py <clickup-list-directory>")
        print("例: python analyze_tasks.py domain/raw-data/clickup/GLOW/GLOW(開発)/v1.5.0")
        sys.exit(1)

    # 入力ディレクトリパス
    input_dir = Path(sys.argv[1])
    json_path = input_dir / '_list_tasks_raw_phase2_with_subtasks.json'

    if not json_path.exists():
        print(f"❌ エラー: {json_path} が見つかりません")
        sys.exit(1)

    # 出力ファイルパス
    project, list_name = input_dir.parts[-2], input_dir.parts[-1]
    output_filename = f"clickup_{project}_{list_name}-タスク分析.md"
    output_path = Path("domain/knowledge/project-management") / output_filename

    # 分析実行
    analyzer = ClickUpTaskAnalyzer(json_path)
    analyzer.generate_report(output_path)


if __name__ == "__main__":
    main()
