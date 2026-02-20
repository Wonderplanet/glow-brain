#!/bin/bash

# 地獄楽いいジャン祭 特別ミッション データ作成スクリプト
# リリースキー: 202601010
# イベントID: event_jig_00001

OUTPUT_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202602015/mission"

# 定数定義
RELEASE_KEY="202601010"
EVENT_ID="event_jig_00001"

# キャラクターID (仕様書の「メイ」と「民谷 巌鉄斎」に対応)
# 推測: メイ → chara_jig_00601, 民谷 巌鉄斎 → chara_jig_00701
CHARA_1="chara_jig_00601"  # 仕様書上の「メイ」
CHARA_2="chara_jig_00701"  # 仕様書上の「民谷 巌鉄斎」

# クエストID (推測値)
QUEST_STORY_1="quest_event_jig1_charaget01"  # 「必ず生きて帰る」
QUEST_STORY_2="quest_event_jig1_charaget02"  # 「朱印の者たち」
QUEST_CHALLENGE="quest_event_jig1_challenge01"  # 「死罪人と首切り役人」
QUEST_SAVAGE="quest_event_jig1_savage"  # 「手負いの獣は恐ろしいぞ」

# MstMissionEvent.csv作成
cat > "$OUTPUT_DIR/MstMissionEvent.csv" << 'EOF'
ENABLE,id,release_key,mst_event_id,criterion_type,criterion_value,criterion_count,unlock_criterion_type,unlock_criterion_value,unlock_criterion_count,group_key,mst_mission_reward_group_id,sort_order,destination_scene
EOF

# メイのミッション (グレード2,3,4,5 + レベル20,30,40,50,60,70,80)
cat >> "$OUTPUT_DIR/MstMissionEvent.csv" << EOF
e,event_jig_00001_1,${RELEASE_KEY},${EVENT_ID},SpecificUnitGradeUpCount,${CHARA_1},2,,"",0,"",jig_00001_event_reward_01,1,UnitList
e,event_jig_00001_2,${RELEASE_KEY},${EVENT_ID},SpecificUnitGradeUpCount,${CHARA_1},3,,"",0,"",jig_00001_event_reward_02,2,UnitList
e,event_jig_00001_3,${RELEASE_KEY},${EVENT_ID},SpecificUnitGradeUpCount,${CHARA_1},4,,"",0,"",jig_00001_event_reward_03,3,UnitList
e,event_jig_00001_4,${RELEASE_KEY},${EVENT_ID},SpecificUnitGradeUpCount,${CHARA_1},5,,"",0,"",jig_00001_event_reward_04,4,UnitList
e,event_jig_00001_5,${RELEASE_KEY},${EVENT_ID},SpecificUnitLevel,${CHARA_1},20,,"",0,"",jig_00001_event_reward_05,5,UnitList
e,event_jig_00001_6,${RELEASE_KEY},${EVENT_ID},SpecificUnitLevel,${CHARA_1},30,,"",0,"",jig_00001_event_reward_06,6,UnitList
e,event_jig_00001_7,${RELEASE_KEY},${EVENT_ID},SpecificUnitLevel,${CHARA_1},40,,"",0,"",jig_00001_event_reward_07,7,UnitList
e,event_jig_00001_8,${RELEASE_KEY},${EVENT_ID},SpecificUnitLevel,${CHARA_1},50,,"",0,"",jig_00001_event_reward_08,8,UnitList
e,event_jig_00001_9,${RELEASE_KEY},${EVENT_ID},SpecificUnitLevel,${CHARA_1},60,,"",0,"",jig_00001_event_reward_09,9,UnitList
e,event_jig_00001_10,${RELEASE_KEY},${EVENT_ID},SpecificUnitLevel,${CHARA_1},70,,"",0,"",jig_00001_event_reward_10,10,UnitList
e,event_jig_00001_11,${RELEASE_KEY},${EVENT_ID},SpecificUnitLevel,${CHARA_1},80,,"",0,"",jig_00001_event_reward_11,11,UnitList
EOF

# 民谷 巌鉄斎のミッション (グレード2,3,4,5 + レベル20,30,40,50,60,70,80)
cat >> "$OUTPUT_DIR/MstMissionEvent.csv" << EOF
e,event_jig_00001_12,${RELEASE_KEY},${EVENT_ID},SpecificUnitGradeUpCount,${CHARA_2},2,,"",0,"",jig_00001_event_reward_12,12,UnitList
e,event_jig_00001_13,${RELEASE_KEY},${EVENT_ID},SpecificUnitGradeUpCount,${CHARA_2},3,,"",0,"",jig_00001_event_reward_13,13,UnitList
e,event_jig_00001_14,${RELEASE_KEY},${EVENT_ID},SpecificUnitGradeUpCount,${CHARA_2},4,,"",0,"",jig_00001_event_reward_14,14,UnitList
e,event_jig_00001_15,${RELEASE_KEY},${EVENT_ID},SpecificUnitGradeUpCount,${CHARA_2},5,,"",0,"",jig_00001_event_reward_15,15,UnitList
e,event_jig_00001_16,${RELEASE_KEY},${EVENT_ID},SpecificUnitLevel,${CHARA_2},20,,"",0,"",jig_00001_event_reward_16,16,UnitList
e,event_jig_00001_17,${RELEASE_KEY},${EVENT_ID},SpecificUnitLevel,${CHARA_2},30,,"",0,"",jig_00001_event_reward_17,17,UnitList
e,event_jig_00001_18,${RELEASE_KEY},${EVENT_ID},SpecificUnitLevel,${CHARA_2},40,,"",0,"",jig_00001_event_reward_18,18,UnitList
e,event_jig_00001_19,${RELEASE_KEY},${EVENT_ID},SpecificUnitLevel,${CHARA_2},50,,"",0,"",jig_00001_event_reward_19,19,UnitList
e,event_jig_00001_20,${RELEASE_KEY},${EVENT_ID},SpecificUnitLevel,${CHARA_2},60,,"",0,"",jig_00001_event_reward_20,20,UnitList
e,event_jig_00001_21,${RELEASE_KEY},${EVENT_ID},SpecificUnitLevel,${CHARA_2},70,,"",0,"",jig_00001_event_reward_21,21,UnitList
e,event_jig_00001_22,${RELEASE_KEY},${EVENT_ID},SpecificUnitLevel,${CHARA_2},80,,"",0,"",jig_00001_event_reward_22,22,UnitList
EOF

# クエストクリアミッション
cat >> "$OUTPUT_DIR/MstMissionEvent.csv" << EOF
e,event_jig_00001_23,${RELEASE_KEY},${EVENT_ID},SpecificQuestClear,${QUEST_STORY_1},1,,"",0,"",jig_00001_event_reward_23,23,Event
e,event_jig_00001_24,${RELEASE_KEY},${EVENT_ID},SpecificQuestClear,${QUEST_STORY_2},1,,"",0,"",jig_00001_event_reward_24,24,Event
e,event_jig_00001_25,${RELEASE_KEY},${EVENT_ID},SpecificQuestClear,${QUEST_CHALLENGE},1,,"",0,"",jig_00001_event_reward_25,25,Event
e,event_jig_00001_26,${RELEASE_KEY},${EVENT_ID},SpecificQuestClear,${QUEST_SAVAGE},1,,"",0,"",jig_00001_event_reward_26,26,Event
EOF

# 敵撃破数ミッション (10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 300, 400, 500, 750, 1000体)
cat >> "$OUTPUT_DIR/MstMissionEvent.csv" << EOF
e,event_jig_00001_27,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",10,,"",0,"",jig_00001_event_reward_27,27,Event
e,event_jig_00001_28,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",20,,"",0,"",jig_00001_event_reward_28,28,Event
e,event_jig_00001_29,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",30,,"",0,"",jig_00001_event_reward_29,29,Event
e,event_jig_00001_30,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",40,,"",0,"",jig_00001_event_reward_30,30,Event
e,event_jig_00001_31,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",50,,"",0,"",jig_00001_event_reward_31,31,Event
e,event_jig_00001_32,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",60,,"",0,"",jig_00001_event_reward_32,32,Event
e,event_jig_00001_33,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",70,,"",0,"",jig_00001_event_reward_33,33,Event
e,event_jig_00001_34,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",80,,"",0,"",jig_00001_event_reward_34,34,Event
e,event_jig_00001_35,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",90,,"",0,"",jig_00001_event_reward_35,35,Event
e,event_jig_00001_36,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",100,,"",0,"",jig_00001_event_reward_36,36,Event
e,event_jig_00001_37,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",150,,"",0,"",jig_00001_event_reward_37,37,Event
e,event_jig_00001_38,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",200,,"",0,"",jig_00001_event_reward_38,38,Event
e,event_jig_00001_39,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",300,,"",0,"",jig_00001_event_reward_39,39,Event
e,event_jig_00001_40,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",400,,"",0,"",jig_00001_event_reward_40,40,Event
e,event_jig_00001_41,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",500,,"",0,"",jig_00001_event_reward_41,41,Event
e,event_jig_00001_42,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",750,,"",0,"",jig_00001_event_reward_42,42,Event
e,event_jig_00001_43,${RELEASE_KEY},${EVENT_ID},DefeatEnemyCount,"",1000,,"",0,"",jig_00001_event_reward_43,43,Event
EOF

echo "MstMissionEvent.csvを作成しました"

# MstMissionEventI18n.csv作成
cat > "$OUTPUT_DIR/MstMissionEventI18n.csv" << 'EOF'
ENABLE,release_key,id,mst_mission_event_id,language,description
EOF

# メイのミッション説明文
cat >> "$OUTPUT_DIR/MstMissionEventI18n.csv" << EOF
e,${RELEASE_KEY},event_jig_00001_1_ja,event_jig_00001_1,ja,メイ をグレード2まで強化しよう
e,${RELEASE_KEY},event_jig_00001_2_ja,event_jig_00001_2,ja,メイ をグレード3まで強化しよう
e,${RELEASE_KEY},event_jig_00001_3_ja,event_jig_00001_3,ja,メイ をグレード4まで強化しよう
e,${RELEASE_KEY},event_jig_00001_4_ja,event_jig_00001_4,ja,メイ をグレード5まで強化しよう
e,${RELEASE_KEY},event_jig_00001_5_ja,event_jig_00001_5,ja,メイ をLv.20まで強化しよう
e,${RELEASE_KEY},event_jig_00001_6_ja,event_jig_00001_6,ja,メイ をLv.30まで強化しよう
e,${RELEASE_KEY},event_jig_00001_7_ja,event_jig_00001_7,ja,メイ をLv.40まで強化しよう
e,${RELEASE_KEY},event_jig_00001_8_ja,event_jig_00001_8,ja,メイ をLv.50まで強化しよう
e,${RELEASE_KEY},event_jig_00001_9_ja,event_jig_00001_9,ja,メイ をLv.60まで強化しよう
e,${RELEASE_KEY},event_jig_00001_10_ja,event_jig_00001_10,ja,メイ をLv.70まで強化しよう
e,${RELEASE_KEY},event_jig_00001_11_ja,event_jig_00001_11,ja,メイ をLv.80まで強化しよう
EOF

# 民谷 巌鉄斎のミッション説明文
cat >> "$OUTPUT_DIR/MstMissionEventI18n.csv" << EOF
e,${RELEASE_KEY},event_jig_00001_12_ja,event_jig_00001_12,ja,民谷 巌鉄斎 をグレード2まで強化しよう
e,${RELEASE_KEY},event_jig_00001_13_ja,event_jig_00001_13,ja,民谷 巌鉄斎 をグレード3まで強化しよう
e,${RELEASE_KEY},event_jig_00001_14_ja,event_jig_00001_14,ja,民谷 巌鉄斎 をグレード4まで強化しよう
e,${RELEASE_KEY},event_jig_00001_15_ja,event_jig_00001_15,ja,民谷 巌鉄斎 をグレード5まで強化しよう
e,${RELEASE_KEY},event_jig_00001_16_ja,event_jig_00001_16,ja,民谷 巌鉄斎 をLv.20まで強化しよう
e,${RELEASE_KEY},event_jig_00001_17_ja,event_jig_00001_17,ja,民谷 巌鉄斎 をLv.30まで強化しよう
e,${RELEASE_KEY},event_jig_00001_18_ja,event_jig_00001_18,ja,民谷 巌鉄斎 をLv.40まで強化しよう
e,${RELEASE_KEY},event_jig_00001_19_ja,event_jig_00001_19,ja,民谷 巌鉄斎 をLv.50まで強化しよう
e,${RELEASE_KEY},event_jig_00001_20_ja,event_jig_00001_20,ja,民谷 巌鉄斎 をLv.60まで強化しよう
e,${RELEASE_KEY},event_jig_00001_21_ja,event_jig_00001_21,ja,民谷 巌鉄斎 をLv.70まで強化しよう
e,${RELEASE_KEY},event_jig_00001_22_ja,event_jig_00001_22,ja,民谷 巌鉄斎 をLv.80まで強化しよう
EOF

# クエストクリアミッション説明文
cat >> "$OUTPUT_DIR/MstMissionEventI18n.csv" << EOF
e,${RELEASE_KEY},event_jig_00001_23_ja,event_jig_00001_23,ja,ストーリークエスト「必ず生きて帰る」をクリアしよう
e,${RELEASE_KEY},event_jig_00001_24_ja,event_jig_00001_24,ja,ストーリークエスト「朱印の者たち」をクリアしよう
e,${RELEASE_KEY},event_jig_00001_25_ja,event_jig_00001_25,ja,チャレンジクエスト「死罪人と首切り役人」をクリアしよう
e,${RELEASE_KEY},event_jig_00001_26_ja,event_jig_00001_26,ja,高難易度「手負いの獣は恐ろしいぞ」をクリアしよう
EOF

# 敵撃破数ミッション説明文
cat >> "$OUTPUT_DIR/MstMissionEventI18n.csv" << EOF
e,${RELEASE_KEY},event_jig_00001_27_ja,event_jig_00001_27,ja,敵を10体撃破しよう
e,${RELEASE_KEY},event_jig_00001_28_ja,event_jig_00001_28,ja,敵を20体撃破しよう
e,${RELEASE_KEY},event_jig_00001_29_ja,event_jig_00001_29,ja,敵を30体撃破しよう
e,${RELEASE_KEY},event_jig_00001_30_ja,event_jig_00001_30,ja,敵を40体撃破しよう
e,${RELEASE_KEY},event_jig_00001_31_ja,event_jig_00001_31,ja,敵を50体撃破しよう
e,${RELEASE_KEY},event_jig_00001_32_ja,event_jig_00001_32,ja,敵を60体撃破しよう
e,${RELEASE_KEY},event_jig_00001_33_ja,event_jig_00001_33,ja,敵を70体撃破しよう
e,${RELEASE_KEY},event_jig_00001_34_ja,event_jig_00001_34,ja,敵を80体撃破しよう
e,${RELEASE_KEY},event_jig_00001_35_ja,event_jig_00001_35,ja,敵を90体撃破しよう
e,${RELEASE_KEY},event_jig_00001_36_ja,event_jig_00001_36,ja,敵を100体撃破しよう
e,${RELEASE_KEY},event_jig_00001_37_ja,event_jig_00001_37,ja,敵を150体撃破しよう
e,${RELEASE_KEY},event_jig_00001_38_ja,event_jig_00001_38,ja,敵を200体撃破しよう
e,${RELEASE_KEY},event_jig_00001_39_ja,event_jig_00001_39,ja,敵を300体撃破しよう
e,${RELEASE_KEY},event_jig_00001_40_ja,event_jig_00001_40,ja,敵を400体撃破しよう
e,${RELEASE_KEY},event_jig_00001_41_ja,event_jig_00001_41,ja,敵を500体撃破しよう
e,${RELEASE_KEY},event_jig_00001_42_ja,event_jig_00001_42,ja,敵を750体撃破しよう
e,${RELEASE_KEY},event_jig_00001_43_ja,event_jig_00001_43,ja,敵を1000体撃破しよう
EOF

echo "MstMissionEventI18n.csvを作成しました"

echo "スクリプト実行完了"
