#!/usr/bin/env python3
"""
ClickUpタスク分析スクリプト

ClickUpリストのタスクJSONから構造化された分析レポートを生成します。
"""

import json
import sys
import csv
from pathlib import Path
from datetime import datetime
from collections import defaultdict
from typing import Dict, List, Any, Optional, Tuple

class ClickUpTaskAnalyzer:
    """ClickUpタスク分析クラス"""

    def __init__(self, json_path: Path):
        """
        Args:
            json_path: _list_tasks_raw_phase2_with_subtasks.jsonのパス
        """
        self.json_path = json_path
        self.tasks = []
        self.member_roles = {}
        self.load_tasks()
        self.member_roles = self.load_member_roles()

    def load_tasks(self):
        """タスクJSONを読み込む"""
        with open(self.json_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
            self.tasks = data.get('tasks', [])

    def load_member_roles(self) -> Dict[str, Tuple[str, str]]:
        """
        メンバー一覧.csvから職種情報を読み込む

        Returns:
            Dict[str, Tuple[str, str]]: {正規化名: (職種, 専門領域)}
        """
        csv_path = Path("domain/knowledge/project-structure/メンバー一覧.csv")

        if not csv_path.exists():
            print(f"⚠️  警告: {csv_path} が見つかりません。職種判定はフォールバックロジックのみになります。")
            return {}

        member_roles = {}

        with open(csv_path, 'r', encoding='utf-8') as f:
            reader = csv.DictReader(f)
            for row in reader:
                name = row.get('名前(英語)', '').strip()
                role = row.get('職種', '').strip()
                specialization = row.get('専門領域', '').strip()

                if name:  # 名前が空でない場合のみ追加
                    normalized_name = self.normalize_name(name)
                    member_roles[normalized_name] = (role, specialization)

        print(f"✅ メンバー一覧.csvから{len(member_roles)}名の職種情報を読み込みました")
        return member_roles

    def normalize_name(self, name: str) -> str:
        """
        名前を正規化（小文字化、統一形式に変換）

        Examples:
            "Takeshi Tanaka" -> "takeshi tanaka"
            "kenji_watanabe" -> "kenji watanabe"
            "souta matsumoto" -> "souta matsumoto"
            "EriYoshida" -> "eriyoshida"
        """
        if not name:
            return ""

        # 小文字化
        name = name.lower()

        # アンダースコアをスペースに変換
        name = name.replace('_', ' ')

        # 前後の空白を削除
        name = name.strip()

        return name

    def get_role_from_member_csv(self, assignee: str) -> Optional[str]:
        """
        メンバー一覧.csvから職種を取得

        Args:
            assignee: ClickUpタスクのassignee名

        Returns:
            職種（例: "クライアントエンジニア", "UIデザイナー"）
            見つからない場合はNone
        """
        normalized_assignee = self.normalize_name(assignee)

        if normalized_assignee not in self.member_roles:
            return None

        role, specialization = self.member_roles[normalized_assignee]

        # 職種と専門領域から詳細職種を決定
        if role == "エンジニア":
            if "クライアント" in specialization:
                return "クライアントエンジニア"
            elif "サーバー" in specialization:
                return "サーバーエンジニア"
            elif "SRE" in specialization:
                return "SRE"
            else:
                return "エンジニア"
        elif role == "デザイナー":
            if "UI" in specialization:
                return "UIデザイナー"
            elif "アセット" in specialization or "アートディレクター" in specialization:
                return "アセットデザイナー"
            else:
                return "デザイナー"
        elif role == "プランナー":
            return "プランナー"
        elif role == "QA":
            return "QA・テスト"
        elif role == "マネジメント":
            return "マネジメント"
        elif role == "ビジネス":
            return "ビジネス"
        else:
            return role

    def classify_role_with_fallback(self, task_name: str, assignees: List[str]) -> str:
        """
        職種を分類（メンバー一覧.csv優先、フォールバックあり）

        優先順位:
        1. メンバー一覧.csvから取得
        2. タスク名プレフィックスから推測
        3. "その他"
        """
        # 担当者から職種を取得（複数担当者の場合は最初の一人）
        for assignee in assignees:
            role = self.get_role_from_member_csv(assignee)
            if role:
                return role

        # フォールバック: タスク名プレフィックスから推測
        if task_name.startswith('(サバ)') or '管理ツール' in task_name:
            return 'サーバーエンジニア'
        elif task_name.startswith('(クラ)') or task_name.startswith('(スキル'):
            return 'クライアントエンジニア'
        elif task_name.startswith('(UI)') or '_T' in task_name and '(UI)' in task_name:
            return 'UIデザイナー'
        elif 'qa期間' in task_name.lower():
            return 'QA・テスト'
        elif 'リリース' in task_name or 'サブミット' in task_name:
            return 'リリース管理'

        # デフォルト
        return 'その他'

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

    def group_tasks_by_role(self) -> Dict[str, List[Dict]]:
        """職種ごとにタスクをグループ化（メンバー一覧.csv使用）"""
        role_tasks = defaultdict(list)

        for task in self.tasks:
            role = self.classify_role_with_fallback(
                task['name'],
                task.get('assignees', [])
            )
            role_tasks[role].append(task)

        return dict(role_tasks)

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
        """職種別レポートを生成（メンバー一覧.csv使用）"""
        version = self.get_version_from_path(self.json_path.parent)
        project, list_name = self.get_project_and_list_name(self.json_path.parent)
        start_date, end_date = self.calculate_date_range()

        # 職種別タスクグループ
        role_tasks = self.group_tasks_by_role()
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

        # 職種別作業分析
        report_lines.append("## 職種別作業分析\n")
        report_lines.append("**注意**: 職種はメンバー一覧.csvから判定しています。未登録メンバーはタスク名プレフィックスから推測しています。\n")

        # 職種の優先順位（表示順）
        role_order = [
            'サーバーエンジニア',
            'クライアントエンジニア',
            'UIデザイナー',
            'アセットデザイナー',
            'プランナー',
            'QA・テスト',
            'マネジメント',
            'その他'
        ]

        for role in role_order:
            if role not in role_tasks:
                continue

            tasks = role_tasks[role]
            report_lines.append(f"### {role}\n")

            # この職種の主な担当者を抽出
            assignees_in_role = set()
            for task in tasks:
                assignees_in_role.update(task.get('assignees', []))

            if assignees_in_role:
                report_lines.append(f"**主な担当者**: {', '.join(sorted(assignees_in_role))}\n")

            # タスク件数
            report_lines.append(f"**タスク数**: {len(tasks)}件\n")

            # 主要タスク（上位10件）
            report_lines.append("**主な作業内容**:\n")
            for task in tasks[:10]:
                assignees_str = ', '.join(task.get('assignees', ['未割り当て']))
                due_date = self.format_date(task.get('due_date'))
                report_lines.append(f"1. {task['name']} ({assignees_str}) - 期限: {due_date}")

            if len(tasks) > 10:
                report_lines.append(f"\n*(他{len(tasks) - 10}タスク)*")

            report_lines.append("")

        # 基本統計
        report_lines.append("## 基本統計\n")
        report_lines.append(f"- **担当者数**: {len([a for a in assignee_tasks.keys() if a != '未割り当て'])}名")
        report_lines.append(f"- **職種数**: {len(role_tasks)}職種")
        report_lines.append(f"- **タスク総数**: {total_count}タスク")
        report_lines.append(f"- **完了率**: {closed_count}/{total_count}タスク ({100*closed_count//total_count if total_count > 0 else 0}%)\n")

        # 職種別タスク数
        report_lines.append("### 職種別タスク数\n")
        for role in role_order:
            if role in role_tasks:
                count = len(role_tasks[role])
                percentage = 100 * count // total_count if total_count > 0 else 0
                report_lines.append(f"- **{role}**: {count}タスク ({percentage}%)")
        report_lines.append("")

        # フッター
        report_lines.append("---\n")
        report_lines.append(f"*分析日: {datetime.now().strftime('%Y-%m-%d')}*")
        report_lines.append(f"*データソース: ClickUp {version}タスク情報*")
        report_lines.append(f"*職種判定: メンバー一覧.csv + タスク名プレフィックス*")

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
