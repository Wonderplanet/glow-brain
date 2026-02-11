#!/bin/bash

RELEASE_KEY="202601010"
PAST_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/raw-data/masterdata/released/202601010/past_tables"
OUTPUT_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202602015"

NEW_ID="quest_raid_jig1_00001"
NEW_REWARD_PREFIX="quest_raid_jig1_reward_group_00001"

echo "=== 降臨バトル 報酬データ作成開始 ==="

# 1. MstAdventBattleRewardGroup.csv
echo "1. MstAdventBattleRewardGroup.csv を作成中..."
{
    head -1 "$PAST_DIR/MstAdventBattleRewardGroup.csv"
    
    # ハイスコア目標達成報酬（MaxScore）- 運営仕様書より12段階
    echo "e,${NEW_REWARD_PREFIX}_01,$NEW_ID,MaxScore,5000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_02,$NEW_ID,MaxScore,10000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_03,$NEW_ID,MaxScore,15000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_04,$NEW_ID,MaxScore,20000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_05,$NEW_ID,MaxScore,30000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_06,$NEW_ID,MaxScore,40000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_07,$NEW_ID,MaxScore,50000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_08,$NEW_ID,MaxScore,75000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_09,$NEW_ID,MaxScore,100000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_10,$NEW_ID,MaxScore,150000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_11,$NEW_ID,MaxScore,200000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_12,$NEW_ID,MaxScore,300000,$RELEASE_KEY"
    
    # ランク到達報酬（Rank）- 16段階
    for i in $(seq -f "%02g" 1 16); do
        echo "e,${NEW_REWARD_PREFIX}_rank_${i},$NEW_ID,Rank,${NEW_ID}_rank_${i},$RELEASE_KEY"
    done
    
    # ランキング報酬（Ranking）- 9段階
    echo "e,${NEW_REWARD_PREFIX}_ranking_01,$NEW_ID,Ranking,1,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_02,$NEW_ID,Ranking,2,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_03,$NEW_ID,Ranking,3,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_04,$NEW_ID,Ranking,50,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_05,$NEW_ID,Ranking,300,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_06,$NEW_ID,Ranking,1000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_07,$NEW_ID,Ranking,5000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_08,$NEW_ID,Ranking,10000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_09,$NEW_ID,Ranking,99999,$RELEASE_KEY"
    
} > "$OUTPUT_DIR/MstAdventBattleRewardGroup.csv"

# 2. MstAdventBattleReward.csv
echo "2. MstAdventBattleReward.csv を作成中..."
{
    head -1 "$PAST_DIR/MstAdventBattleReward.csv"
    
    # ハイスコア目標達成報酬（運営仕様書の84-95行目より）
    # 5000pt: プリズム20
    echo "e,${NEW_REWARD_PREFIX}_01_01,${NEW_REWARD_PREFIX}_01,FreeDiamond,prism_glo_00001,20,$RELEASE_KEY"
    # 10000pt: コイン1500
    echo "e,${NEW_REWARD_PREFIX}_02_01,${NEW_REWARD_PREFIX}_02,Coin,,1500,$RELEASE_KEY"
    # 15000pt: プリズム30
    echo "e,${NEW_REWARD_PREFIX}_03_01,${NEW_REWARD_PREFIX}_03,FreeDiamond,prism_glo_00001,30,$RELEASE_KEY"
    # 20000pt: コイン3000
    echo "e,${NEW_REWARD_PREFIX}_04_01,${NEW_REWARD_PREFIX}_04,Coin,,3000,$RELEASE_KEY"
    # 30000pt: プリズム50
    echo "e,${NEW_REWARD_PREFIX}_05_01,${NEW_REWARD_PREFIX}_05,FreeDiamond,prism_glo_00001,50,$RELEASE_KEY"
    # 40000pt: コイン4500
    echo "e,${NEW_REWARD_PREFIX}_06_01,${NEW_REWARD_PREFIX}_06,Coin,,4500,$RELEASE_KEY"
    # 50000pt: プリズム50
    echo "e,${NEW_REWARD_PREFIX}_07_01,${NEW_REWARD_PREFIX}_07,FreeDiamond,prism_glo_00001,50,$RELEASE_KEY"
    # 75000pt: コイン6000
    echo "e,${NEW_REWARD_PREFIX}_08_01,${NEW_REWARD_PREFIX}_08,Coin,,6000,$RELEASE_KEY"
    # 100000pt: プリズム100
    echo "e,${NEW_REWARD_PREFIX}_09_01,${NEW_REWARD_PREFIX}_09,FreeDiamond,prism_glo_00001,100,$RELEASE_KEY"
    # 150000pt: メモリーフラグメント・上級 1
    echo "e,${NEW_REWARD_PREFIX}_10_01,${NEW_REWARD_PREFIX}_10,Item,memoryfragment_glo_00003,1,$RELEASE_KEY"
    # 200000pt: スペシャルガシャチケット 1
    echo "e,${NEW_REWARD_PREFIX}_11_01,${NEW_REWARD_PREFIX}_11,Item,ticket_glo_00002,1,$RELEASE_KEY"
    # 300000pt: コイン10000
    echo "e,${NEW_REWARD_PREFIX}_12_01,${NEW_REWARD_PREFIX}_12,Coin,,10000,$RELEASE_KEY"
    
    # ランク到達報酬（運営仕様書の60-75行目より）
    # Bronze1-4: プリズム10, コイン1000, メモリーフラグメント・初級1
    for i in 01 02 03 04; do
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_01,${NEW_REWARD_PREFIX}_rank_${i},FreeDiamond,prism_glo_00001,10,$RELEASE_KEY"
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_02,${NEW_REWARD_PREFIX}_rank_${i},Coin,,1000,$RELEASE_KEY"
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_03,${NEW_REWARD_PREFIX}_rank_${i},Item,memoryfragment_glo_00001,1,$RELEASE_KEY"
    done
    
    # Silver1-2: プリズム20, コイン2000, メモリーフラグメント・初級1
    for i in 05 06; do
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_01,${NEW_REWARD_PREFIX}_rank_${i},FreeDiamond,prism_glo_00001,20,$RELEASE_KEY"
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_02,${NEW_REWARD_PREFIX}_rank_${i},Coin,,2000,$RELEASE_KEY"
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_03,${NEW_REWARD_PREFIX}_rank_${i},Item,memoryfragment_glo_00001,1,$RELEASE_KEY"
    done
    
    # Silver3-4: プリズム20, コイン2000, メモリーフラグメント・初級2
    for i in 07 08; do
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_01,${NEW_REWARD_PREFIX}_rank_${i},FreeDiamond,prism_glo_00001,20,$RELEASE_KEY"
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_02,${NEW_REWARD_PREFIX}_rank_${i},Coin,,2000,$RELEASE_KEY"
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_03,${NEW_REWARD_PREFIX}_rank_${i},Item,memoryfragment_glo_00001,2,$RELEASE_KEY"
    done
    
    # Gold1-2: プリズム30, コイン3000, 初級2, 中級1/2
    echo "e,${NEW_REWARD_PREFIX}_rank_09_01,${NEW_REWARD_PREFIX}_rank_09,FreeDiamond,prism_glo_00001,30,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_rank_09_02,${NEW_REWARD_PREFIX}_rank_09,Coin,,3000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_rank_09_03,${NEW_REWARD_PREFIX}_rank_09,Item,memoryfragment_glo_00001,2,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_rank_09_04,${NEW_REWARD_PREFIX}_rank_09,Item,memoryfragment_glo_00002,1,$RELEASE_KEY"
    
    echo "e,${NEW_REWARD_PREFIX}_rank_10_01,${NEW_REWARD_PREFIX}_rank_10,FreeDiamond,prism_glo_00001,30,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_rank_10_02,${NEW_REWARD_PREFIX}_rank_10,Coin,,3000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_rank_10_03,${NEW_REWARD_PREFIX}_rank_10,Item,memoryfragment_glo_00001,2,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_rank_10_04,${NEW_REWARD_PREFIX}_rank_10,Item,memoryfragment_glo_00002,2,$RELEASE_KEY"
    
    # Gold3-4: プリズム30, コイン3000, 初級2, 中級2
    for i in 11 12; do
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_01,${NEW_REWARD_PREFIX}_rank_${i},FreeDiamond,prism_glo_00001,30,$RELEASE_KEY"
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_02,${NEW_REWARD_PREFIX}_rank_${i},Coin,,3000,$RELEASE_KEY"
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_03,${NEW_REWARD_PREFIX}_rank_${i},Item,memoryfragment_glo_00001,2,$RELEASE_KEY"
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_04,${NEW_REWARD_PREFIX}_rank_${i},Item,memoryfragment_glo_00002,2,$RELEASE_KEY"
    done
    
    # Master1-3: プリズム40, コイン4000, 初級3, 中級2
    for i in 13 14 15; do
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_01,${NEW_REWARD_PREFIX}_rank_${i},FreeDiamond,prism_glo_00001,40,$RELEASE_KEY"
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_02,${NEW_REWARD_PREFIX}_rank_${i},Coin,,4000,$RELEASE_KEY"
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_03,${NEW_REWARD_PREFIX}_rank_${i},Item,memoryfragment_glo_00001,3,$RELEASE_KEY"
        echo "e,${NEW_REWARD_PREFIX}_rank_${i}_04,${NEW_REWARD_PREFIX}_rank_${i},Item,memoryfragment_glo_00002,2,$RELEASE_KEY"
    done
    
    # Master4: プリズム40, コイン4000, 初級3, 中級2, 上級1
    echo "e,${NEW_REWARD_PREFIX}_rank_16_01,${NEW_REWARD_PREFIX}_rank_16,FreeDiamond,prism_glo_00001,40,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_rank_16_02,${NEW_REWARD_PREFIX}_rank_16,Coin,,4000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_rank_16_03,${NEW_REWARD_PREFIX}_rank_16,Item,memoryfragment_glo_00001,3,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_rank_16_04,${NEW_REWARD_PREFIX}_rank_16,Item,memoryfragment_glo_00002,2,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_rank_16_05,${NEW_REWARD_PREFIX}_rank_16,Item,memoryfragment_glo_00003,1,$RELEASE_KEY"
    
    # ランキング報酬（運営仕様書の108-116行目より）
    # 1位: エンブレム, プリズム1000, コイン100000, スペシャルガシャチケット5
    echo "e,${NEW_REWARD_PREFIX}_ranking_01_01,${NEW_REWARD_PREFIX}_ranking_01,Emblem,emblem_adventbattle_jig_season01_00001,1,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_01_02,${NEW_REWARD_PREFIX}_ranking_01,FreeDiamond,prism_glo_00001,1000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_01_03,${NEW_REWARD_PREFIX}_ranking_01,Coin,,100000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_01_04,${NEW_REWARD_PREFIX}_ranking_01,Item,ticket_glo_00002,5,$RELEASE_KEY"
    
    # 2位: エンブレム, プリズム750, コイン75000, スペシャルガシャチケット5
    echo "e,${NEW_REWARD_PREFIX}_ranking_02_01,${NEW_REWARD_PREFIX}_ranking_02,Emblem,emblem_adventbattle_jig_season01_00002,1,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_02_02,${NEW_REWARD_PREFIX}_ranking_02,FreeDiamond,prism_glo_00001,750,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_02_03,${NEW_REWARD_PREFIX}_ranking_02,Coin,,75000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_02_04,${NEW_REWARD_PREFIX}_ranking_02,Item,ticket_glo_00002,5,$RELEASE_KEY"
    
    # 3位: エンブレム, プリズム500, コイン50000, スペシャルガシャチケット5
    echo "e,${NEW_REWARD_PREFIX}_ranking_03_01,${NEW_REWARD_PREFIX}_ranking_03,Emblem,emblem_adventbattle_jig_season01_00003,1,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_03_02,${NEW_REWARD_PREFIX}_ranking_03,FreeDiamond,prism_glo_00001,500,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_03_03,${NEW_REWARD_PREFIX}_ranking_03,Coin,,50000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_03_04,${NEW_REWARD_PREFIX}_ranking_03,Item,ticket_glo_00002,5,$RELEASE_KEY"
    
    # 4-50位: エンブレム, プリズム300, コイン30000, スペシャルガシャチケット4
    echo "e,${NEW_REWARD_PREFIX}_ranking_04_01,${NEW_REWARD_PREFIX}_ranking_04,Emblem,emblem_adventbattle_jig_season01_00004,1,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_04_02,${NEW_REWARD_PREFIX}_ranking_04,FreeDiamond,prism_glo_00001,300,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_04_03,${NEW_REWARD_PREFIX}_ranking_04,Coin,,30000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_04_04,${NEW_REWARD_PREFIX}_ranking_04,Item,ticket_glo_00002,4,$RELEASE_KEY"
    
    # 51-300位: エンブレム, プリズム200, コイン20000, スペシャルガシャチケット3
    echo "e,${NEW_REWARD_PREFIX}_ranking_05_01,${NEW_REWARD_PREFIX}_ranking_05,Emblem,emblem_adventbattle_jig_season01_00005,1,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_05_02,${NEW_REWARD_PREFIX}_ranking_05,FreeDiamond,prism_glo_00001,200,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_05_03,${NEW_REWARD_PREFIX}_ranking_05,Coin,,20000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_05_04,${NEW_REWARD_PREFIX}_ranking_05,Item,ticket_glo_00002,3,$RELEASE_KEY"
    
    # 301-1000位: エンブレム, プリズム150, コイン15000, スペシャルガシャチケット3
    echo "e,${NEW_REWARD_PREFIX}_ranking_06_01,${NEW_REWARD_PREFIX}_ranking_06,Emblem,emblem_adventbattle_jig_season01_00006,1,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_06_02,${NEW_REWARD_PREFIX}_ranking_06,FreeDiamond,prism_glo_00001,150,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_06_03,${NEW_REWARD_PREFIX}_ranking_06,Coin,,15000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_06_04,${NEW_REWARD_PREFIX}_ranking_06,Item,ticket_glo_00002,3,$RELEASE_KEY"
    
    # 1001-5000位: プリズム100, コイン10000, スペシャルガシャチケット2
    echo "e,${NEW_REWARD_PREFIX}_ranking_07_01,${NEW_REWARD_PREFIX}_ranking_07,FreeDiamond,prism_glo_00001,100,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_07_02,${NEW_REWARD_PREFIX}_ranking_07,Coin,,10000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_07_03,${NEW_REWARD_PREFIX}_ranking_07,Item,ticket_glo_00002,2,$RELEASE_KEY"
    
    # 5001-10000位: プリズム50, コイン5000, スペシャルガシャチケット2
    echo "e,${NEW_REWARD_PREFIX}_ranking_08_01,${NEW_REWARD_PREFIX}_ranking_08,FreeDiamond,prism_glo_00001,50,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_08_02,${NEW_REWARD_PREFIX}_ranking_08,Coin,,5000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_08_03,${NEW_REWARD_PREFIX}_ranking_08,Item,ticket_glo_00002,2,$RELEASE_KEY"
    
    # 10001位以降: プリズム20, コイン2000, スペシャルガシャチケット1
    echo "e,${NEW_REWARD_PREFIX}_ranking_09_01,${NEW_REWARD_PREFIX}_ranking_09,FreeDiamond,prism_glo_00001,20,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_09_02,${NEW_REWARD_PREFIX}_ranking_09,Coin,,2000,$RELEASE_KEY"
    echo "e,${NEW_REWARD_PREFIX}_ranking_09_03,${NEW_REWARD_PREFIX}_ranking_09,Item,ticket_glo_00002,1,$RELEASE_KEY"
    
} > "$OUTPUT_DIR/MstAdventBattleReward.csv"

echo "=== 降臨バトル 報酬データ作成完了 ==="
echo ""
echo "作成されたファイル:"
ls -lh "$OUTPUT_DIR"/MstAdventBattle*.csv

