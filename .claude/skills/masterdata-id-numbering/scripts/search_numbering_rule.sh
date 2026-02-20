#!/bin/bash
# ID割り振りルール.csvから指定カテゴリーの採番ルールを検索

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/config.sh"

# 引数チェック
if [ $# -lt 1 ]; then
    echo "使用方法: $0 <カテゴリー名>" >&2
    echo "" >&2
    echo "例: $0 キャラ" >&2
    echo "例: $0 クエスト" >&2
    echo "" >&2
    echo "利用可能なカテゴリー一覧を表示: ./list_categories.sh" >&2
    exit 1
fi

CATEGORY="$1"

# CSVからカテゴリーを検索（部分一致）
# カラム3=カテゴリー, カラム4=ID付与ルール, カラム5=最大ID桁数, カラム6=備考
RESULT=$(awk -F',' -v cat="$CATEGORY" '
NR > 6 && $3 != "" && $3 ~ cat {
    printf "カテゴリー: %s\n", $3
    printf "最大ID桁数: %s\n", $5
    printf "\nID付与ルール:\n%s\n", $4
    if ($6 != "") {
        printf "\n備考: %s\n", $6
    }
    printf "\n----------------------------------------\n"
    found = 1
}
END {
    if (!found) {
        exit 1
    }
}
' "$ID_RULE_CSV")

if [ $? -ne 0 ]; then
    echo "エラー: カテゴリー '$CATEGORY' が見つかりませんでした" >&2
    echo "" >&2
    echo "利用可能なカテゴリー一覧を表示: ./list_categories.sh" >&2
    exit 1
fi

echo "=== ID採番ルール検索結果 ==="
echo ""
echo "$RESULT"
