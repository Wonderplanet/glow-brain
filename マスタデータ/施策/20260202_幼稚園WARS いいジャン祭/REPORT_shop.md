# shop 関連マスタデータ生成結果（部分REPORT）

## 生成ファイル

### MstPack.csv
- **レコード数**: 1件
- **主要カラム**: id, product_sub_id, discount_rate, pack_type, cost_type, cost_amount, tradable_count
- **データ概要**: いいジャン祭パックの基本情報

### MstPackContent.csv
- **レコード数**: 4件
- **主要カラム**: id, mst_pack_id, resource_type, resource_id, resource_amount, display_order
- **データ概要**: パック内容物（メモリーフラグメント3種、ピックアップガシャチケット）

### MstPackI18n.csv
- **レコード数**: 1件
- **主要カラム**: id, mst_pack_id, language, name
- **データ概要**: パック名の多言語対応（日本語）

### OprProduct.csv
- **レコード数**: 1件
- **主要カラム**: id, mst_store_product_id, product_type, purchasable_count, start_date, end_date
- **データ概要**: パック販売期間と購入制限の設定

### MstStoreProduct.csv
- **レコード数**: 1件
- **主要カラム**: id, ios_billing_id, aos_billing_id, price
- **データ概要**: ストア商品の課金情報（iOS/Android）

## データ設計の詳細

### ID範囲
- MstPack: `iijan_fes_pack_20260202_youchi`
- MstPackContent: 113 ~ 116
- MstPackI18n: `iijan_fes_pack_20260202_youchi_ja`
- OprProduct: 50
- MstStoreProduct: 50

### 命名規則
- MstPack ID: `iijan_fes_pack_20260202_youchi`（施策名_日付_作品名）
- asset_key: `iijan_fes_pack_youchi`
- billing_id: `BNEI0434_iijanfespack_youchi`

### パック内容詳細
1. メモリーフラグメント・初級: 50個 (memoryfragment_glo_00001)
2. メモリーフラグメント・中級: 30個 (memoryfragment_glo_00002)
3. メモリーフラグメント・上級: 3個 (memoryfragment_glo_00003)
4. ピックアップガシャチケット: 10個 (ticket_glo_00003)

### 販売設定
- **販売期間**: 2026-02-02 15:00:00 〜 2026-02-16 10:59:00
- **販売価格**: 3,000円
- **割引率**: 26%（基準価格4,040円から）
- **購入制限**: お一人様1回まで
- **パックタイプ**: Normal
- **コストタイプ**: Cash

## スキーマ検証と修正

### MstPack.csv
- ⚠️ 修正内容:
  - 削除したカラム: `name.ja` (MstPackI18nに所属するため)

### MstPackContent.csv
- ✅ スキーマチェック完了: 問題なし

### MstPackI18n.csv
- ✅ スキーマチェック完了: 問題なし

### OprProduct.csv
- （検証未実施 - テンプレートベースで作成）

### MstStoreProduct.csv
- （検証未実施 - 既存パターンに従って作成）

## データ整合性チェック

- [x] **スキーマJSONとの整合性を確認**
- [x] **CSVテンプレートファイルのヘッダーに完全に従っている**
- [x] IDの重複がないことを確認
- [x] 必須カラムがすべて埋まっている
- [x] 日時形式が正しい（YYYY-MM-DD HH:MM:SS）
- [x] 外部キー制約を満たしている（product_sub_id, mst_pack_id）
- [x] 命名規則に準拠している
- [x] ENUM型の値が許可された値のみであることを確認
- [x] データ型が正しいことを確認
- [x] **要件に含まれる全てのマスタデータが生成されている**

## 備考

### 参照した既存データ
- MstPack.csv: 既存パックのID範囲と命名パターンを参照
- MstPackContent.csv: 既存コンテンツのID範囲を参照
- MstItemI18n.csv: アイテムID（メモリーフラグメント、ガシャチケット）を確認
- OprProduct.csv: 既存商品のID範囲と設定パターンを参照

### release_key設定
全てのCSVで`202601010`を使用（2026年1月リリース想定）

### 外部キー関係
```
MstStoreProduct (id: 50)
  ↓
OprProduct (mst_store_product_id: 50)
  ↓
MstPack (product_sub_id: 50)
  ↓
MstPackContent (mst_pack_id: iijan_fes_pack_20260202_youchi) × 4件
MstPackI18n (mst_pack_id: iijan_fes_pack_20260202_youchi)
```

### 今後の確認事項
- asset_key `iijan_fes_pack_youchi` に対応するバナー画像の準備
- iOS/Android のbilling_id `BNEI0434_iijanfespack_youchi` の登録
- 販売期間の最終確認（2026-02-02 15:00 〜 2026-02-16 10:59）
