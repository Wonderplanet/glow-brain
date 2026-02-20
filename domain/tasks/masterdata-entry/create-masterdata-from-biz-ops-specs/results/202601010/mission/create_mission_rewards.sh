#!/bin/bash

# 地獄楽いいジャン祭 特別ミッション 報酬データ作成スクリプト

OUTPUT_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202602015/mission"

RELEASE_KEY="202601010"
EVENT_NAME="地獄楽いいジャン祭"

# アイテムID定義
ITEM_TICKET_PICKUP="ticket_jig_00001"  # ピックアップガシャチケット (推測)
ITEM_TICKET_SPECIAL="ticket_glo_00002"  # スペシャルガシャチケット
ITEM_MEMORY_FRAGMENT_1="memoryfragment_glo_00001"  # メモリーフラグメント・初級
ITEM_MEMORY_FRAGMENT_2="memoryfragment_glo_00002"  # メモリーフラグメント・中級
ITEM_MEMORY_FRAGMENT_3="memoryfragment_glo_00003"  # メモリーフラグメント・上級
ITEM_PIECE_CHARA_1="piece_jig_00601"  # メイのかけら (推測)
ITEM_PIECE_CHARA_2="piece_jig_00701"  # 民谷 巌鉄斎のかけら (推測)
ITEM_MEMORY_CHARA_1="memory_chara_jig_00601"  # メイのカラーメモリー (推測)
ITEM_MEMORY_CHARA_2="memory_chara_jig_00701"  # 民谷 巌鉄斎のカラーメモリー (推測)
ITEM_PIECE_GARAMARU="piece_jig_00401"  # がらんの画眉丸のかけら

# MstMissionReward.csv作成
cat > "$OUTPUT_DIR/MstMissionReward.csv" << 'EOF'
ENABLE,id,release_key,mst_mission_reward_group_id,reward_type,reward_id,reward_quantity,sort_order,note
EOF

# 報酬データ作成
# 仕様書の報酬内容に基づく

# メイのミッション報酬
# グレード2: メイのカラーメモリー 200
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_01,${RELEASE_KEY},jig_00001_event_reward_01,Item,${ITEM_MEMORY_CHARA_1},200,1,${EVENT_NAME}
EOF

# グレード3: メイのカラーメモリー 300
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_02,${RELEASE_KEY},jig_00001_event_reward_02,Item,${ITEM_MEMORY_CHARA_1},300,1,${EVENT_NAME}
EOF

# グレード4: メイのカラーメモリー 350
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_03,${RELEASE_KEY},jig_00001_event_reward_03,Item,${ITEM_MEMORY_CHARA_1},350,1,${EVENT_NAME}
EOF

# グレード5: ピックアップガシャチケット 1
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_04,${RELEASE_KEY},jig_00001_event_reward_04,Item,${ITEM_TICKET_PICKUP},1,1,${EVENT_NAME}
EOF

# Lv.20: メイのかけら 10
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_05,${RELEASE_KEY},jig_00001_event_reward_05,Item,${ITEM_PIECE_CHARA_1},10,1,${EVENT_NAME}
EOF

# Lv.30: メイのかけら 20
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_06,${RELEASE_KEY},jig_00001_event_reward_06,Item,${ITEM_PIECE_CHARA_1},20,1,${EVENT_NAME}
EOF

# Lv.40: メイのかけら 20
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_07,${RELEASE_KEY},jig_00001_event_reward_07,Item,${ITEM_PIECE_CHARA_1},20,1,${EVENT_NAME}
EOF

# Lv.50: メイのかけら 20
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_08,${RELEASE_KEY},jig_00001_event_reward_08,Item,${ITEM_PIECE_CHARA_1},20,1,${EVENT_NAME}
EOF

# Lv.60: メイのかけら 30
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_09,${RELEASE_KEY},jig_00001_event_reward_09,Item,${ITEM_PIECE_CHARA_1},30,1,${EVENT_NAME}
EOF

# Lv.70: メモリーフラグメント・上級 1
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_10,${RELEASE_KEY},jig_00001_event_reward_10,Item,${ITEM_MEMORY_FRAGMENT_3},1,1,${EVENT_NAME}
EOF

# Lv.80: プリズム 50
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_11,${RELEASE_KEY},jig_00001_event_reward_11,FreeDiamond,"",50,1,${EVENT_NAME}
EOF

# 民谷 巌鉄斎のミッション報酬
# グレード2: 民谷 巌鉄斎のカラーメモリー 200
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_12,${RELEASE_KEY},jig_00001_event_reward_12,Item,${ITEM_MEMORY_CHARA_2},200,1,${EVENT_NAME}
EOF

# グレード3: 民谷 巌鉄斎のカラーメモリー 300
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_13,${RELEASE_KEY},jig_00001_event_reward_13,Item,${ITEM_MEMORY_CHARA_2},300,1,${EVENT_NAME}
EOF

# グレード4: 民谷 巌鉄斎のカラーメモリー 350
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_14,${RELEASE_KEY},jig_00001_event_reward_14,Item,${ITEM_MEMORY_CHARA_2},350,1,${EVENT_NAME}
EOF

# グレード5: ピックアップガシャチケット 1
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_15,${RELEASE_KEY},jig_00001_event_reward_15,Item,${ITEM_TICKET_PICKUP},1,1,${EVENT_NAME}
EOF

# Lv.20: 民谷 巌鉄斎のかけら 10
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_16,${RELEASE_KEY},jig_00001_event_reward_16,Item,${ITEM_PIECE_CHARA_2},10,1,${EVENT_NAME}
EOF

# Lv.30: 民谷 巌鉄斎のかけら 20
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_17,${RELEASE_KEY},jig_00001_event_reward_17,Item,${ITEM_PIECE_CHARA_2},20,1,${EVENT_NAME}
EOF

# Lv.40: 民谷 巌鉄斎のかけら 20
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_18,${RELEASE_KEY},jig_00001_event_reward_18,Item,${ITEM_PIECE_CHARA_2},20,1,${EVENT_NAME}
EOF

# Lv.50: 民谷 巌鉄斎のかけら 20
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_19,${RELEASE_KEY},jig_00001_event_reward_19,Item,${ITEM_PIECE_CHARA_2},20,1,${EVENT_NAME}
EOF

# Lv.60: 民谷 巌鉄斎のかけら 30
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_20,${RELEASE_KEY},jig_00001_event_reward_20,Item,${ITEM_PIECE_CHARA_2},30,1,${EVENT_NAME}
EOF

# Lv.70: メモリーフラグメント・上級 1
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_21,${RELEASE_KEY},jig_00001_event_reward_21,Item,${ITEM_MEMORY_FRAGMENT_3},1,1,${EVENT_NAME}
EOF

# Lv.80: プリズム 50
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_22,${RELEASE_KEY},jig_00001_event_reward_22,FreeDiamond,"",50,1,${EVENT_NAME}
EOF

# クエストクリア報酬
# ストーリークエスト1: コイン 12500
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_23,${RELEASE_KEY},jig_00001_event_reward_23,Coin,"",12500,1,${EVENT_NAME}
EOF

# ストーリークエスト2: コイン 12500
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_24,${RELEASE_KEY},jig_00001_event_reward_24,Coin,"",12500,1,${EVENT_NAME}
EOF

# チャレンジクエスト: スペシャルガシャチケット 3
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_25,${RELEASE_KEY},jig_00001_event_reward_25,Item,${ITEM_TICKET_SPECIAL},3,1,${EVENT_NAME}
EOF

# 高難易度: ピックアップガシャチケット 1
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_26,${RELEASE_KEY},jig_00001_event_reward_26,Item,${ITEM_TICKET_PICKUP},1,1,${EVENT_NAME}
EOF

# 敵撃破数報酬
# 10体: メモリーフラグメント・初級 5
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_27,${RELEASE_KEY},jig_00001_event_reward_27,Item,${ITEM_MEMORY_FRAGMENT_1},5,1,${EVENT_NAME}
EOF

# 20体: メモリーフラグメント・初級 5
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_28,${RELEASE_KEY},jig_00001_event_reward_28,Item,${ITEM_MEMORY_FRAGMENT_1},5,1,${EVENT_NAME}
EOF

# 30体: メモリーフラグメント・初級 5
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_29,${RELEASE_KEY},jig_00001_event_reward_29,Item,${ITEM_MEMORY_FRAGMENT_1},5,1,${EVENT_NAME}
EOF

# 40体: メモリーフラグメント・初級 5
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_30,${RELEASE_KEY},jig_00001_event_reward_30,Item,${ITEM_MEMORY_FRAGMENT_1},5,1,${EVENT_NAME}
EOF

# 50体: メモリーフラグメント・初級 10
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_31,${RELEASE_KEY},jig_00001_event_reward_31,Item,${ITEM_MEMORY_FRAGMENT_1},10,1,${EVENT_NAME}
EOF

# 60体: メモリーフラグメント・中級 5
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_32,${RELEASE_KEY},jig_00001_event_reward_32,Item,${ITEM_MEMORY_FRAGMENT_2},5,1,${EVENT_NAME}
EOF

# 70体: メモリーフラグメント・中級 5
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_33,${RELEASE_KEY},jig_00001_event_reward_33,Item,${ITEM_MEMORY_FRAGMENT_2},5,1,${EVENT_NAME}
EOF

# 80体: メモリーフラグメント・中級 5
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_34,${RELEASE_KEY},jig_00001_event_reward_34,Item,${ITEM_MEMORY_FRAGMENT_2},5,1,${EVENT_NAME}
EOF

# 90体: メモリーフラグメント・中級 5
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_35,${RELEASE_KEY},jig_00001_event_reward_35,Item,${ITEM_MEMORY_FRAGMENT_2},5,1,${EVENT_NAME}
EOF

# 100体: メモリーフラグメント・上級 1
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_36,${RELEASE_KEY},jig_00001_event_reward_36,Item,${ITEM_MEMORY_FRAGMENT_3},1,1,${EVENT_NAME}
EOF

# 150体: メモリーフラグメント・上級 1
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_37,${RELEASE_KEY},jig_00001_event_reward_37,Item,${ITEM_MEMORY_FRAGMENT_3},1,1,${EVENT_NAME}
EOF

# 200体: メモリーフラグメント・上級 1
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_38,${RELEASE_KEY},jig_00001_event_reward_38,Item,${ITEM_MEMORY_FRAGMENT_3},1,1,${EVENT_NAME}
EOF

# 300体: ピックアップガシャチケット 1
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_39,${RELEASE_KEY},jig_00001_event_reward_39,Item,${ITEM_TICKET_PICKUP},1,1,${EVENT_NAME}
EOF

# 400体: ピックアップガシャチケット 1
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_40,${RELEASE_KEY},jig_00001_event_reward_40,Item,${ITEM_TICKET_PICKUP},1,1,${EVENT_NAME}
EOF

# 500体: がらんの画眉丸のかけら 5
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_41,${RELEASE_KEY},jig_00001_event_reward_41,Item,${ITEM_PIECE_GARAMARU},5,1,${EVENT_NAME}
EOF

# 750体: がらんの画眉丸のかけら 15
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_42,${RELEASE_KEY},jig_00001_event_reward_42,Item,${ITEM_PIECE_GARAMARU},15,1,${EVENT_NAME}
EOF

# 1000体: がらんの画眉丸のかけら 20
cat >> "$OUTPUT_DIR/MstMissionReward.csv" << EOF
e,mission_reward_jig_00001_43,${RELEASE_KEY},jig_00001_event_reward_43,Item,${ITEM_PIECE_GARAMARU},20,1,${EVENT_NAME}
EOF

echo "MstMissionReward.csvを作成しました"
