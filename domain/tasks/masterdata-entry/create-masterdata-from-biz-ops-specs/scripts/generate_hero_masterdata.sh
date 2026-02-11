#!/bin/bash

# ヒーローマスタデータ自動生成スクリプト
# 過去データをテンプレートとして使用し、新キャラクターのマスタデータを生成

set -e

# ディレクトリ設定
PAST_DATA_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/raw-data/masterdata/released/202601010/past_tables"
OUTPUT_BASE_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202602015"

# 新規キャラクター定義
declare -A CHAR_CONFIGS=(
    ["chara_jig_00401"]="chara_jig_00001|UR|賊王 亜左 弔兵衛|Technical|Colorless|1000|770|655|1140|2100|21000|2500|25000|30|0.31|幕府の重罪人たちが収容された孤島「獄門島」の主。1000人以上の盗賊を束ねる、神出鬼没の「賊王」。"
    ["chara_jig_00501"]="chara_jig_00101|SSR|山田浅ェ門 桐馬|Support|Green|605|425|395|1740|1160|11600|1200|12000|30|0.32|山田浅ェ門一派の一員。冷静沈着で、常に任務を最優先する。"
    ["chara_jig_00601"]="chara_jig_00301|SR|民谷 巌鉄斎|Defense|Blue|440|920|480|1195|2510|25100|880|8800|35|0.21|石隠れの里の長老。石化の術に長ける。"
    ["chara_jig_00701"]="chara_jig_00301|SR|メイ|Technical|Colorless|440|920|480|1195|1800|18000|750|7500|30|0.25|画眉丸と共に旅をする少女。純粋で優しい心を持つ。"
)

# 対象テーブル一覧
TABLES=(
    "MstUnit"
    "MstUnitI18n"
    "MstUnitAbility"
    "MstAbility"
    "MstAbilityI18n"
    "MstAttack"
    "MstAttackElement"
    "MstAttackI18n"
    "MstSpecialAttackI18n"
    "MstSpeechBalloonI18n"
)

# リリースキー
RELEASE_KEY="202601010"

echo "========================================="
echo "ヒーローマスタデータ自動生成開始"
echo "========================================="
echo ""

# 各キャラクターのデータ生成
for NEW_CHAR_ID in "${!CHAR_CONFIGS[@]}"; do
    CONFIG="${CHAR_CONFIGS[$NEW_CHAR_ID]}"
    IFS='|' read -r TEMPLATE_ID RARITY CHAR_NAME ROLE_TYPE COLOR SUMMON_COST SUMMON_COOL_TIME SPECIAL_INIT_CT SPECIAL_CT MIN_HP MAX_HP MIN_ATK MAX_ATK MOVE_SPEED WELL_DIST FLAVOR_TEXT <<< "$CONFIG"

    echo "----------------------------------------"
    echo "生成中: $NEW_CHAR_ID ($CHAR_NAME)"
    echo "  テンプレート: $TEMPLATE_ID"
    echo "  レアリティ: $RARITY"
    echo "----------------------------------------"

    # 出力ディレクトリ作成
    OUTPUT_DIR="$OUTPUT_BASE_DIR/$NEW_CHAR_ID"
    mkdir -p "$OUTPUT_DIR"

    # 各テーブルのデータを抽出・変換
    for TABLE in "${TABLES[@]}"; do
        INPUT_FILE="$PAST_DATA_DIR/$TABLE.csv"
        OUTPUT_FILE="$OUTPUT_DIR/$TABLE.csv"

        if [ ! -f "$INPUT_FILE" ]; then
            echo "  ⚠️  スキップ: $TABLE (ファイルが存在しません)"
            continue
        fi

        echo "  生成中: $TABLE.csv"

        # ヘッダー行を取得
        head -1 "$INPUT_FILE" > "$OUTPUT_FILE"

        # テンプレートキャラのデータを抽出して変換
        grep "$TEMPLATE_ID" "$INPUT_FILE" | sed \
            -e "s/$TEMPLATE_ID/$NEW_CHAR_ID/g" \
            -e "s/piece_${TEMPLATE_ID#chara_}/piece_${NEW_CHAR_ID#chara_}/g" \
            -e "s/ability_${TEMPLATE_ID#chara_}/ability_${NEW_CHAR_ID#chara_}/g" \
            -e "s/attack_${TEMPLATE_ID#chara_}/attack_${NEW_CHAR_ID#chara_}/g" \
            -e "s/enemy_${TEMPLATE_ID#chara_}/enemy_${NEW_CHAR_ID#chara_}/g" \
            -e "s/202509010/$RELEASE_KEY/g" \
            >> "$OUTPUT_FILE" || true

        # レコード数をカウント
        RECORD_COUNT=$(tail -n +2 "$OUTPUT_FILE" | wc -l | tr -d ' ')
        echo "    → $RECORD_COUNT レコード生成"
    done

    echo "  ✅ $NEW_CHAR_ID 完了"
    echo ""
done

echo "========================================="
echo "全キャラクター生成完了"
echo "========================================="
echo ""
echo "出力先: $OUTPUT_BASE_DIR"
echo ""
