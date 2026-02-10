#!/bin/bash
set -uo pipefail

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
echo "path" > "$SPECS_CSV"

# 見つからないファイルのログ初期化
echo "# 見つからなかった運営仕様書" > "$MISSING_LOG"
echo "# 生成日時: $(date)" >> "$MISSING_LOG"
echo "" >> "$MISSING_LOG"

found_count=0
missing_count=0

# 一時ファイルを作成（BOM除去済み）
TEMP_CSV=$(mktemp)
tail -n +2 "$SPREADSHEET_LIST" | sed '1s/^\xEF\xBB\xBF//' > "$TEMP_CSV"

# spreadsheet_list.csv を1行ずつ処理
while IFS=',' read -r spreadsheet_name spreadsheet_url source_name; do
    # ヘッダー行やコメント行をスキップ
    if [[ "$spreadsheet_name" =~ ^(spreadsheet_name|スプシ名|#) ]] || [ -z "$spreadsheet_name" ]; then
        continue
    fi

    # クォート、前後の空白、BOMを削除
    spreadsheet_name=$(echo "$spreadsheet_name" | sed 's/^"//;s/"$//;s/^\xEF\xBB\xBF//;s/^[[:space:]]*//;s/[[:space:]]*$//')
    spreadsheet_url=$(echo "$spreadsheet_url" | sed 's/^"//;s/"$//;s/^[[:space:]]*//;s/[[:space:]]*$//')

    # デバッグ出力
    echo "検索中: [$spreadsheet_name]" >&2

    # Unicode正規化（NFC→NFD）を行う（macOSのファイルシステムはNFD）
    # iconv を使ってUTF-8-MAC（NFD）に変換
    spreadsheet_name_nfd=$(echo "$spreadsheet_name" | iconv -f UTF-8 -t UTF-8-MAC 2>/dev/null || echo "$spreadsheet_name")

    # 1. NFD形式で完全一致検索
    found_path=$(find "$GOOGLE_DRIVE_DIR" -type d -name "${spreadsheet_name_nfd}" 2>/dev/null | head -1)

    if [ -z "$found_path" ]; then
        # 2. NFD形式でパス全体での完全一致検索
        found_path=$(find "$GOOGLE_DRIVE_DIR" -type d 2>/dev/null | grep -F "/${spreadsheet_name_nfd}$" | head -1)
    fi

    if [ -z "$found_path" ]; then
        # 3. 元のNFC形式で部分一致検索（フォールバック）
        found_path=$(find "$GOOGLE_DRIVE_DIR" -type d 2>/dev/null | grep -F "${spreadsheet_name}" | head -1)
    fi

    if [ -n "$found_path" ]; then
        # 見つかった場合、相対パスとして記録
        echo "\"$found_path\"" >> "$SPECS_CSV"
        found_count=$((found_count + 1))
        echo "  ✓ 見つかりました: $found_path" >&2
    else
        # 見つからない場合、GoogleドライブURLを記録
        echo "\"${spreadsheet_url}\"" >> "$SPECS_CSV"
        echo "$spreadsheet_name" >> "$MISSING_LOG"
        missing_count=$((missing_count + 1))
        echo "  ✗ 見つかりません" >&2
    fi
done < "$TEMP_CSV"

# 一時ファイル削除
rm -f "$TEMP_CSV"

echo "specs.csv を生成しました: $SPECS_CSV"
echo "  ローカルで見つかった数: $found_count"
echo "  見つからなかった数: $missing_count"

if [ "$missing_count" -gt 0 ]; then
    echo ""
    echo "警告: 一部の運営仕様書がローカルに見つかりませんでした"
    echo "詳細: $MISSING_LOG"
fi
