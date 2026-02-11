#!/bin/bash

# ショップ/パックマスタデータ作成スクリプト
# 【お一人様1回まで購入可】お得強化パック

OUTPUT_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202602015"
PAST_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/raw-data/masterdata/released/202601010/past_tables"

# 出力先ディレクトリの確認
cd "$OUTPUT_DIR" || exit 1

# ========================================
# MstStoreProduct.csv
# ========================================
echo "Creating MstStoreProduct.csv..."

cat > MstStoreProduct.csv << 'EOF'
ENABLE,id,product_id_ios,product_id_android,product_id_webstore,release_key
e,52,BNEI0434_0050,com.bandainamcoent.jumble_0050,"",202601010
EOF

# ========================================
# MstStoreProductI18n.csv
# ========================================
echo "Creating MstStoreProductI18n.csv..."

cat > MstStoreProductI18n.csv << 'EOF'
ENABLE,release_key,id,mst_store_product_id,language,price_ios,price_android,price_webstore,paid_diamond_price_ios,paid_diamond_price_android,paid_diamond_price_webstore
e,202601010,52_ja,52,ja,980,980,0,0,0,0
EOF

# ========================================
# MstPack.csv
# ========================================
echo "Creating MstPack.csv..."

cat > MstPack.csv << 'EOF'
ENABLE,id,product_sub_id,discount_rate,sale_condition,sale_condition_value,sale_hours,is_display_expiration,pack_type,tradable_count,cost_type,is_first_time_free,cost_amount,is_recommend,asset_key,pack_decoration,release_key
e,monthly_item_pack_3,52,0,,0,"",1,Normal,0,Cash,0,0,0,pack_00017,,202601010
EOF

# ========================================
# MstPackI18n.csv
# ========================================
echo "Creating MstPackI18n.csv..."

cat > MstPackI18n.csv << 'EOF'
ENABLE,release_key,id,mst_pack_id,language,name
e,202601010,monthly_item_pack_3_ja,monthly_item_pack_3,ja,【お一人様1回まで購入可】\nお得強化パック
EOF

# ========================================
# MstPackContent.csv
# ========================================
echo "Creating MstPackContent.csv..."

# 次に使用可能なIDを確認 (過去データの最大IDは119)
# 新規IDは120から開始

cat > MstPackContent.csv << 'EOF'
ENABLE,id,mst_pack_id,resource_type,resource_id,resource_amount,is_bonus,display_order,release_key
e,120,monthly_item_pack_3,Item,memoryfragment_glo_00001,50,"",3,202601010
e,121,monthly_item_pack_3,Item,memoryfragment_glo_00002,30,"",2,202601010
e,122,monthly_item_pack_3,Item,memoryfragment_glo_00003,3,"",1,202601010
EOF

echo "✅ All shop/pack CSV files created successfully!"
echo ""
echo "Created files:"
echo "  - MstStoreProduct.csv (1 record)"
echo "  - MstStoreProductI18n.csv (1 record)"
echo "  - MstPack.csv (1 record)"
echo "  - MstPackI18n.csv (1 record)"
echo "  - MstPackContent.csv (3 records)"
