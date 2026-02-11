#!/bin/bash

# ヒーローマスタデータ自動生成スクリプト v2
# 過去データをテンプレートとして使用し、新キャラクターのマスタデータを生成

set -e

# ディレクトリ設定
PAST_DATA_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/raw-data/masterdata/released/202601010/past_tables"
OUTPUT_BASE_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202602015"

# 対象テーブル一覧
TABLES="MstUnit MstUnitI18n MstUnitAbility MstAbility MstAbilityI18n MstAttack MstAttackElement MstAttackI18n MstSpecialAttackI18n MstSpeechBalloonI18n"

# リリースキー
RELEASE_KEY="202601010"

echo "========================================="
echo "ヒーローマスタデータ自動生成開始"
echo "========================================="
echo ""

# 関数: キャラクターデータ生成
generate_character() {
    local NEW_CHAR_ID=$1
    local TEMPLATE_ID=$2
    local CHAR_NAME=$3

    echo "----------------------------------------"
    echo "生成中: $NEW_CHAR_ID ($CHAR_NAME)"
    echo "  テンプレート: $TEMPLATE_ID"
    echo "----------------------------------------"

    # 出力ディレクトリ作成
    OUTPUT_DIR="$OUTPUT_BASE_DIR/$NEW_CHAR_ID"
    mkdir -p "$OUTPUT_DIR"

    # 各テーブルのデータを抽出・変換
    for TABLE in $TABLES; do
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
            >> "$OUTPUT_FILE" 2>/dev/null || true

        # レコード数をカウント
        RECORD_COUNT=$(tail -n +2 "$OUTPUT_FILE" 2>/dev/null | wc -l | tr -d ' ')
        echo "    → $RECORD_COUNT レコード生成"
    done

    echo "  ✅ $NEW_CHAR_ID 完了"
    echo ""
}

# キャラクター1: chara_jig_00401 (UR)
generate_character "chara_jig_00401" "chara_jig_00001" "賊王 亜左 弔兵衛"

# キャラクター2: chara_jig_00501 (SSR)
generate_character "chara_jig_00501" "chara_jig_00101" "山田浅ェ門 桐馬"

# キャラクター3: chara_jig_00601 (SR)
generate_character "chara_jig_00601" "chara_jig_00301" "民谷 巌鉄斎"

# キャラクター4: chara_jig_00701 (SR)
generate_character "chara_jig_00701" "chara_jig_00301" "メイ"

echo "========================================="
echo "全キャラクター生成完了"
echo "========================================="
echo ""
echo "出力先: $OUTPUT_BASE_DIR"
echo ""
