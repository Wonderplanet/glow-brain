#!/bin/bash

# クエスト・ステージマスタデータ一括生成スクリプト
# テンプレート: sur1イベント (魔都精兵のスレイブ)
# 生成対象: jig1イベント (地獄楽)

set -e

PAST_DATA_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/raw-data/masterdata/released/202601010/past_tables"
OUTPUT_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202602015"

echo "🚀 クエスト・ステージマスタデータ生成開始..."

# 対象テーブル一覧
TABLES=(
  "MstQuest"
  "MstQuestI18n"
  "MstStage"
  "MstStageI18n"
  "MstStageEventReward"
  "MstStageEventSetting"
  "MstStageClearTimeReward"
)

# テンプレートから地獄楽イベント用にデータを抽出・変換
for table in "${TABLES[@]}"; do
  echo "📝 ${table}.csv を生成中..."

  # ヘッダー行を抽出
  head -1 "${PAST_DATA_DIR}/${table}.csv" > "${OUTPUT_DIR}/${table}.csv.tmp"

  # sur1イベントのデータを抽出して置換
  grep -E "quest_event_sur1_|event_sur1_" "${PAST_DATA_DIR}/${table}.csv" | \
    sed 's/quest_event_sur1_charaget01/quest_event_jig1_charaget01/g' | \
    sed 's/quest_event_sur1_charaget02/quest_event_jig1_charaget02/g' | \
    sed 's/quest_event_sur1_1day/quest_event_jig1_1day/g' | \
    sed 's/quest_event_sur1_challenge01/quest_event_jig1_challenge01/g' | \
    sed 's/quest_event_sur1_savage/quest_event_jig1_savage/g' | \
    sed 's/event_sur1_charaget01/event_jig1_charaget01/g' | \
    sed 's/event_sur1_charaget02/event_jig1_charaget02/g' | \
    sed 's/event_sur1_1day/event_jig1_1day/g' | \
    sed 's/event_sur1_challenge01/event_jig1_challenge01/g' | \
    sed 's/event_sur1_savage/event_jig1_savage/g' | \
    sed 's/event_sur_00001/event_jig_00001/g' | \
    sed 's/sur1_charaget01/jig1_charaget01/g' | \
    sed 's/sur1_charaget02/jig1_charaget02/g' | \
    sed 's/sur1_1day/jig1_1day/g' | \
    sed 's/sur1_challenge01/jig1_challenge01/g' | \
    sed 's/sur1_savage/jig1_savage/g' | \
    sed 's/202512010/202601010/g' | \
    sed 's/2025-12-08 15:00:00/2026-01-16 15:00:00/g' | \
    sed 's/2025-12-15 15:00:00/2026-01-21 15:00:00/g' | \
    sed 's/2026-01-16 10:59:59/2026-02-16 10:59:59/g' | \
    sed 's/2025-12-31 23:59:59/2026-02-02 03:59:59/g' | \
    sed 's/event_sur1_charaget_sur/event_jig1_charaget_mei/g' | \
    sed 's/event_sur1_charaget_aoba/event_jig1_charaget_sagiri/g' | \
    sed 's/event_sur1_savege/event_jig1_savege/g' | \
    sed 's/event_sur_a_/event_jig_a_/g' | \
    sed 's/event_sur_b_/event_jig_b_/g' | \
    sed 's/chara_sur_/chara_jig_/g' | \
    sed 's/piece_sur_/piece_jig_/g' | \
    sed 's/sur_/jig_/g' \
    >> "${OUTPUT_DIR}/${table}.csv.tmp"

  # 既存のMstQuest.csvとMstQuestI18n.csvをバックアップして置換
  if [ -f "${OUTPUT_DIR}/${table}.csv" ]; then
    mv "${OUTPUT_DIR}/${table}.csv" "${OUTPUT_DIR}/${table}.csv.bak"
  fi

  mv "${OUTPUT_DIR}/${table}.csv.tmp" "${OUTPUT_DIR}/${table}.csv"

  # 生成されたレコード数を表示
  RECORD_COUNT=$(tail -n +2 "${OUTPUT_DIR}/${table}.csv" | wc -l | tr -d ' ')
  echo "   ✅ ${RECORD_COUNT} レコード生成完了"
done

echo ""
echo "✨ 全テーブルの生成が完了しました！"
echo ""
echo "📊 生成されたファイル:"
for table in "${TABLES[@]}"; do
  echo "   - ${OUTPUT_DIR}/${table}.csv"
done

echo ""
echo "🔍 次のステップ:"
echo "   1. 生成されたCSVファイルを確認"
echo "   2. クエスト設計書から詳細情報を確認"
echo "   3. 必要に応じて手動調整"
