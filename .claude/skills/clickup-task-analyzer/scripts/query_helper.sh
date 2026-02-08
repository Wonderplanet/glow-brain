#!/bin/bash
# ClickUpタスクJSON調査用クエリヘルパー

set -e

LIST_DIR="$1"

if [ -z "$LIST_DIR" ]; then
    echo "使用法: ./query_helper.sh <clickup-list-directory>"
    echo ""
    echo "例: ./query_helper.sh domain/raw-data/clickup/GLOW/GLOW(開発)/v1.5.0"
    exit 1
fi

PHASE1_JSON="$LIST_DIR/_list_tasks_raw_phase1_parents.json"
PHASE2_JSON="$LIST_DIR/_list_tasks_raw_phase2_with_subtasks.json"

# ファイル存在チェック
if [ ! -f "$PHASE2_JSON" ]; then
    echo "❌ エラー: $PHASE2_JSON が見つかりません"
    exit 1
fi

echo "=================================================="
echo "ClickUpタスクJSON調査レポート"
echo "ディレクトリ: $LIST_DIR"
echo "=================================================="
echo ""

# 親タスク一覧
if [ -f "$PHASE1_JSON" ]; then
    echo "=== 親タスク一覧 ==="
    jq -r '.tasks[] | "\(.name) (担当: \(.assignees | join(", ")))"' "$PHASE1_JSON" | head -20
    echo ""
fi

# プレフィックス集計
echo "=== プレフィックス集計 ==="
jq -r '.tasks[].name | capture("^\\((?<prefix>[^)]+)\\)")? // {prefix: "なし"} | .prefix' \
   "$PHASE2_JSON" | sort | uniq -c | sort -rn
echo ""

# 担当者別タスク数
echo "=== 担当者別タスク数 ==="
jq -r '.tasks[].assignees[]' "$PHASE2_JSON" | sort | uniq -c | sort -rn
echo ""

# 期限日範囲
echo "=== 期限日範囲 ==="
start_date=$(jq -r '.tasks[] | select(.due_date != null) | .due_date' "$PHASE2_JSON" | sort | head -1)
end_date=$(jq -r '.tasks[] | select(.due_date != null) | .due_date' "$PHASE2_JSON" | sort | tail -1)

if [ -n "$start_date" ] && [ -n "$end_date" ]; then
    echo "開始: $start_date"
    echo "終了: $end_date"
else
    echo "期限日情報なし"
fi
echo ""

# ステータス集計
echo "=== ステータス集計 ==="
jq -r '.tasks[].status' "$PHASE2_JSON" | sort | uniq -c | sort -rn
echo ""

# タスク総数
echo "=== 基本統計 ==="
total_tasks=$(jq '.tasks | length' "$PHASE2_JSON")
total_assignees=$(jq -r '.tasks[].assignees[]' "$PHASE2_JSON" | sort -u | wc -l)

echo "タスク総数: $total_tasks"
echo "担当者数: $total_assignees"
echo ""

# 優先度集計（QAリストの場合）
if jq -e '.tasks[0].priority' "$PHASE2_JSON" > /dev/null 2>&1; then
    echo "=== 優先度集計（QAリスト） ==="
    jq -r '.tasks[] | .priority // "なし"' "$PHASE2_JSON" | sort | uniq -c | sort -rn
    echo ""
fi

echo "=================================================="
echo "分析完了"
echo "=================================================="
