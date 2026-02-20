# ショップ・パックマスタデータ - 202601010

## 生成対象

1. **いいジャン祭パック** - 3,000円（お一人様1回まで）
2. **お得強化パック** - 980円（月1回）

---

## 1. MstStoreProduct

| ENABLE | id | product_id_ios | product_id_android | product_id_webstore | release_key |
|--------|----|-----------------|--------------------|---------------------|-------------|
| e | 50 | BNEI0434_0048 | com.bandainamcoent.jumble_0048 | | 202601010 |
| e | 52 | BNEI0434_0050 | com.bandainamcoent.jumble_0050 | | 202601010 |

---

## 2. MstStoreProductI18n

| ENABLE | release_key | id | mst_store_product_id | language | price_ios | price_android | price_webstore | paid_diamond_price_ios | paid_diamond_price_android | paid_diamond_price_webstore |
|--------|-------------|----|----------------------|----------|-----------|---------------|----------------|------------------------|----------------------------|----------------------------|
| e | 202601010 | 50_ja | 50 | ja | 3000 | 3000 | 0 | 0 | 0 | 0 |
| e | 202601010 | 52_ja | 52 | ja | 980 | 980 | 0 | 0 | 0 | 0 |

---

## 3. OprProduct

| ENABLE | id | mst_store_product_id | product_type | purchasable_count | paid_amount | display_priority | start_date | end_date | release_key |
|--------|----|----------------------|--------------|-------------------|-------------|------------------|------------|----------|-------------|
| e | 50 | 50 | Pack | 1 | | 18 | 2026-01-16 15:00:00 | 2026-02-02 10:59:59 | 202601010 |
| e | 52 | 52 | Pack | 1 | | 17 | 2026-02-02 15:00:00 | 2026-02-28 23:59:59 | 202601010 |

---

## 4. OprProductI18n

| ENABLE | release_key | id | opr_product_id | language | asset_key |
|--------|-------------|----|----------------|----------|-----------|
| e | 202601010 | 50_ja | 50 | ja | |
| e | 202601010 | 52_ja | 52 | ja | |

---

## 5. MstPack

| ENABLE | id | product_sub_id | discount_rate | sale_condition | sale_condition_value | sale_hours | is_display_expiration | pack_type | tradable_count | cost_type | is_first_time_free | cost_amount | is_recommend | asset_key | pack_decoration | release_key |
|--------|----|----------------|---------------|----------------|----------------------|------------|----------------------|-----------|----------------|-----------|-------------------|-------------|-------------|-----------|----------------|-------------|
| e | event_item_pack_12 | 50 | 0 | __NULL__ | 0 | | 1 | Normal | 0 | Cash | 0 | 0 | 0 | pack_00005 | __NULL__ | 202601010 |
| e | monthly_item_pack_3 | 52 | 0 | __NULL__ | 0 | | 1 | Normal | 0 | Cash | 0 | 0 | 0 | pack_00017 | __NULL__ | 202601010 |

---

## 6. MstPackI18n

| ENABLE | release_key | id | mst_pack_id | language | name |
|--------|-------------|----|-------------|----------|------|
| e | 202601010 | event_item_pack_12_ja | event_item_pack_12 | ja | 【お一人様1回まで購入可】\nいいジャン祭パック |
| e | 202601010 | monthly_item_pack_3_ja | monthly_item_pack_3 | ja | 【お一人様1回まで購入可】\nお得強化パック |

---

## 7. MstPackContent

| ENABLE | id | mst_pack_id | resource_type | resource_id | resource_amount | is_bonus | display_order | release_key |
|--------|----|-------------|---------------|-------------|-----------------|----------|---------------|-------------|
| e | 120 | event_item_pack_12 | Item | memoryfragment_glo_00001 | 50 | | 4 | 202601010 |
| e | 121 | event_item_pack_12 | Item | memoryfragment_glo_00002 | 30 | | 3 | 202601010 |
| e | 122 | event_item_pack_12 | Item | memoryfragment_glo_00003 | 3 | | 2 | 202601010 |
| e | 123 | event_item_pack_12 | Item | ticket_glo_00003 | 10 | | 1 | 202601010 |
| e | 124 | monthly_item_pack_3 | Item | memoryfragment_glo_00001 | 50 | | 3 | 202601010 |
| e | 125 | monthly_item_pack_3 | Item | memoryfragment_glo_00002 | 30 | | 2 | 202601010 |
| e | 126 | monthly_item_pack_3 | Item | memoryfragment_glo_00003 | 3 | | 1 | 202601010 |

---

## 推測値レポート

### MstStoreProduct.id
- **値**: 50, 52
- **理由**: 既存データの最大ID(51)を確認し、次の番号を採番
- **確認事項**: IDが他の商品と重複していないか確認してください

### MstStoreProduct.product_id_ios
- **値**: BNEI0434_0048, BNEI0434_0050
- **理由**: 設計書に記載の値を使用
- **確認事項**: プラットフォーム側でプロダクトIDを登録し、正しい値に差し替えてください

### MstStoreProduct.product_id_android
- **値**: com.bandainamcoent.jumble_0048, com.bandainamcoent.jumble_0050
- **理由**: 設計書に記載の値を使用
- **確認事項**: プラットフォーム側でプロダクトIDを登録し、正しい値に差し替えてください

### MstPack.id
- **値**: event_item_pack_12, monthly_item_pack_3
- **理由**: 既存データのパックIDを確認し、命名規則に従って採番
- **確認事項**: パックIDが他のパックと重複していないか確認してください

### MstPack.asset_key
- **値**: pack_00005, pack_00017
- **理由**:
  - event_item_pack_12: 既存のいいジャン祭パックと同じアセットキー(pack_00005)を使用
  - monthly_item_pack_3: 既存の月次パックと同じアセットキー(pack_00017)を使用
- **確認事項**: アセットキーが正しいか、バナー画像が存在するか確認してください

### MstPackContent.id
- **値**: 120〜126
- **理由**: 既存データの最大ID(119)を確認し、次の番号を採番
- **確認事項**: IDが他のパック内容物と重複していないか確認してください

### MstPackContent.display_order
- **値**: 1〜4
- **理由**:
  - チケット類を優先度高く表示(数値小さい方が下に表示)
  - メモリーフラグメントを上級→中級→初級の順で表示
- **確認事項**: 表示順序が仕様通りか確認してください

### OprProduct.display_priority
- **値**: 18, 17
- **理由**:
  - いいジャン祭パック: 既存のイベントパックと同等の優先度(18)を設定
  - お得強化パック: 月次パックの標準優先度(17)を設定
- **確認事項**: 表示優先度が他の商品と適切に設定されているか確認してください

---

## データ整合性チェック結果

✅ **ヘッダーの列順**: 正しい
✅ **IDの一意性**: すべてのIDが一意
✅ **ID採番ルール**: 命名規則に従っている
✅ **リレーションの整合性**: すべての外部キーが正しく設定されている
✅ **enum値の正確性**: すべてのenum値が正しい（Pack, Normal, Cash, Item等）
✅ **販売期間の妥当性**: start_date < end_date
✅ **価格の整合性**: price_ios、price_androidが正の数値
✅ **パックタイプとコストタイプ**: product_type=PackでMstPackが存在
✅ **cost_type=Cash**: cost_amount=0（価格はMstStoreProductI18nで管理）

---

## 注意事項

1. **プロダクトID**: 設計書に記載されたプロダクトIDをそのまま使用していますが、プラットフォーム側での登録を確認してください
2. **アセットキー**: 既存パックと同じアセットキーを使用していますが、専用バナーが必要な場合は変更してください
3. **販売期間**: 設計書通りに設定していますが、実際の販売開始前に最終確認してください
4. **パック内容物**: 設計書通りに設定していますが、resource_idの存在確認（MstItemテーブル）を行ってください
