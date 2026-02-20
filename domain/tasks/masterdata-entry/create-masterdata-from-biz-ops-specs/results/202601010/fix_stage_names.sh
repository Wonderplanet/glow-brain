#!/bin/bash
# MstStageI18nのステージ名を修正

INPUT="MstStageI18n.csv"
OUTPUT="MstStageI18n_new.csv"

# ヘッダー行をコピー
head -1 "$INPUT" > "$OUTPUT"

# データ行を処理
tail -n +2 "$INPUT" | while IFS=, read -r enable release_key id mst_stage_id language name category; do
  # mst_stage_idから最後の5桁の数字を抽出
  stage_num=$(echo "$mst_stage_id" | grep -o '[0-9]\{5\}$')
  
  # 先頭の0を削除
  stage_num=$((10#$stage_num))
  
  # ステージ名を設定
  new_name="${stage_num}話"
  
  # 出力
  echo "$enable,$release_key,$id,$mst_stage_id,$language,$new_name,\"$category\""
done >> "$OUTPUT"

mv "$OUTPUT" "$INPUT"
echo "✅ ステージ名を修正完了"
