#!/bin/bash
set -euo pipefail

# 使用方法チェック
if [ $# -eq 0 ]; then
    echo "エラー: リリースキーを指定してください"
    echo "使用方法: $0 <RELEASE_KEY>"
    exit 1
fi

RELEASE_KEY="$1"
OUTPUT_DIR="domain/raw-data/masterdata/released/${RELEASE_KEY}"
SPECS_DIR="${OUTPUT_DIR}/specs"
SPREADSHEET_LIST="${SPECS_DIR}/spreadsheet_list.csv"
SPECS_CSV="${SPECS_DIR}/specs.csv"
GOOGLE_DRIVE_DIR="domain/raw-data/google-drive/spread-sheet"
MISSING_LOG="${SPECS_DIR}/missing_spreadsheets.log"

# spreadsheet_list.csv が存在しない場合は終了
if [ ! -f "$SPREADSHEET_LIST" ]; then
    echo "注意: spreadsheet_list.csv が見つかりません。"
    echo "パス: $SPREADSHEET_LIST"
    exit 0
fi

echo "=== specs.csv 生成中 ==="

# ディレクトリ作成
mkdir -p "$SPECS_DIR"

# ヘッダー作成
echo "description,path" > "$SPECS_CSV"

# 見つからないファイルのログ初期化
echo "# 見つからなかった運営仕様書" > "$MISSING_LOG"
echo "# 生成日時: $(date)" >> "$MISSING_LOG"
echo "" >> "$MISSING_LOG"

found_count=0
missing_count=0

# spreadsheet_list.csv を1行ずつ処理（ヘッダー行をスキップ）
while IFS=',' read -r spreadsheet_name spreadsheet_url source_name; do
    # ヘッダー行やコメント行をスキップ
    if [[ "$spreadsheet_name" =~ ^(spreadsheet_name|#) ]] || [ -z "$spreadsheet_name" ]; then
        continue
    fi

    # クォートを削除
    spreadsheet_name=$(echo "$spreadsheet_name" | sed 's/^"//;s/"$//')
    spreadsheet_url=$(echo "$spreadsheet_url" | sed 's/^"//;s/"$//')

    # 1. 完全一致検索
    found_path=$(find "$GOOGLE_DRIVE_DIR" -type d -name "$spreadsheet_name" 2>/dev/null | head -1)

    if [ -z "$found_path" ]; then
        # 2. 部分一致検索（特殊文字対応）
        found_path=$(find "$GOOGLE_DRIVE_DIR" -type d -iname "*${spreadsheet_name}*" 2>/dev/null | head -1)
    fi

    if [ -n "$found_path" ]; then
        # 見つかった場合、相対パスとして記録
        echo "local,\"$found_path\"" >> "$SPECS_CSV"
        found_count=$((found_count + 1))
    else
        # 見つからない場合、GoogleドライブURLを記録
        echo "\"Googleドライブ: ${spreadsheet_name}\",\"${spreadsheet_url}\"" >> "$SPECS_CSV"
        echo "$spreadsheet_name" >> "$MISSING_LOG"
        missing_count=$((missing_count + 1))
    fi
done < <(tail -n +2 "$SPREADSHEET_LIST")

echo "specs.csv を生成しました: $SPECS_CSV"
echo "  ローカルで見つかった数: $found_count"
echo "  見つからなかった数: $missing_count"

if [ "$missing_count" -gt 0 ]; then
    echo ""
    echo "警告: 一部の運営仕様書がローカルに見つかりませんでした"
    echo "詳細: $MISSING_LOG"
fi
