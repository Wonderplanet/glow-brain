#!/bin/bash

set -uo pipefail

# ============================================
# タスク対応期間CSV一括生成スクリプト
# ============================================
# domain/raw-data/clickup/GLOW以下のすべてのリストに対して
# generate_task_duration_csv.shを自動実行します。

# 設定
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# scripts/ から3階層上がリポジトリルート（scripts -> clickup -> tools -> domain -> repo_root）
REPO_ROOT="$(cd "$SCRIPT_DIR/../../../.." && pwd)"
BASE_DIR="$REPO_ROOT/domain/raw-data/clickup/GLOW"
SCRIPT_PATH="$SCRIPT_DIR/generate_task_duration_csv.sh"

# 色付き出力
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ============================================
# 前提条件チェック
# ============================================

echo ""
echo "============================================"
echo "タスク対応期間CSV一括生成"
echo "============================================"
echo ""

# 既存スクリプトの存在確認
if [ ! -f "$SCRIPT_PATH" ]; then
  echo -e "${RED}エラー: generate_task_duration_csv.sh が見つかりません${NC}" >&2
  echo "パス: $SCRIPT_PATH" >&2
  exit 1
fi

# ベースディレクトリの存在確認
if [ ! -d "$BASE_DIR" ]; then
  echo -e "${RED}エラー: ベースディレクトリが存在しません: $BASE_DIR${NC}" >&2
  exit 1
fi

# ============================================
# リスト検出
# ============================================

echo "リストを検出中..."
list_paths=()

# 深さ2のディレクトリを検出（フォルダ名/リスト名）
while IFS= read -r list_dir; do
  relative_path="${list_dir#$BASE_DIR/}"

  # raw.jsonの存在確認（少なくとも1つのタスクにraw.jsonが存在するか）
  if find "$list_dir" -mindepth 1 -maxdepth 1 -type d -exec test -f "{}/raw.json" \; -print -quit | grep -q .; then
    list_paths+=("$relative_path")
  fi
done < <(find "$BASE_DIR" -mindepth 2 -maxdepth 2 -type d | sort)

# リストが見つからない場合
if [ ${#list_paths[@]} -eq 0 ]; then
  echo -e "${YELLOW}警告: 処理対象のリストが見つかりませんでした${NC}" >&2
  echo "ベースディレクトリ: $BASE_DIR" >&2
  exit 0
fi

echo -e "${GREEN}検出されたリスト数: ${#list_paths[@]}${NC}"
echo ""

# リストを表示
echo "処理対象リスト:"
for path in "${list_paths[@]}"; do
  echo "  - $path"
done
echo ""

# ============================================
# メイン処理ループ
# ============================================

# カウンター初期化
success_count=0
error_count=0
failed_lists=()
start_time=$(date +%s)

total_lists=${#list_paths[@]}
current=0

for relative_path in "${list_paths[@]}"; do
  current=$((current + 1))
  echo "============================================"
  echo -e "${BLUE}[$current/$total_lists] 処理中: $relative_path${NC}"
  echo "============================================"

  # スクリプト実行
  if "$SCRIPT_PATH" "$relative_path"; then
    success_count=$((success_count + 1))
    echo -e "${GREEN}✓ 成功${NC}"
  else
    error_count=$((error_count + 1))
    failed_lists+=("$relative_path")
    echo -e "${RED}✗ 失敗${NC}"
  fi
  echo ""
done

# ============================================
# サマリーレポート
# ============================================

# 実行時間計算
end_time=$(date +%s)
elapsed_time=$((end_time - start_time))

echo "============================================"
echo "処理完了サマリー"
echo "============================================"
echo -e "成功: ${GREEN}$success_count${NC}"
echo -e "失敗: ${RED}$error_count${NC}"
echo "実行時間: ${elapsed_time}秒"

# 失敗したリストがある場合は表示
if [ $error_count -gt 0 ]; then
  echo ""
  echo -e "${RED}失敗したリスト:${NC}"
  for failed in "${failed_lists[@]}"; do
    echo "  - $failed"
  done
fi

echo "============================================"
echo ""

# 終了コード
if [ $error_count -gt 0 ]; then
  exit 1
else
  exit 0
fi
