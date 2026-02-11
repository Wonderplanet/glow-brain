#!/bin/bash

# 地獄楽 降臨バトル「まるで 悪夢を見ているようだ」マスタデータ作成スクリプト

RELEASE_KEY="202601010"
PAST_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/raw-data/masterdata/released/202601010/past_tables"
OUTPUT_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202602015"

# 地獄楽の前回イベントデータ（sur1）をテンプレートとして使用
OLD_ID="quest_raid_sur1_00001"
NEW_ID="quest_raid_jig1_00001"
OLD_EVENT="event_sur_00001"
NEW_EVENT="event_jig_00001"
OLD_RAID="raid_sur1_00001"
NEW_RAID="raid_jig1_00001"
OLD_ASSET="sur_00001"
NEW_ASSET="jig_00002"
OLD_REWARD_PREFIX="quest_raid_sur1_reward_group_00001"
NEW_REWARD_PREFIX="quest_raid_jig1_reward_group_00001"

echo "=== 降臨バトル マスタデータ作成開始 ==="

# 1. MstAdventBattle.csv
echo "1. MstAdventBattle.csv を作成中..."
{
    head -1 "$PAST_DIR/MstAdventBattle.csv"
    grep "^e,$OLD_ID," "$PAST_DIR/MstAdventBattle.csv" | \
    sed "s/$OLD_ID/$NEW_ID/g" | \
    sed "s/$OLD_EVENT/$NEW_EVENT/g" | \
    sed "s/$OLD_RAID/$NEW_RAID/g" | \
    sed "s/$OLD_ASSET/$NEW_ASSET/g" | \
    sed "s/enemy_sur_00001/enemy_jig_00001/g" | \
    sed "s/2025-12-22 15:00:00/2026-01-23 15:00:00/g" | \
    sed "s/2025-12-29 14:59:59/2026-01-29 14:59:59/g" | \
    sed "s/202512010/$RELEASE_KEY/g" | \
    sed "s/,3,2,/,5,2,/"
} > "$OUTPUT_DIR/MstAdventBattle.csv"

# 2. MstAdventBattleI18n.csv
echo "2. MstAdventBattleI18n.csv を作成中..."
{
    head -1 "$PAST_DIR/MstAdventBattleI18n.csv"
    grep "^e,.*,$OLD_ID," "$PAST_DIR/MstAdventBattleI18n.csv" | \
    sed "s/$OLD_ID/$NEW_ID/g" | \
    sed "s/魔防隊と戦う者/まるで 悪夢を見ているようだ/g" | \
    sed "s/202512010/$RELEASE_KEY/g"
} > "$OUTPUT_DIR/MstAdventBattleI18n.csv"

# 3. MstAdventBattleRank.csv
echo "3. MstAdventBattleRank.csv を作成中..."
{
    head -1 "$PAST_DIR/MstAdventBattleRank.csv"
    grep "^e,.*,$OLD_ID," "$PAST_DIR/MstAdventBattleRank.csv" | \
    sed "s/${OLD_ID}_rank/${NEW_ID}_rank/g" | \
    sed "s/$OLD_ID/$NEW_ID/g" | \
    sed "s/202512010/$RELEASE_KEY/g"
} > "$OUTPUT_DIR/MstAdventBattleRank.csv"

# 4. MstAdventBattleClearReward.csv
echo "4. MstAdventBattleClearReward.csv を作成中..."
{
    head -1 "$PAST_DIR/MstAdventBattleClearReward.csv"
    # ランダムクリア報酬（カラーメモリー各色）
    for i in 1 2 3 4 5; do
        echo "e,${NEW_ID}_${i},${NEW_ID},Random,Item,memory_glo_0000${i},3,20,${i},$RELEASE_KEY"
    done
    # 固定クリア報酬（Coin）
    echo "e,${NEW_ID}_fixed_01,${NEW_ID},Fixed,Coin,,300,0,6,$RELEASE_KEY"
} > "$OUTPUT_DIR/MstAdventBattleClearReward.csv"

echo "=== 降臨バトル 基本データ作成完了 ==="
echo ""
echo "次に報酬データを作成します..."

