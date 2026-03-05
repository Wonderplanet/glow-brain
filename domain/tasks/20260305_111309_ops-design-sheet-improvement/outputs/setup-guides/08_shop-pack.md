# パック・商品販売（ショップ） マスタデータ設定手順書

## 概要

ショップで販売するパック商品（課金商品）のマスタデータ設定手順書。MstPack（ゲーム内パック定義）と OprProduct（販売情報）を連携させる。

- **report.md 対応セクション**: `機能別データ詳細 > パック・ショップ`

---

## 対象テーブル一覧と設定順序

| 作業順 | テーブル名 | 役割 | 必須/任意 |
|-------|-----------|------|---------|
| 1 | MstStoreProduct | ストア商品 ID（iOS/Android）定義 | 必須 |
| 2 | MstStoreProductI18n | ストア商品多言語名 | 任意 |
| 3 | MstPack | パック内容定義 | 必須 |
| 4 | MstPackI18n | パック多言語名 | 必須 |
| 5 | MstPackContent | パック内容物（アイテム一覧） | 必須 |
| 6 | OprProduct | 販売情報（期間・優先度） | 必須 |
| 7 | OprProductI18n | 販売情報多言語（バナー等） | 任意 |

---

## 前提条件・依存関係

- MstItem（アイテム）が登録済みであること
- MstPack.product_sub_id = OprProduct.id = MstStoreProduct.id（同一 ID で連結）
- iOS / Android / Webstore 各プラットフォームのプロダクト ID は事前に確認が必要

---

## report.md から読み取る情報チェックリスト

- [ ] パック名（日本語）
- [ ] パック販売期間（start_date / end_date）
- [ ] パックコンテンツ（アイテム種別・数量）
- [ ] 販売回数制限（purchasable_count）
- [ ] 表示優先度（display_priority）
- [ ] ストア商品 ID（iOS / Android）

---

## テーブル別設定手順

### MstStoreProduct（ストア商品 ID 定義）

各プラットフォームのアプリ内課金プロダクト ID を定義する。

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（OprProduct.id と同一） | `53` |
| product_id_ios | App Store プロダクト ID | `BNEI0434_0051` |
| product_id_android | Google Play プロダクト ID | `com.bandainamcoent.jumble_0051` |
| product_id_webstore | Webstore プロダクト ID（なければ NULL） | `NULL` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ（masterdata-explorer）**

```duckdb
SELECT id, product_id_ios, product_id_android, product_id_webstore, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstStoreProduct.csv')
ORDER BY id;
```

---

### MstPack（パック内容定義）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `event_item_pack_{連番}` | `event_item_pack_13` |
| product_sub_id | OprProduct.id と同一（連番） | `53` |
| discount_rate | 割引率（%）（0=割引なし） | `0` |
| sale_condition | セール条件（`__NULL__`=なし） | `__NULL__` |
| sale_condition_value | セール条件値 | `0` |
| sale_hours | セール表示期間 | `NULL` |
| is_display_expiration | 有効期限表示（0 or 1） | `1` |
| pack_type | パック種別（Normal/...） | `Normal` |
| tradable_count | 購入可能回数（0=無制限） | `0` |
| cost_type | コスト種別（Cash=課金） | `Cash` |
| is_first_time_free | 初回無料（0 or 1） | `0` |
| cost_amount | 金額（0=プラットフォーム側で管理） | `0` |
| is_recommend | おすすめ表示（0 or 1） | `0` |
| asset_key | アセットキー | `pack_00005` |
| pack_decoration | 装飾（`__NULL__`=なし） | `__NULL__` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ**

```duckdb
SELECT id, product_sub_id, pack_type, tradable_count, cost_type,
       is_first_time_free, is_recommend, asset_key, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstPack.csv')
ORDER BY release_key DESC;
```

---

### MstPackI18n（パック多言語名）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| release_key | 今回のリリースキー | `202602015` |
| id | `{mst_pack_id}_{language}` | `event_item_pack_13_ja` |
| mst_pack_id | 対応する MstPack.id | `event_item_pack_13` |
| language | 言語コード | `ja` |
| name | パック表示名 | `【お一人様1回まで購入可】\nいいジャン祭 開催記念パック` |

---

### MstPackContent（パック内容物）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（最終 ID + 1 から） | `123` |
| mst_pack_id | 対応する MstPack.id | `event_item_pack_13` |
| resource_type | リソース種別（Item/FreeDiamond/...） | `Item` |
| resource_id | リソース ID（Diamond は NULL） | `memoryfragment_glo_00001` |
| resource_amount | 数量 | `50` |
| is_bonus | ボーナスアイテムフラグ（NULL=通常） | `NULL` |
| display_order | 表示順 | `4` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_pack_id, resource_type, resource_id, resource_amount, display_order
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstPackContent.csv')
ORDER BY mst_pack_id, display_order;
```

---

### OprProduct（販売情報）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（MstPack.product_sub_id と同一） | `53` |
| mst_store_product_id | MstStoreProduct.id と同一 | `53` |
| product_type | 商品種別（Pack/...） | `Pack` |
| purchasable_count | 購入可能回数（1=1回のみ） | `1` |
| paid_amount | 販売金額（NULL=ストア側で管理） | `NULL` |
| display_priority | 表示優先度（大きいほど上位） | `18` |
| start_date | 販売開始日時（UTC） | `2026-02-02 15:00:00` |
| end_date | 販売終了日時（UTC） | `2026-02-16 10:59:59` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_store_product_id, product_type, purchasable_count,
       display_priority, start_date, end_date, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/OprProduct.csv')
ORDER BY display_priority DESC;
```

---

## 検証方法

- MstPack.product_sub_id = OprProduct.id = MstStoreProduct.id の三者が一致するか
- MstPackContent.mst_pack_id → MstPack.id が存在するか
- OprProduct.mst_store_product_id → MstStoreProduct.id が存在するか
- 販売期間の UTC 変換ミスがないか

---

## 参照リソース

- DBスキーマ: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- 利用スキル: `masterdata-explorer`, `masterdata-csv-validator`
- 過去リリース: `domain/raw-data/masterdata/released/202602015/tables/`
