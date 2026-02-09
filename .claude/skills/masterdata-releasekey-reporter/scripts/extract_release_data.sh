#!/bin/bash

set -euo pipefail

# リリースキーデータ抽出スクリプト v2
# 使用方法: ./extract_release_data.sh <RELEASE_KEY>

if [ $# -eq 0 ]; then
    echo "エラー: リリースキーを指定してください"
    echo "使用方法: $0 <RELEASE_KEY>"
    echo "例: $0 202512020"
    exit 1
fi

RELEASE_KEY="$1"
MASTERDATA_DIR="projects/glow-masterdata"
OUTPUT_DIR="domain/raw-data/masterdata/released/${RELEASE_KEY}"
TABLES_DIR="${OUTPUT_DIR}/tables"
STATS_DIR="${OUTPUT_DIR}/stats"

# マスタデータディレクトリの存在確認
if [ ! -d "$MASTERDATA_DIR" ]; then
    echo "エラー: マスタデータディレクトリが見つかりません: $MASTERDATA_DIR"
    echo "glow-brainリポジトリのルートディレクトリで実行してください"
    exit 1
fi

# jqの存在確認
if ! command -v jq &> /dev/null; then
    echo "エラー: jqコマンドが見つかりません。インストールしてください。"
    echo "  macOS: brew install jq"
    echo "  Linux: sudo apt-get install jq"
    exit 1
fi

# リリースキーを含むファイルを検索
files=$(grep -l "$RELEASE_KEY" "$MASTERDATA_DIR"/*.csv 2>/dev/null | sort || true)

if [ -z "$files" ]; then
    echo "エラー: リリースキー ${RELEASE_KEY} を含むデータが見つかりませんでした"
    exit 1
fi

# 出力ディレクトリを作成
mkdir -p "$TABLES_DIR"
mkdir -p "$STATS_DIR"

echo "=== リリースキー ${RELEASE_KEY} マスタデータ抽出 ==="
echo ""

file_count=0
total_rows=0

# テーブル別の統計を保存する一時ファイル
TEMP_TABLE_STATS=$(mktemp)
echo "{}" > "$TEMP_TABLE_STATS"

# カテゴリ別統計
mst_tables=0
mst_rows=0
opr_tables=0
opr_rows=0
i18n_tables=0
i18n_rows=0

# テーブル別CSVファイル出力
for file in $files; do
    filename=$(basename "$file")
    table_name="${filename%.csv}"

    # ヘッダー行を取得
    header=$(head -n 1 "$file")

    # リリースキーを含む行を抽出
    data_lines=$(grep "$RELEASE_KEY" "$file" | wc -l | tr -d ' ')

    if [ "$data_lines" -gt 0 ]; then
        file_count=$((file_count + 1))
        total_rows=$((total_rows + data_lines))

        # テーブル別CSVファイルを作成
        output_csv="${TABLES_DIR}/${table_name}.csv"
        echo "$header" > "$output_csv"
        grep "$RELEASE_KEY" "$file" >> "$output_csv"

        # ファイルサイズを取得（KB単位）
        file_size=$(du -k "$output_csv" | cut -f1)

        # カラム数を取得（ヘッダーのカンマ数+1）
        column_count=$(echo "$header" | awk -F',' '{print NF}')

        # カテゴリ判定
        category="Other"
        if [[ "$table_name" =~ ^Mst.*I18n$ ]]; then
            category="I18n"
            i18n_tables=$((i18n_tables + 1))
            i18n_rows=$((i18n_rows + data_lines))
        elif [[ "$table_name" =~ ^Mst ]]; then
            category="Mst"
            mst_tables=$((mst_tables + 1))
            mst_rows=$((mst_rows + data_lines))
        elif [[ "$table_name" =~ ^Opr ]]; then
            category="Opr"
            opr_tables=$((opr_tables + 1))
            opr_rows=$((opr_rows + data_lines))
        fi

        # テーブル統計をJSON形式で追加
        table_stat=$(cat <<EOF
{
  "rows": $data_lines,
  "columns": $column_count,
  "category": "$category",
  "file_size_kb": $file_size
}
EOF
)
        jq --arg table "$table_name" --argjson stat "$table_stat" \
            '. + {($table): $stat}' "$TEMP_TABLE_STATS" > "${TEMP_TABLE_STATS}.tmp"
        mv "${TEMP_TABLE_STATS}.tmp" "$TEMP_TABLE_STATS"
    fi
done

# tables.json を生成
cp "$TEMP_TABLE_STATS" "${STATS_DIR}/tables.json"

# summary.json を生成
extraction_date=$(date -u +"%Y-%m-%dT%H:%M:%SZ")

# largest_tables トップ10を抽出
largest_tables=$(jq -r 'to_entries | sort_by(-.value.rows) | .[0:10] |
    map({name: .key, rows: .value.rows})' "$TEMP_TABLE_STATS")

summary=$(cat <<EOF
{
  "release_key": "$RELEASE_KEY",
  "extraction_date": "$extraction_date",
  "total_tables": $file_count,
  "total_rows": $total_rows,
  "categories": {
    "Mst": {
      "tables": $mst_tables,
      "rows": $mst_rows
    },
    "Opr": {
      "tables": $opr_tables,
      "rows": $opr_rows
    },
    "I18n": {
      "tables": $i18n_tables,
      "rows": $i18n_rows
    }
  },
  "largest_tables": $largest_tables
}
EOF
)

echo "$summary" | jq . > "${STATS_DIR}/summary.json"

# 一時ファイル削除
rm -f "$TEMP_TABLE_STATS"

echo "抽出完了: ${file_count}テーブル、${total_rows}行"
echo ""
echo "出力先:"
echo "  テーブル別CSV: $TABLES_DIR/"
echo "  統計情報:"
echo "    - ${STATS_DIR}/summary.json"
echo "    - ${STATS_DIR}/tables.json"
echo ""
echo "次のステップ: Claudeが統計JSONとテーブルCSVを分析してレポートを作成します"
