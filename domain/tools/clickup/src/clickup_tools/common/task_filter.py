"""タスクフィルタリング"""

from typing import List, Set
from abc import ABC, abstractmethod
from .models import Task


class TaskFilter(ABC):
    """タスクフィルタの基底クラス"""

    @abstractmethod
    def filter(self, tasks: List[Task]) -> List[Task]:
        """タスクをフィルタリングする

        Args:
            tasks: フィルタ前のタスクリスト

        Returns:
            フィルタ後のタスクリスト
        """
        pass


class HolidayTaskFilter(TaskFilter):
    """「休日」タスクとその子孫を除外するフィルタ"""

    def filter(self, tasks: List[Task]) -> List[Task]:
        """「休日」タスクとその子孫を除外

        ステップ:
        1. 親タスク（parent=None）で名前が「休日」のタスクを特定
        2. 親子関係マップを構築
        3. 「休日」タスクの全子孫IDを収集
        4. 「休日」タスクとその子孫を除外

        Args:
            tasks: フィルタ前のタスクリスト

        Returns:
            フィルタ後のタスクリスト
        """
        if not tasks:
            print("フィルタリング対象のタスクが0件のため、スキップします")
            return tasks

        print(f"=== 休日タスクフィルタリング開始: 対象タスク数={len(tasks)} ===")

        # ステップ1: 「休日」親タスクを特定
        holiday_parent_ids = {
            task.id for task in tasks
            if task.parent is None and task.name == "休日"
        }

        if not holiday_parent_ids:
            print("「休日」タスクが見つからなかったため、フィルタリングをスキップします")
            return tasks

        print(f"「休日」タスクを{len(holiday_parent_ids)}件特定:")
        for holiday_id in holiday_parent_ids:
            holiday_task = next(t for t in tasks if t.id == holiday_id)
            print(f"  ID={holiday_id}, name='{holiday_task.name}', status={holiday_task.status}")

        # ステップ2: 親子関係マップ構築
        parent_to_children = self._build_parent_child_map(tasks)

        # ステップ3: 子孫IDを再帰的に収集
        exclude_ids = set(holiday_parent_ids)
        for holiday_id in holiday_parent_ids:
            descendants = self._collect_descendants(holiday_id, parent_to_children)
            exclude_ids.update(descendants)
            if descendants:
                print(f"  「休日」タスク {holiday_id} の子孫: {len(descendants)}件")

        # ステップ4: フィルタリング
        filtered_tasks = [task for task in tasks if task.id not in exclude_ids]

        print(f"=== 休日タスクフィルタリング完了: {len(exclude_ids)}件除外 ===")
        print(f"  除外内訳: 休日タスク本体={len(holiday_parent_ids)}件, 子孫={len(exclude_ids) - len(holiday_parent_ids)}件")
        print(f"  結果: {len(tasks)}件 → {len(filtered_tasks)}件")

        return filtered_tasks

    def _build_parent_child_map(self, tasks: List[Task]) -> dict:
        """親子関係マップを構築する

        Args:
            tasks: タスクリスト

        Returns:
            親タスクIDをキー、子タスクIDのリストを値とする辞書
        """
        parent_to_children = {}
        for task in tasks:
            if task.parent:
                if task.parent not in parent_to_children:
                    parent_to_children[task.parent] = []
                parent_to_children[task.parent].append(task.id)
        return parent_to_children

    def _collect_descendants(self, parent_id: str, parent_to_children: dict) -> Set[str]:
        """指定された親タスクの全子孫IDを再帰的に収集

        Args:
            parent_id: 親タスクID
            parent_to_children: 親子関係マップ

        Returns:
            子孫タスクIDのセット
        """
        descendants = set()
        if parent_id in parent_to_children:
            for child_id in parent_to_children[parent_id]:
                descendants.add(child_id)
                descendants.update(self._collect_descendants(child_id, parent_to_children))
        return descendants
