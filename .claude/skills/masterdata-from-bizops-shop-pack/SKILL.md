---
name: masterdata-from-bizops-shop-pack
description: ショップ・パック商品の運営仕様書からマスタデータCSVを作成するスキル。対象テーブル: 7個(MstStoreProduct, MstStoreProductI18n, OprProduct, OprProductI18n, MstPack, MstPackI18n, MstPackContent)。課金パック、ダイヤ商品等のマスタデータを精度高く作成します。
---

# ショップ・パック マスタデータ作成スキル

## 概要

ショップで販売するパック商品のマスタデータCSVを運営仕様書から作成します。設計書に記載された情報を元に、DB投入可能な形式のマスタデータを自動生成し、推測で決定した値は必ずレポートします。

### 作成対象テーブル

以下の7テーブルを自動生成:

**ストア商品情報**:
- **MstStoreProduct** - プラットフォーム(iOS/Android/Web)のプロダクトID管理
- **MstStoreProductI18n** - ストア商品の価格情報(多言語対応)

**期間限定商品情報**:
- **OprProduct** - 実際に販売する商品の期間・優先度設定
- **OprProductI18n** - 商品のアセットキー(多言語対応)

**パック情報**:
- **MstPack** - パックの基本設定(割引率、コスト等)
- **MstPackI18n** - パック名(多言語対応)
- **MstPackContent** - パックの内容物

## 基本的な使い方

### 必須パラメータ

以下のパラメータを指定してください:

| パラメータ名 | 説明 | 例 |
|------------|------|-----|
| **release_key** | リリースキー | `202601010` |
| **event_name** | イベント名 | `地獄楽 いいジャン祭` |
| **product_type** | 商品タイプ | `Pack`(Pack/Diamond/Pass) |
| **pack_name** | パック名 | `いいジャン祭 開催記念パック` |
| **price_ios** | iOS価格 | `3000` |
| **price_android** | Android価格 | `3000` |
| **sale_period_start** | 販売開始日時 | `2026-01-16 15:00:00` |
| **sale_period_end** | 販売終了日時 | `2026-02-02 10:59:59` |
| **purchasable_count** | 購入可能回数 | `1`(1回限り) |

### 実行方法

運営仕様書ファイルを添付して、以下のプロンプトを実行してください:

```
ショップ・パックの運営仕様書からマスタデータを作成してください。

添付ファイル:
- ショップ商品設計書_地獄楽_いいジャン祭.xlsx

パラメータ:
- release_key: 202601010
- event_name: 地獄楽 いいジャン祭
- product_type: Pack
- pack_name: いいジャン祭 開催記念パック
- price_ios: 3000
- price_android: 3000
- sale_period_start: 2026-01-16 15:00:00
- sale_period_end: 2026-02-02 10:59:59
- purchasable_count: 1
```

## ワークフロー

### Step 1: 仕様書の読み込み

運営仕様書から以下の情報を抽出します:

**必須情報**:
- パック名(商品名)
- 販売価格(iOS、Android、Webstore)
- 販売期間(開始日時、終了日時)
- 購入可能回数(1回限り、無制限等)
- パックタイプ(Normal、Daily)
- コストタイプ(Cash: 課金、Diamond: ダイヤ、PaidDiamond: 有償ダイヤ、Free: 無料)
- コスト量
- 内容物(アイテムID、数量、おまけフラグ)

**任意情報**:
- 割引率(記載がない場合は0)
- 表示優先度(記載がない場合は推測)
- おすすめフラグ(記載がない場合は0)
- 初回無料フラグ(記載がない場合は0)
- 有効期限表示フラグ(記載がない場合は1)
- アセットキー(記載がない場合は推測)

### Step 2: マスタデータ生成

詳細ルールは [references/manual.md](references/manual.md) を参照し、以下のテーブルを作成します:

1. **MstStoreProduct** - プラットフォームプロダクトID設定
2. **MstStoreProductI18n** - 価格情報(多言語対応)
3. **OprProduct** - 販売期間・優先度設定
4. **OprProductI18n** - アセットキー設定(多言語対応)
5. **MstPack** - パック基本設定(product_type=Packの場合のみ)
6. **MstPackI18n** - パック名(product_type=Packの場合のみ)
7. **MstPackContent** - パック内容物(product_type=Packの場合のみ)

#### ID採番ルール

ショップ・パックのIDは以下の形式で採番します:

```
MstStoreProduct.id: 連番採番(例: 50, 52, 64)
OprProduct.id: MstStoreProduct.idと同じ値
MstPack.id: event_item_pack_{連番} または monthly_item_pack_{連番}
MstPackContent.id: 連番採番(例: 113, 114, 115)
I18n系テーブルのid: {親テーブルid}_{language}
```

**例**:
```
50 (ストア商品ID)
50 (運営商品ID)
event_item_pack_12 (パックID)
113 (パック内容物1)
50_ja (日本語I18n)
```

### Step 3: データ整合性チェック

以下の項目を自動確認し、問題があれば修正します:

- [ ] ヘッダーの列順が正しいか
- [ ] すべてのIDが一意であるか
- [ ] ID採番ルールに従っているか
- [ ] リレーションが正しく設定されているか
- [ ] enum値が正確に一致しているか(product_type、pack_type、cost_type、resource_type等)
- [ ] 販売期間が妥当か(start_date < end_date)
- [ ] 価格が正の数値であるか
- [ ] product_type=Packの場合、MstPack系テーブルが存在するか
- [ ] cost_type=Cashの場合、cost_amount=0であるか

### Step 4: 推測値レポート

設計書に記載がなく、推測で決定した値を必ずレポートします。

**推測値の例**:
- `MstStoreProduct.id`: 連番採番の推測値
- `MstStoreProduct.product_id_ios`: プロダクトIDの推測値
- `MstStoreProduct.product_id_android`: プロダクトIDの推測値
- `MstPack.id`: パックIDの推測値
- `MstPack.asset_key`: アセットキーの推測値
- `MstPackContent.id`: 連番採番の推測値
- `MstPackContent.display_order`: 表示順序の推測値
- `OprProduct.display_priority`: 表示優先度の推測値

### Step 5: 出力

以下の形式で出力します:

#### 1. マスタデータ(Markdown表形式)

- スプレッドシートへのエクスポート・コピーボタンが正常に表示される形式
- 以下の7シート構成で作成:
  1. MstStoreProduct
  2. MstStoreProductI18n
  3. OprProduct
  4. OprProductI18n
  5. MstPack(product_type=Packの場合のみ)
  6. MstPackI18n(product_type=Packの場合のみ)
  7. MstPackContent(product_type=Packの場合のみ)

#### 2. 推測値レポート(必須)

作成したデータのうち、以下に該当するものを必ずレポートします:

- **添付ファイルにも手順書にも記載がなく、推測で決定したID値やパラメータ値**
- 手順書通りに作成したID値は対象外

**レポート形式:**
```
## 推測値レポート

### MstStoreProduct.id
- 値: 50(推測値)
- 理由: 既存の最大IDを確認し、次の番号を採番
- 確認事項: IDが他の商品と重複していないか確認してください

### MstStoreProduct.product_id_ios
- 値: BNEI0434_0048(推測値)
- 理由: 既存のプロダクトIDを確認し、次の番号を採番
- 確認事項: プラットフォーム側でプロダクトIDを登録し、正しい値に差し替えてください
```

**重要**: このレポートを怠ると、データインポートエラーや本番不具合のリスクが高まります。推測で決定した値は必ず報告してください。

## 出力例

### MstStoreProduct シート

| ENABLE | id | product_id_ios | product_id_android | product_id_webstore | release_key |
|--------|----|-----------------|--------------------|---------------------|-------------|
| e | 50 | BNEI0434_0048 | com.bandainamcoent.jumble_0048 | | 202601010 |

### MstStoreProductI18n シート

| ENABLE | release_key | id | mst_store_product_id | language | price_ios | price_android | price_webstore | paid_diamond_price_ios | paid_diamond_price_android | paid_diamond_price_webstore |
|--------|-------------|----|----------------------|----------|-----------|---------------|----------------|------------------------|----------------------------|----------------------------|
| e | 202601010 | 50_ja | 50 | ja | 3000 | 3000 | 0 | 0 | 0 | 0 |

### OprProduct シート

| ENABLE | id | mst_store_product_id | product_type | purchasable_count | paid_amount | display_priority | start_date | end_date | release_key |
|--------|----|----------------------|--------------|-------------------|-------------|------------------|------------|----------|-------------|
| e | 50 | 50 | Pack | 1 | | 18 | 2026-01-16 15:00:00 | 2026-02-02 10:59:59 | 202601010 |

### OprProductI18n シート

| ENABLE | release_key | id | opr_product_id | language | asset_key |
|--------|-------------|----|----------------|----------|-----------|
| e | 202601010 | 50_ja | 50 | ja | |

### MstPack シート

| ENABLE | id | product_sub_id | discount_rate | sale_condition | sale_condition_value | sale_hours | is_display_expiration | pack_type | tradable_count | cost_type | is_first_time_free | cost_amount | is_recommend | asset_key | pack_decoration | release_key |
|--------|----|----------------|---------------|----------------|----------------------|------------|----------------------|-----------|----------------|-----------|-------------------|-------------|-------------|-----------|----------------|-------------|
| e | event_item_pack_12 | 50 | 0 | __NULL__ | 0 | | 1 | Normal | 0 | Cash | 0 | 0 | 0 | pack_00005 | __NULL__ | 202601010 |

### MstPackI18n シート

| ENABLE | release_key | id | mst_pack_id | language | name |
|--------|-------------|----|-------------|----------|------|
| e | 202601010 | event_item_pack_12_ja | event_item_pack_12 | ja | 【お一人様1回まで購入可】\nいいジャン祭 開催記念パック |

### MstPackContent シート

| ENABLE | id | mst_pack_id | resource_type | resource_id | resource_amount | is_bonus | display_order | release_key |
|--------|----|-------------|---------------|-------------|-----------------|----------|---------------|-------------|
| e | 113 | event_item_pack_12 | Item | memoryfragment_glo_00001 | 50 | | 4 | 202601010 |
| e | 114 | event_item_pack_12 | Item | memoryfragment_glo_00002 | 30 | | 3 | 202601010 |
| e | 115 | event_item_pack_12 | Item | memoryfragment_glo_00003 | 3 | | 2 | 202601010 |
| e | 116 | event_item_pack_12 | Item | ticket_glo_00003 | 10 | | 1 | 202601010 |

### 推測値レポート

#### MstStoreProduct.id
- **値**: 50(推測値)
- **理由**: 既存の最大IDを確認し、次の番号を採番
- **確認事項**: IDが他の商品と重複していないか確認してください

#### MstStoreProduct.product_id_ios
- **値**: BNEI0434_0048(推測値)
- **理由**: 既存のプロダクトIDを確認し、次の番号を採番
- **確認事項**: プラットフォーム側でプロダクトIDを登録し、正しい値に差し替えてください

## 注意事項

### プラットフォームプロダクトIDについて

MstStoreProductのプロダクトID(product_id_ios、product_id_android)は、**一度定義したら変更できません**(リストア機能のため)。

**命名規則**:
- iOS: `BNEI0434_XXXX`(XXXXは4桁の連番)
- Android: `com.bandainamcoent.jumble_XXXX`(XXXXは4桁の連番)

既存のプロダクトIDを確認し、重複しない番号を採番してください。

### 商品タイプによる分岐

OprProduct.product_typeの値によって、作成するテーブルが異なります:

- **Pack**: MstPack、MstPackI18n、MstPackContentを作成
- **Diamond**: MstPackは作成不要(ダイヤ購入のみ)
- **Pass**: MstPackは作成不要(MstShopPassを使用)

### 価格管理の分離

- **課金商品(cost_type=Cash)の価格**: MstStoreProductI18nで管理
  - MstPack.cost_amount = 0
- **ダイヤ商品(cost_type=Diamond)のコスト**: MstPackで管理
  - MstPack.cost_amount = ダイヤ数量

### パック内容物の設定

MstPackContentは、パックに含まれるアイテムを設定します。

**resource_typeとresource_idの対応**:
- `FreeDiamond`: resource_id不要(空欄)
- `Coin`: resource_id不要(空欄)
- `Item`: resource_id = MstItem.id(例: `memoryfragment_glo_00001`、`ticket_glo_00003`)
- `Unit`: resource_id = MstUnit.id(例: `chara_jig_00401`)

**display_order**:
- 数字が大きいほど上に表示される
- 重要なアイテムほど大きい数字を設定

### 外部キー整合性について

以下のリレーションが正しく設定されていることを必ず確認してください:
- `MstStoreProductI18n.mst_store_product_id` → `MstStoreProduct.id`
- `OprProduct.mst_store_product_id` → `MstStoreProduct.id`
- `OprProductI18n.opr_product_id` → `OprProduct.id`
- `MstPack.product_sub_id` → `OprProduct.id`
- `MstPackI18n.mst_pack_id` → `MstPack.id`
- `MstPackContent.mst_pack_id` → `MstPack.id`
- `MstPackContent.resource_id` → `MstItem.id`(resource_type=Itemの場合)

## リファレンス

詳細なルールとenum値一覧:

- **[詳細手順書](references/manual.md)** - テーブル定義、カラム設定ルール、ID採番ルール、enum値一覧
- **[サンプル出力](examples/sample-output.md)** - 実際の出力例

## トラブルシューティング

### Q1: プロダクトIDの採番がわからない

**対処法**:
1. 既存のMstStoreProductテーブルを確認
2. 最大のproduct_id_iosとproduct_id_androidを確認
3. 次の連番を採番
4. 推測値レポートで報告

### Q2: enum値のエラーが発生する

**エラー**:
```
Invalid product_type: pack (expected: Pack)
```

**対処法**:
1. enum値は**大文字小文字を正確に一致**させる
2. 正しいenum値: Pack、Diamond、Pass
3. 頻出エラー: `pack` → `Pack`, `cash` → `Cash`

### Q3: パック内容物の表示順序がわからない

**対処法**:
- display_orderは数字が大きいほど上に表示
- 重要なアイテムほど大きい数字を設定
- 例: ダイヤ(10) → チケット(9) → メモリー(8)

## 検証

作成したマスタデータCSVは、`masterdata-csv-validator` スキルで検証できます:

```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv {作成したCSVファイルパス}
```

詳細は [masterdata-csv-validator](../../masterdata-csv-validator/SKILL.md) を参照してください。
