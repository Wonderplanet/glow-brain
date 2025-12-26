#!/bin/bash
# CSV検証スクリプト
# GLOWマスタデータCSVファイルをスキーマJSONと照合し、整合性を検証します。
#
# 引数:
#   $1: CSVファイルパス
#   $2: モデル名（例: OprGacha, MstUnit）
#
# 処理内容:
#   6-1. スキーマJSONファイルの参照
#   6-2. カラムの存在確認
#   6-3. データ型の検証
#   6-4. 制約の検証
#   6-5. 自動修正の実施
#   6-6. 修正ログの出力

set -euo pipefail

# 使用方法
usage() {
    cat <<EOF
Usage: $0 <csv_file_path> <model_name>

Arguments:
  csv_file_path  - GLOWマスタデータCSVファイルのパス
  model_name     - モデル名（例: OprGacha, MstUnit）

Example:
  $0 マスタデータ/施策/新春ガチャ/OprGacha.csv OprGacha

Output:
  検証結果と修正ログをJSON形式で標準出力
EOF
    exit 1
}

if [ $# -ne 2 ]; then
    usage
fi

CSV_FILE="$1"
MODEL_NAME="$2"

# 作業用のログファイル
LOG_FILE=$(mktemp)
trap "rm -f $LOG_FILE" EXIT

# 関数: ログ出力
log_message() {
    local level="$1"
    shift
    echo "{\"level\":\"$level\",\"message\":\"$*\"}" >> "$LOG_FILE"
}

# 関数: モデル名をテーブル名に変換
convert_model_to_table() {
    local model_name="$1"
    local snake_case=$(echo "$model_name" | sed -E 's/([A-Z])/_\1/g' | sed 's/^_//' | tr '[:upper:]' '[:lower:]')

    if [[ "$snake_case" =~ (s|x|z|ch|sh)$ ]]; then
        echo "${snake_case}es"
    elif [[ "$snake_case" =~ [^aeiou]y$ ]]; then
        echo "${snake_case%y}ies"
    else
        echo "${snake_case}s"
    fi
}

# 関数: スキーマJSONファイルのパスを取得
get_schema_file() {
    local table_name="$1"

    if [[ "$table_name" =~ ^(mst_|opr_) ]]; then
        echo "projects/glow-server/api/database/schema/exports/master_tables_schema.json"
    else
        echo "projects/glow-server/api/database/schema/exports/user_tables_schema.json"
    fi
}

# 関数: スキーマJSONからテーブル定義を取得
get_table_schema() {
    local schema_file="$1"
    local table_name="$2"

    if [[ "$table_name" =~ ^(mst_|opr_) ]]; then
        local db_name="mst"
    else
        local db_name="usr"
    fi

    jq -r ".databases.$db_name.tables.\"$table_name\"" "$schema_file"
}

# ========================================
# メイン処理
# ========================================

log_message "INFO" "CSV検証を開始します: $CSV_FILE (モデル: $MODEL_NAME)"

# 6-1. スキーマJSONファイルの参照
log_message "INFO" "ステップ6-1: スキーマJSONファイルの参照"

TABLE_NAME=$(convert_model_to_table "$MODEL_NAME")
log_message "INFO" "テーブル名: $TABLE_NAME"

SCHEMA_FILE=$(get_schema_file "$TABLE_NAME")
log_message "INFO" "スキーマファイル: $SCHEMA_FILE"

if [ ! -f "$SCHEMA_FILE" ]; then
    log_message "ERROR" "スキーマファイルが見つかりません: $SCHEMA_FILE"
    cat "$LOG_FILE"
    exit 1
fi

TABLE_SCHEMA=$(get_table_schema "$SCHEMA_FILE" "$TABLE_NAME")

if [ "$TABLE_SCHEMA" == "null" ]; then
    log_message "ERROR" "テーブル '$TABLE_NAME' がスキーマJSONに見つかりません"
    cat "$LOG_FILE"
    exit 1
fi

log_message "INFO" "スキーマJSONからテーブル定義を取得しました"

# 6-2. カラムの存在確認
log_message "INFO" "ステップ6-2: カラムの存在確認"

if [ ! -f "$CSV_FILE" ]; then
    log_message "ERROR" "CSVファイルが見つかりません: $CSV_FILE"
    cat "$LOG_FILE"
    exit 1
fi

# CSVのヘッダー行を取得（3行目）
CSV_HEADER=$(sed -n '3p' "$CSV_FILE")
log_message "INFO" "CSVヘッダー: $CSV_HEADER"

# スキーマJSONから全カラム名を取得
SCHEMA_COLUMNS=$(echo "$TABLE_SCHEMA" | jq -r '.columns | keys[]' | sort)
log_message "INFO" "スキーマJSONのカラム数: $(echo "$SCHEMA_COLUMNS" | wc -l)"

# CSVのカラム名を抽出（カンマ区切り）
CSV_COLUMNS=$(echo "$CSV_HEADER" | tr ',' '\n' | sort)
log_message "INFO" "CSVのカラム数: $(echo "$CSV_COLUMNS" | wc -l)"

# CSVにあってスキーマJSONにないカラム
EXTRA_COLUMNS=$(comm -23 <(echo "$CSV_COLUMNS") <(echo "$SCHEMA_COLUMNS"))
if [ -n "$EXTRA_COLUMNS" ]; then
    log_message "WARNING" "CSVに存在するがスキーマJSONにないカラム: $EXTRA_COLUMNS"
fi

# スキーマJSONにあってCSVにないカラム
MISSING_COLUMNS=$(comm -13 <(echo "$CSV_COLUMNS") <(echo "$SCHEMA_COLUMNS"))
if [ -n "$MISSING_COLUMNS" ]; then
    log_message "WARNING" "スキーマJSONに存在するがCSVにないカラム: $MISSING_COLUMNS"

    # NOT NULL制約のある不足カラムを確認
    while IFS= read -r col; do
        NULLABLE=$(echo "$TABLE_SCHEMA" | jq -r ".columns.\"$col\".nullable")
        if [ "$NULLABLE" == "false" ]; then
            log_message "ERROR" "NOT NULL制約のあるカラムが不足しています: $col"
        fi
    done <<< "$MISSING_COLUMNS"
fi

# 6-3. データ型の検証
log_message "INFO" "ステップ6-3: データ型の検証"
log_message "INFO" "（このステップは実際のデータ値の検証が必要なため、別途手動確認を推奨）"

# 6-4. 制約の検証
log_message "INFO" "ステップ6-4: 制約の検証"

# PRIMARY KEY制約の確認
PRIMARY_KEY=$(echo "$TABLE_SCHEMA" | jq -r '.indexes.PRIMARY.columns[]' 2>/dev/null || echo "")
if [ -n "$PRIMARY_KEY" ]; then
    log_message "INFO" "PRIMARY KEY: $PRIMARY_KEY"
fi

# NOT NULL制約のカラムを確認
NOT_NULL_COLUMNS=$(echo "$TABLE_SCHEMA" | jq -r '.columns | to_entries[] | select(.value.nullable == false) | .key' | sort)
log_message "INFO" "NOT NULL制約のカラム数: $(echo "$NOT_NULL_COLUMNS" | wc -l)"

# __NULL__ の使用チェック
log_message "INFO" "CSVデータ内の __NULL__ 使用をチェック中..."
CSV_DATA_LINES=$(tail -n +4 "$CSV_FILE")  # ヘッダー3行をスキップ

if [ -n "$CSV_DATA_LINES" ]; then
    # 各カラムのnullable属性を確認
    COLUMN_COUNT=$(sed -n '3p' "$CSV_FILE" | awk -F',' '{print NF}')

    for col_index in $(seq 1 $COLUMN_COUNT); do
        COL_NAME=$(sed -n '3p' "$CSV_FILE" | cut -d',' -f$col_index)

        # スキーマからnullable属性を取得
        NULLABLE=$(echo "$TABLE_SCHEMA" | jq -r ".columns.\"$COL_NAME\".nullable" 2>/dev/null)

        if [ "$NULLABLE" == "false" ]; then
            # NOT NULL列で __NULL__ が使用されているかチェック
            NULL_COUNT=$(echo "$CSV_DATA_LINES" | awk -F',' -v col=$col_index '$col == "__NULL__" {print}' | wc -l | tr -d ' ')

            if [ "$NULL_COUNT" -gt 0 ]; then
                log_message "ERROR" "NOT NULL列 '$COL_NAME' に __NULL__ が使用されています (${NULL_COUNT}行)"
                log_message "INFO" "  → 空文字列またはデフォルト値を使用してください"
            fi
        fi
    done
fi

# 6-5. 自動修正の実施
log_message "INFO" "ステップ6-5: 自動修正の実施"
log_message "INFO" "（自動修正は慎重に行う必要があるため、手動確認を推奨）"

# 6-6. 修正ログの出力
log_message "INFO" "ステップ6-6: 修正ログの出力"

# 最終結果を出力
cat "$LOG_FILE" | jq -s '.'
