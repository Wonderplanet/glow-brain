#!/bin/bash

# ヒーローステータス値修正スクリプト
# 過去データのテンプレート値を、ヒーロー基礎設計書の値に置き換え

set -e

OUTPUT_BASE_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202602015"

echo "========================================="
echo "ヒーローステータス値修正開始"
echo "========================================="
echo ""

# chara_jig_00401の修正
echo "chara_jig_00401 (賊王 亜左 弔兵衛) 修正中..."
sed -i '' \
    -e 's/,Red,/,Colorless,/' \
    -e 's/,985,1010,530,1510,2760,27600,3,50,0.26,2710,27100,/,1000,770,655,1140,2100,21000,3,30,0.31,2500,25000,/' \
    "$OUTPUT_BASE_DIR/chara_jig_00401/MstUnit.csv"
echo "  ✅ 完了"

# chara_jig_00501の修正
echo "chara_jig_00501 (山田浅ェ門 桐馬) 修正中..."
sed -i '' \
    -e 's/,Red,/,Green,/' \
    -e 's/,620,545,260,615,1490,14900,2,40,0.29,1740,17400,/,605,425,395,1740,1160,11600,2,30,0.32,1200,12000,/' \
    "$OUTPUT_BASE_DIR/chara_jig_00501/MstUnit.csv"
echo "  ✅ 完了"

# chara_jig_00601の修正
echo "chara_jig_00601 (民谷 巌鉄斎) 修正中..."
sed -i '' \
    -e 's/,Green,/,Blue,/' \
    -e 's/,320,705,370,940,1920,19200,1,30,0.21,610,6100,/,440,920,480,1195,2510,25100,1,35,0.21,880,8800,/' \
    -e 's/,PremiumSR,/,DropSR,/' \
    -e 's/,has_specific_rank_up,/,has_specific_rank_up,/' \
    -e 's/,0,jig,/,1,jig,/' \
    "$OUTPUT_BASE_DIR/chara_jig_00601/MstUnit.csv"
echo "  ✅ 完了"

# chara_jig_00701の修正
echo "chara_jig_00701 (メイ) 修正中..."
sed -i '' \
    -e 's/,Green,/,Colorless,/' \
    -e 's/,Defense,/,Technical,/' \
    -e 's/,320,705,370,940,1920,19200,1,30,0.21,610,6100,/,440,920,480,1195,1800,18000,1,30,0.25,750,7500,/' \
    -e 's/,PremiumSR,/,DropSR,/' \
    -e 's/,0,jig,/,1,jig,/' \
    "$OUTPUT_BASE_DIR/chara_jig_00701/MstUnit.csv"
echo "  ✅ 完了"

echo ""
echo "========================================="
echo "全キャラクターの修正完了"
echo "========================================="
