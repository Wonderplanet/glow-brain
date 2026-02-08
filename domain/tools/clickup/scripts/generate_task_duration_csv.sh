#!/bin/bash

set -euo pipefail

# 使い方チェック
if [ $# -ne 1 ]; then
  echo "使い方: $0 <フォルダ/リストのパス>" >&2
  echo "例: $0 'GLOW(開発)/v1.5.0'" >&2
  echo "例: $0 'QA/アプリQA_Ver1.5.0'" >&2
  exit 1
fi

# パスを分割してフォルダ名とリスト名を取得
FOLDER_LIST_PATH="$1"
FOLDER_NAME=$(dirname "$FOLDER_LIST_PATH")
LIST_NAME=$(basename "$FOLDER_LIST_PATH")

# ディレクトリパス設定
MEMBERS_CSV="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/knowledge/project-structure/メンバー一覧.csv"
RAW_DATA_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/raw-data/clickup/GLOW/${FOLDER_NAME}/${LIST_NAME}"
OUTPUT_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/knowledge/project-management/clickup/GLOW/${FOLDER_NAME}/${LIST_NAME}"
OUTPUT_CSV="${OUTPUT_DIR}/タスク対応期間.csv"

# 入力ディレクトリの存在確認
if [ ! -d "$RAW_DATA_DIR" ]; then
  echo "エラー: 入力ディレクトリが存在しません: $RAW_DATA_DIR" >&2
  exit 1
fi

# 出力ディレクトリ作成
mkdir -p "$OUTPUT_DIR"

# 一時ファイル
TMP_MEMBER_MAP=$(mktemp)
TMP_DATA=$(mktemp)
TMP_CSV_UNSORTED=$(mktemp)

# クリーンアップ関数
cleanup() {
  rm -f "$TMP_MEMBER_MAP" "$TMP_DATA" "$TMP_CSV_UNSORTED"
}
trap cleanup EXIT

echo "============================================" >&2
echo "タスク対応期間CSV生成" >&2
echo "フォルダ: $FOLDER_NAME" >&2
echo "リスト: $LIST_NAME" >&2
echo "============================================" >&2
echo "" >&2

# メンバー辞書作成（email → 名前,専門領域）
echo "メンバー辞書を作成中..." >&2
tail -n +2 "$MEMBERS_CSV" | awk -F',' '
{
  if (NF >= 6) {
    name = $1
    senmon_ryoiki = $4
    email = $6
    gsub(/^[[:space:]]+|[[:space:]]+$/, "", name)
    gsub(/^[[:space:]]+|[[:space:]]+$/, "", senmon_ryoiki)
    gsub(/^[[:space:]]+|[[:space:]]+$/, "", email)
    if (email != "") {
      print email "→" name "→" senmon_ryoiki
    }
  }
}' > "$TMP_MEMBER_MAP"

# CSVヘッダー出力（一時ファイルに出力）
echo "名前,専門領域,タスクID,タスク名,フォルダ名,リスト名,開始日時,終了日時" > "$TMP_CSV_UNSORTED"

# 全タスクを処理
echo "タスクデータを抽出中..." >&2
task_count=0
assignee_count=0

for raw_json in "$RAW_DATA_DIR"/*/raw.json; do
  if [ ! -f "$raw_json" ]; then
    continue
  fi

  task_count=$((task_count + 1))

  # jqでタスク情報を抽出
  jq -r '
    .task |
    {
      task_id: .id,
      task_name: .name,
      folder_name: .folder.name,
      list_name: .list.name,
      start_date: .start_date,
      due_date: .due_date,
      assignees: .assignees | map({email: .email, username: .username})
    } |
    # 担当者がいない場合はスキップ
    select(.assignees | length > 0) |
    # 各担当者について1行ずつ出力
    .assignees[] as $assignee |
    [
      $assignee.email,
      .task_id,
      .task_name,
      .folder_name,
      .list_name,
      .start_date,
      .due_date
    ] |
    @tsv
  ' "$raw_json" >> "$TMP_DATA" 2>/dev/null || true
done

echo "全 $task_count 個のタスクを処理しました" >&2

# データが空の場合の処理
if [ ! -s "$TMP_DATA" ]; then
  echo "警告: 担当者が設定されているタスクが見つかりませんでした" >&2
  echo "空のCSVファイルを作成しました: $OUTPUT_CSV" >&2
  exit 0
fi

# データをCSVに変換
echo "CSVデータを生成中..." >&2
unknown_emails=()

while IFS=$'\t' read -r email task_id task_name folder_name list_name start_date due_date; do
  # メンバー辞書から名前と専門領域を検索
  member_info=$(grep "^${email}→" "$TMP_MEMBER_MAP" || echo "")

  if [ -n "$member_info" ]; then
    name=$(echo "$member_info" | cut -d'→' -f2)
    senmon_ryoiki=$(echo "$member_info" | cut -d'→' -f3)
  else
    name="不明"
    senmon_ryoiki="不明"
    # 不明なメールアドレスを記録
    if [[ ! " ${unknown_emails[@]:-} " =~ " ${email} " ]]; then
      unknown_emails+=("$email")
    fi
  fi

  assignee_count=$((assignee_count + 1))

  # 日時変換（ミリ秒からISO 8601形式へ）
  if [ "$start_date" != "null" ] && [ -n "$start_date" ]; then
    start_date_formatted=$(date -r $((start_date / 1000)) '+%Y-%m-%d %H:%M:%S' 2>/dev/null || echo "")
  else
    start_date_formatted=""
  fi

  if [ "$due_date" != "null" ] && [ -n "$due_date" ]; then
    due_date_formatted=$(date -r $((due_date / 1000)) '+%Y-%m-%d %H:%M:%S' 2>/dev/null || echo "")
  else
    due_date_formatted=""
  fi

  # CSVエスケープ処理（ダブルクォートで囲む、内部のダブルクォートは2重にする）
  escape_csv() {
    local value="$1"
    # ダブルクォートを2重にする
    value="${value//\"/\"\"}"
    # カンマ、改行、ダブルクォートを含む場合はダブルクォートで囲む
    if [[ "$value" =~ [,\"\n] ]]; then
      echo "\"$value\""
    else
      echo "$value"
    fi
  }

  # CSV行を出力（一時ファイルに出力）
  echo "$(escape_csv "$name"),$(escape_csv "$senmon_ryoiki"),$(escape_csv "$task_id"),$(escape_csv "$task_name"),$(escape_csv "$folder_name"),$(escape_csv "$list_name"),$start_date_formatted,$due_date_formatted" >> "$TMP_CSV_UNSORTED"
done < "$TMP_DATA"

# ソート処理
echo "CSVデータをソート中..." >&2
# ヘッダー行を抽出
head -1 "$TMP_CSV_UNSORTED" > "$OUTPUT_CSV"
# データ行をソートして追加
tail -n +2 "$TMP_CSV_UNSORTED" | sort -t, -k1,1 -k2,2 -k3,3 >> "$OUTPUT_CSV"

echo "" >&2
echo "============================================" >&2
echo "CSV生成完了: $OUTPUT_CSV" >&2
echo "============================================" >&2
echo "データ行数: $assignee_count" >&2

# 不明なメールアドレスがある場合は警告
if [ ${#unknown_emails[@]:-0} -gt 0 ]; then
  echo "" >&2
  echo "⚠️  メンバー一覧に存在しないメールアドレス:" >&2
  for unknown_email in "${unknown_emails[@]}"; do
    echo "  - $unknown_email" >&2
  done
fi

echo "" >&2
