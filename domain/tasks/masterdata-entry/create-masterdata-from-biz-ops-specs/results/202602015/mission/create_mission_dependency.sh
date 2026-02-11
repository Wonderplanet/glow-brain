#!/bin/bash

# 地獄楽いいジャン祭 特別ミッション 依存関係データ作成スクリプト

OUTPUT_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202602015/mission"

RELEASE_KEY="202601010"

# MstMissionEventDependency.csv作成
cat > "$OUTPUT_DIR/MstMissionEventDependency.csv" << 'EOF'
ENABLE,id,release_key,group_id,mst_mission_event_id,unlock_order,備考
EOF

# ID カウンタ
id=1

# メイ グレードアップ依存関係 (event_jig_00001_1 ~ event_jig_00001_4)
for i in {1..4}; do
cat >> "$OUTPUT_DIR/MstMissionEventDependency.csv" << EOF
e,$id,${RELEASE_KEY},event_jig_00001_1,event_jig_00001_$i,$i,""
EOF
  ((id++))
done

# メイ レベルアップ依存関係 (event_jig_00001_5 ~ event_jig_00001_11)
for i in {5..11}; do
  order=$((i - 4))
cat >> "$OUTPUT_DIR/MstMissionEventDependency.csv" << EOF
e,$id,${RELEASE_KEY},event_jig_00001_5,event_jig_00001_$i,$order,""
EOF
  ((id++))
done

# 民谷 巌鉄斎 グレードアップ依存関係 (event_jig_00001_12 ~ event_jig_00001_15)
for i in {12..15}; do
  order=$((i - 11))
cat >> "$OUTPUT_DIR/MstMissionEventDependency.csv" << EOF
e,$id,${RELEASE_KEY},event_jig_00001_12,event_jig_00001_$i,$order,""
EOF
  ((id++))
done

# 民谷 巌鉄斎 レベルアップ依存関係 (event_jig_00001_16 ~ event_jig_00001_22)
for i in {16..22}; do
  order=$((i - 15))
cat >> "$OUTPUT_DIR/MstMissionEventDependency.csv" << EOF
e,$id,${RELEASE_KEY},event_jig_00001_16,event_jig_00001_$i,$order,""
EOF
  ((id++))
done

# 敵撃破数依存関係 (event_jig_00001_27 ~ event_jig_00001_43)
for i in {27..43}; do
  order=$((i - 26))
cat >> "$OUTPUT_DIR/MstMissionEventDependency.csv" << EOF
e,$id,${RELEASE_KEY},event_jig_00001_27,event_jig_00001_$i,$order,""
EOF
  ((id++))
done

echo "MstMissionEventDependency.csvを作成しました"
echo "総レコード数: $((id - 1))"
