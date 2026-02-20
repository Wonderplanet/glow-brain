#!/bin/bash
set -euo pipefail

# 使用方法チェック
if [ $# -eq 0 ]; then
    echo "エラー: リリースキーを指定してください"
    echo "使用方法: $0 <RELEASE_KEY>"
    exit 1
fi

RELEASE_KEY="$1"
MASTERDATA_DIR="projects/glow-masterdata"
OUTPUT_DIR="domain/raw-data/masterdata/released/${RELEASE_KEY}"
PAST_TABLES_DIR="${OUTPUT_DIR}/past_tables"
STATS_DIR="${OUTPUT_DIR}/stats"

# DuckDB 存在確認
if ! command -v duckdb &> /dev/null; then
    echo "エラー: duckdbコマンドが見つかりません"
    echo "  macOS: brew install duckdb"
    exit 1
fi

# ディレクトリ作成
mkdir -p "$PAST_TABLES_DIR"
mkdir -p "$STATS_DIR"

echo "=== リリースキー ${RELEASE_KEY} 過去データ抽出（DuckDB）==="

# テーブル別に処理
past_file_count=0
past_total_rows=0
TEMP_TABLE_STATS=$(mktemp)
echo "{}" > "$TEMP_TABLE_STATS"

for csv_file in "${MASTERDATA_DIR}"/*.csv; do
    filename=$(basename "$csv_file")
    table_name="${filename%.csv}"
    output_csv="${PAST_TABLES_DIR}/${filename}"

    # release_key カラムが存在するかチェック（CSVヘッダーを確認）
    if ! head -1 "$csv_file" | grep -q "release_key"; then
        continue
    fi

    # DuckDBで過去データを抽出してCSV出力
    row_count=0
    if duckdb :memory: <<EOF
COPY (
    SELECT *
    FROM read_csv('${csv_file}', AUTO_DETECT=TRUE, nullstr='__NULL__', SAMPLE_SIZE=-1)
    WHERE release_key < ${RELEASE_KEY}
      AND release_key IS NOT NULL
) TO '${output_csv}' (HEADER true, DELIMITER ',');
EOF
    then
        # 出力されたCSVが存在し、データ行がある場合のみカウント
        if [ -f "$output_csv" ] && [ $(wc -l < "$output_csv") -gt 1 ]; then
            # ヘッダー行を除いた行数をカウント
            row_count=$(($(wc -l < "$output_csv") - 1))
        else
            # 空のファイルを削除
            rm -f "$output_csv"
            row_count=0
        fi
    fi

    if [ "$row_count" -gt 0 ]; then
        past_file_count=$((past_file_count + 1))
        past_total_rows=$((past_total_rows + row_count))

        # カラム数を取得
        column_count=$(head -1 "$output_csv" | awk -F',' '{print NF}')

        # ファイルサイズ取得
        file_size=$(du -k "$output_csv" | cut -f1)

        # カテゴリ判定
        category="Other"
        if [[ "$table_name" =~ ^Mst.*I18n$ ]]; then
            category="I18n"
        elif [[ "$table_name" =~ ^Mst ]]; then
            category="Mst"
        elif [[ "$table_name" =~ ^Opr ]]; then
            category="Opr"
        fi

        # テーブル統計をJSON形式で追加
        table_stat=$(cat <<STATEOF
{
  "rows": $row_count,
  "columns": $column_count,
  "category": "$category",
  "file_size_kb": $file_size
}
STATEOF
)
        jq --arg table "$table_name" --argjson stat "$table_stat" \
            '. + {($table): $stat}' "$TEMP_TABLE_STATS" > "${TEMP_TABLE_STATS}.tmp"
        mv "${TEMP_TABLE_STATS}.tmp" "$TEMP_TABLE_STATS"

        echo "  ${table_name}: ${row_count}行"
    fi
done

# past_tables.json を生成
cp "$TEMP_TABLE_STATS" "${STATS_DIR}/past_tables.json"

# summary.json の past_tables セクションを更新（既存のsummary.jsonに追加）
extraction_date=$(date -u +"%Y-%m-%dT%H:%M:%SZ")

if [ -f "${STATS_DIR}/summary.json" ]; then
    # 既存のsummary.jsonに past_tables セクションを追加
    jq --argjson past_tables "{\"total_tables\": $past_file_count, \"total_rows\": $past_total_rows}" \
        '. + {past_tables: $past_tables}' "${STATS_DIR}/summary.json" > "${STATS_DIR}/summary.json.tmp"
    mv "${STATS_DIR}/summary.json.tmp" "${STATS_DIR}/summary.json"
else
    # summary.json が存在しない場合は新規作成
    cat > "${STATS_DIR}/summary.json" <<EOF
{
  "release_key": "$RELEASE_KEY",
  "extraction_date": "$extraction_date",
  "past_tables": {
    "total_tables": $past_file_count,
    "total_rows": $past_total_rows
  }
}
EOF
fi

# 一時ファイル削除
rm -f "$TEMP_TABLE_STATS"

echo ""
echo "抽出完了: ${past_file_count}テーブル、${past_total_rows}行"
echo "出力先:"
echo "  - ${PAST_TABLES_DIR}/"
echo "  - ${STATS_DIR}/past_tables.json"
echo "  - ${STATS_DIR}/summary.json (past_tablesセクション更新)"
