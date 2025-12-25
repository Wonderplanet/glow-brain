#!/bin/bash
# モデル名をテーブル名に変換するスクリプト
# 例: OprGacha -> opr_gachas
# 例: MstUnit -> mst_units
# 例: MstAdventBattle -> mst_advent_battles

set -euo pipefail

if [ $# -eq 0 ]; then
    echo "Usage: $0 <ModelName>" >&2
    echo "Example: $0 OprGacha" >&2
    exit 1
fi

MODEL_NAME="$1"

# PascalCase を snake_case に変換
# 例: OprGacha -> opr_gacha
SNAKE_CASE=$(echo "$MODEL_NAME" | sed -E 's/([A-Z])/_\1/g' | sed 's/^_//' | tr '[:upper:]' '[:lower:]')

# 複数形化のルール
# - 末尾が "s", "x", "z", "ch", "sh" の場合は "es" を追加
# - 末尾が "y" で直前が子音の場合は "ies" に変換
# - それ以外は "s" を追加

if [[ "$SNAKE_CASE" =~ (s|x|z|ch|sh)$ ]]; then
    # 例: box -> boxes, batch -> batches
    TABLE_NAME="${SNAKE_CASE}es"
elif [[ "$SNAKE_CASE" =~ [^aeiou]y$ ]]; then
    # 例: ability -> abilities
    TABLE_NAME="${SNAKE_CASE%y}ies"
else
    # 通常の複数形
    TABLE_NAME="${SNAKE_CASE}s"
fi

echo "$TABLE_NAME"
