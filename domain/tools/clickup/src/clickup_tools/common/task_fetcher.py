"""タスク取得戦略"""

from typing import List
from .models import Task
from .client import ClickUpClient


def get_tasks_two_phase(
    client: ClickUpClient,
    list_id: str,
    statuses: List[str] = None,
    include_closed: bool = False,
) -> List[Task]:
    """2段階でタスクを取得し、親タスク（特にClosedステータス）を確実に取得する

    Phase 1: include_subtasks=False で親タスクのみ取得
    Phase 2: include_subtasks=True でサブタスク含む全タスク取得
    結果をマージして重複排除

    Args:
        client: ClickUpクライアント
        list_id: リストID
        statuses: フィルタするステータスのリスト
        include_closed: Closedステータスを含めるか

    Returns:
        重複排除された全タスクのリスト（親タスク + サブタスク）
    """
    print("=== 2段階タスク取得開始 ===")

    # Phase 1: 親タスクのみ取得
    print("Phase 1: 親タスクのみ取得中...")
    parent_tasks = client.get_tasks(
        list_id,
        statuses=statuses,
        include_closed=include_closed,
        include_subtasks=False
    )
    print(f"Phase 1: {len(parent_tasks)}件の親タスクを取得")

    # Phase 2: サブタスク含む全タスク取得
    print("Phase 2: サブタスク含む全タスク取得中...")
    all_tasks_with_subtasks = client.get_tasks(
        list_id,
        statuses=statuses,
        include_closed=include_closed,
        include_subtasks=True
    )
    print(f"Phase 2: {len(all_tasks_with_subtasks)}件のタスク（サブタスク含む）を取得")

    # マージと重複排除
    merged_tasks = merge_tasks([parent_tasks, all_tasks_with_subtasks])

    print(f"=== 2段階タスク取得完了: 合計{len(merged_tasks)}件（重複排除済み） ===")

    # 内訳表示
    parent_count = sum(1 for t in merged_tasks if t.parent is None)
    subtask_count = sum(1 for t in merged_tasks if t.parent is not None)
    print(f"  内訳: 親タスク={parent_count}件, サブタスク={subtask_count}件")

    return merged_tasks


def merge_tasks(task_lists: List[List[Task]]) -> List[Task]:
    """複数のタスクリストをマージし、重複を排除する

    タスクIDをキーとして重複を排除します。
    同じIDのタスクが複数ある場合、後のリストのタスクで上書きされます。

    Args:
        task_lists: タスクリストのリスト

    Returns:
        重複排除されたタスクリスト
    """
    print("タスクのマージと重複排除を実行中...")
    task_dict = {}

    for task_list in task_lists:
        for task in task_list:
            task_dict[task.id] = task

    return list(task_dict.values())
