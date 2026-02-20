#!/bin/bash

# アビリティデータ補完スクリプト
# MstUnitAbility、MstAbility、MstAbilityI18nを過去データから抽出して補完

set -e

PAST_DATA_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/raw-data/masterdata/released/202601010/past_tables"
OUTPUT_BASE_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202602015"
RELEASE_KEY="202601010"

echo "========================================="
echo "アビリティデータ補完開始"
echo "========================================="
echo ""

# 関数: アビリティデータ補完
補完_ability() {
    local NEW_CHAR_ID=$1
    local TEMPLATE_ABILITY_PREFIX=$2
    local CHAR_NAME=$3

    echo "----------------------------------------"
    echo "補完中: $NEW_CHAR_ID ($CHAR_NAME)"
    echo "  テンプレート: $TEMPLATE_ABILITY_PREFIX"
    echo "----------------------------------------"

    OUTPUT_DIR="$OUTPUT_BASE_DIR/$NEW_CHAR_ID"
    NEW_ABILITY_PREFIX="ability_${NEW_CHAR_ID#chara_}"

    # MstUnitAbility
    echo "  補完中: MstUnitAbility.csv"
    grep "$TEMPLATE_ABILITY_PREFIX" "$PAST_DATA_DIR/MstUnitAbility.csv" | sed \
        -e "s/$TEMPLATE_ABILITY_PREFIX/$NEW_ABILITY_PREFIX/g" \
        -e "s/202509010/$RELEASE_KEY/g" \
        >> "$OUTPUT_DIR/MstUnitAbility.csv"
    RECORD_COUNT=$(tail -n +2 "$OUTPUT_DIR/MstUnitAbility.csv" | wc -l | tr -d ' ')
    echo "    → $RECORD_COUNT レコード補完"

    # MstAbilityとMstAbilityI18nは共通なのでスキップ
    # （既に過去データに存在するため）

    echo "  ✅ $NEW_CHAR_ID 完了"
    echo ""
}

# chara_jig_00401のアビリティデータ補完
補完_ability "chara_jig_00401" "ability_jig_00001" "賊王 亜左 弔兵衛"

# chara_jig_00501のアビリティデータ補完
補完_ability "chara_jig_00501" "ability_jig_00101" "山田浅ェ門 桐馬"

# chara_jig_00601のアビリティデータ補完
補完_ability "chara_jig_00601" "ability_jig_00301" "民谷 巌鉄斎"

# chara_jig_00701のアビリティデータ補完
補完_ability "chara_jig_00701" "ability_jig_00301" "メイ"

echo "========================================="
echo "全キャラクターの補完完了"
echo "========================================="
