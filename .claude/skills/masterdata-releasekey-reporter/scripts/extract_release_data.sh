#!/bin/bash

set -euo pipefail

# リリースキーデータ抽出スクリプト
# 使用方法: ./extract_release_data.sh <RELEASE_KEY>

if [ $# -eq 0 ]; then
    echo "エラー: リリースキーを指定してください"
    echo "使用方法: $0 <RELEASE_KEY>"
    echo "例: $0 202512020"
    exit 1
fi

RELEASE_KEY="$1"
MASTERDATA_DIR="projects/glow-masterdata"
OUTPUT_DIR="マスタデータ/リリース/${RELEASE_KEY}"
OUTPUT_RAW="${OUTPUT_DIR}/release_${RELEASE_KEY}_raw_data.txt"

# マスタデータディレクトリの存在確認
if [ ! -d "$MASTERDATA_DIR" ]; then
    echo "エラー: マスタデータディレクトリが見つかりません: $MASTERDATA_DIR"
    echo "glow-brainリポジトリのルートディレクトリで実行してください"
    exit 1
fi

# リリースキーを含むファイルを検索
files=$(grep -l "$RELEASE_KEY" "$MASTERDATA_DIR"/*.csv 2>/dev/null | sort || true)

if [ -z "$files" ]; then
    echo "エラー: リリースキー ${RELEASE_KEY} を含むデータが見つかりませんでした"
    exit 1
fi

# 出力ディレクトリを作成
mkdir -p "$OUTPUT_DIR"

echo "=== リリースキー ${RELEASE_KEY} マスタデータ抽出 ==="
echo ""

# 初期化
> "$OUTPUT_RAW"

file_count=0
total_rows=0

# rawデータ抽出
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

        # rawデータファイルに追記
        echo "######## $table_name ########" >> "$OUTPUT_RAW"
        echo "$header" >> "$OUTPUT_RAW"
        grep "$RELEASE_KEY" "$file" >> "$OUTPUT_RAW"
        echo "" >> "$OUTPUT_RAW"
    fi
done

echo "抽出完了: ${file_count}テーブル、${total_rows}行"
echo ""
echo "rawデータ: $OUTPUT_RAW"
echo ""
echo "次のステップ: Claudeがこのrawデータを分析してレポートを作成します"
