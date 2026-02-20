#!/bin/bash
# ID割り振りルール.csvから全カテゴリーを一覧表示

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/config.sh"

echo "=== GLOWマスタデータ ID採番ルール カテゴリー一覧 ==="
echo ""
echo "データソース: $ID_RULE_CSV"
echo ""

# CSVからカテゴリー、ID付与ルール、最大ID桁数を抽出（ヘッダー行をスキップ）
# カラム3=カテゴリー, カラム4=ID付与ルール, カラム5=最大ID桁数
awk -F',' '
BEGIN {
    print "カテゴリー | 最大桁数 | ID付与ルール（概要）"
    print "---------|---------|--------------------"
}
NR > 6 && $3 != "" {
    # カテゴリー名を取得
    category = $3
    # 最大桁数を取得
    max_digits = $5
    # ID付与ルールの最初の行（例を除く）を取得
    rule = $4
    # 改行を削除し、最初の100文字のみ表示
    gsub(/\n/, " ", rule)
    if (length(rule) > 100) {
        rule = substr(rule, 1, 100) "..."
    }
    printf "%-12s | %-8s | %s\n", category, max_digits, rule
}
' "$ID_RULE_CSV"

echo ""
echo "詳細な採番ルールを確認するには: ./search_numbering_rule.sh <カテゴリー名>"
