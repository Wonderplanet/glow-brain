# ショップ・パック マスタデータ設定手順書

## 概要

ショップで販売するパック商品のマスタデータ作成手順を記載します。
本手順書に従うことで、ゲーム内でエラーが発生しない正確なマスタデータを作成できます。

## 対象テーブル

ショップ・パックのマスタデータは、以下の7テーブル構成で作成します。

**ストア商品情報**:
- **MstStoreProduct** - プラットフォーム（iOS/Android/Web）のプロダクトID管理
- **MstStoreProductI18n** - ストア商品の価格情報（多言語対応）

**期間限定商品情報**:
- **OprProduct** - 実際に販売する商品の期間・優先度設定
- **OprProductI18n** - 商品のアセットキー（多言語対応）

**パック情報**:
- **MstPack** - パックの基本設定（割引率、コスト等）
- **MstPackI18n** - パック名（多言語対応）
- **MstPackContent** - パックの内容物

**重要**: 各I18nテーブルは独立したシートとして作成します。

## 作成フロー

### 1. 仕様書の確認

運営仕様書から以下の情報を抽出します。

**必要情報**:
- パック名（商品名）
- 販売価格（iOS、Android、Webstore）
- 販売期間（開始日時、終了日時）
- 購入可能回数（1回限り、無制限等）
- パックタイプ（Normal、Daily）
- コストタイプ（Cash: 課金、Diamond: ダイヤ、PaidDiamond: 有償ダイヤ、Free: 無料）
- コスト量
- 内容物（アイテムID、数量、おまけフラグ）
- 割引率
- 表示優先度
- おすすめフラグ
- 初回無料フラグ
- 有効期限表示フラグ

### 2. MstStoreProduct シートの作成

#### 2.1 シートスキーマ

このシートには、ENABLE行とデータ行が含まれます。

**ENABLEと列名行** - カラム名を示します。

```
ENABLE,id,product_id_ios,product_id_android,product_id_webstore,release_key
```

#### 2.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | ストア商品の一意識別子。連番採番（例: 50, 52, 64など） |
| **product_id_ios** | iOS App StoreのプロダクトID。命名規則: `BNEI0434_XXXX` |
| **product_id_android** | Google PlayのプロダクトID。命名規則: `com.bandainamcoent.jumble_XXXX` |
| **product_id_webstore** | WebStoreのSKU。空欄の場合はモバイルアプリ専用商品 |
| **release_key** | リリースキー。例: `202601010` |

#### 2.3 ID採番ルール

MstStoreProduct.idは、連番で採番します。

**採番パターン**:
- 既存の最大IDを確認し、次の番号を採番
- 通常は10番台、50番台、60番台などに分類される

**採番例**:
```
50  (パック商品)
52  (パック商品)
64  (ダイヤ商品)
65  (ダイヤ商品)
```

**重要**: プラットフォームのプロダクトIDは、一度定義したら変更できません（リストア機能のため）。

#### 2.4 作成例

```
ENABLE,id,product_id_ios,product_id_android,product_id_webstore,release_key
e,50,BNEI0434_0048,com.bandainamcoent.jumble_0048,,202601010
e,52,BNEI0434_0050,com.bandainamcoent.jumble_0050,,202601010
```

### 3. MstStoreProductI18n シートの作成

#### 3.1 シートスキーマ

```
ENABLE,release_key,id,mst_store_product_id,language,price_ios,price_android,price_webstore,paid_diamond_price_ios,paid_diamond_price_android,paid_diamond_price_webstore
```

#### 3.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。MstStoreProductと同じ値 |
| **id** | I18nの一意識別子。命名規則: `{mst_store_product_id}_{language}` |
| **mst_store_product_id** | ストア商品ID。MstStoreProduct.idと対応 |
| **language** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語（簡体字）、`zh-TW`: 中国語（繁体字） |
| **price_ios** | iOS App Storeの価格（円単位） |
| **price_android** | Google Playの価格（円単位） |
| **price_webstore** | WebStoreの価格（円単位）。0: WebStore非対応 |
| **paid_diamond_price_ios** | 商品に含まれる有償通貨部分の価格（iOS）。通常は0 |
| **paid_diamond_price_android** | 商品に含まれる有償通貨部分の価格（Android）。通常は0 |
| **paid_diamond_price_webstore** | 商品に含まれる有償通貨部分の価格（WebStore）。通常は0 |

#### 3.3 作成例

```
ENABLE,release_key,id,mst_store_product_id,language,price_ios,price_android,price_webstore,paid_diamond_price_ios,paid_diamond_price_android,paid_diamond_price_webstore
e,202601010,50_ja,50,ja,3000,3000,0,0,0,0
e,202601010,52_ja,52,ja,980,980,0,0,0,0
```

### 4. OprProduct シートの作成

#### 4.1 シートスキーマ

```
ENABLE,id,mst_store_product_id,product_type,purchasable_count,paid_amount,display_priority,start_date,end_date,release_key
```

#### 4.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 商品ID。MstStoreProduct.idと同じ値を使用 |
| **mst_store_product_id** | ストア商品ID。MstStoreProduct.idと対応 |
| **product_type** | 商品タイプ。`Pack`: パック、`Diamond`: ダイヤ、`Pass`: パス |
| **purchasable_count** | 購入可能回数。`1`: 1回限り、`0`または空欄: 無制限 |
| **paid_amount** | 配布する有償一次通貨（ダイヤ）。通常は空欄 |
| **display_priority** | 表示優先度。数字が大きいほど優先（例: 18, 17, 106, 105） |
| **start_date** | 販売開始日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **end_date** | 販売終了日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **release_key** | リリースキー。MstStoreProductと同じ値 |

#### 4.3 product_type設定一覧

| product_type | 説明 | 対応するMstPack |
|-------------|------|----------------|
| **Pack** | パック商品 | あり |
| **Diamond** | ダイヤ商品 | なし |
| **Pass** | パス商品 | なし（MstShopPassを使用） |

**重要**: `product_type=Pack`の場合のみ、MstPackテーブルを作成します。

#### 4.4 作成例

```
ENABLE,id,mst_store_product_id,product_type,purchasable_count,paid_amount,display_priority,start_date,end_date,release_key
e,50,50,Pack,1,,18,"2026-01-16 15:00:00","2026-02-02 10:59:59",202601010
e,52,52,Pack,1,,17,"2026-02-02 15:00:00","2026-02-28 23:59:59",202601010
e,64,64,Diamond,1,300,106,"2026-01-26 15:00:00","2026-02-02 10:59:59",202601010
```

### 5. OprProductI18n シートの作成

#### 5.1 シートスキーマ

```
ENABLE,release_key,id,opr_product_id,language,asset_key
```

#### 5.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。OprProductと同じ値 |
| **id** | I18nの一意識別子。命名規則: `{opr_product_id}_{language}` |
| **opr_product_id** | 商品ID。OprProduct.idと対応 |
| **language** | 言語コード。`ja`: 日本語 |
| **asset_key** | アセットキー。商品バナー画像のパス（例: `increase_prism_00002`）。パック商品の場合は空欄 |

#### 5.3 作成例

```
ENABLE,release_key,id,opr_product_id,language,asset_key
e,202601010,50_ja,50,ja,
e,202601010,52_ja,52,ja,
e,202601010,64_ja,64,ja,increase_prism_00002
```

### 6. MstPack シートの作成

**重要**: このシートは`OprProduct.product_type=Pack`の場合のみ作成します。

#### 6.1 シートスキーマ

```
ENABLE,id,product_sub_id,discount_rate,sale_condition,sale_condition_value,sale_hours,is_display_expiration,pack_type,tradable_count,cost_type,is_first_time_free,cost_amount,is_recommend,asset_key,pack_decoration,release_key
```

#### 6.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | パックの一意識別子。命名規則: `event_item_pack_{連番}` または `monthly_item_pack_{連番}` |
| **product_sub_id** | 商品サブID。OprProduct.idと対応 |
| **discount_rate** | 割引率（%）。0: 割引なし、30: 30%OFF等 |
| **sale_condition** | 販売開始条件。`StageClear`: ステージクリア、`UserLevel`: ユーザーレベル、`__NULL__`: 条件なし |
| **sale_condition_value** | 販売開始条件値。sale_conditionに応じた値。条件なしの場合は0 |
| **sale_hours** | 条件達成からの販売時間（時間単位）。空欄: 無制限 |
| **is_display_expiration** | 有効期限表示フラグ。`0`: 表示しない、`1`: 表示する |
| **pack_type** | パック販売タイプ。`Normal`: 通常、`Daily`: デイリー |
| **tradable_count** | 交換可能個数。0: 無制限 |
| **cost_type** | 販売コスト種別。`Cash`: 課金、`Diamond`: ダイヤ、`PaidDiamond`: 有償ダイヤ、`Ad`: 広告視聴、`Free`: 無料 |
| **is_first_time_free** | 初回無料フラグ。`0`: 通常、`1`: 初回無料 |
| **cost_amount** | コスト量。cost_typeがCashの場合は0（MstStoreProductI18nで管理） |
| **is_recommend** | おすすめフラグ。`0`: 通常、`1`: おすすめ |
| **asset_key** | バナー画像パス。例: `pack_00005`、`pack_00017` |
| **pack_decoration** | パックの装飾。`Gold`: ゴールド装飾、`__NULL__`: 装飾なし |
| **release_key** | リリースキー。MstStoreProductと同じ値 |

#### 6.3 ID採番ルール

MstPack.idは、以下の形式で採番します。

**パターン1: イベントパック**
```
event_item_pack_{連番}
```

**パターン2: 月額パック**
```
monthly_item_pack_{連番}
```

**採番例**:
```
event_item_pack_12   (いいジャン祭パック)
monthly_item_pack_3  (強化パック)
```

#### 6.4 cost_type設定一覧

| cost_type | 説明 | cost_amount設定 |
|----------|------|----------------|
| **Cash** | 課金商品 | 0（MstStoreProductI18nで価格管理） |
| **Diamond** | ダイヤ消費 | ダイヤ数量 |
| **PaidDiamond** | 有償ダイヤ消費 | 有償ダイヤ数量 |
| **Ad** | 広告視聴 | 0 |
| **Free** | 無料 | 0 |

#### 6.5 作成例

```
ENABLE,id,product_sub_id,discount_rate,sale_condition,sale_condition_value,sale_hours,is_display_expiration,pack_type,tradable_count,cost_type,is_first_time_free,cost_amount,is_recommend,asset_key,pack_decoration,release_key
e,event_item_pack_12,50,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00005,__NULL__,202601010
e,monthly_item_pack_3,52,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00017,__NULL__,202601010
```

### 7. MstPackI18n シートの作成

#### 7.1 シートスキーマ

```
ENABLE,release_key,id,mst_pack_id,language,name
```

#### 7.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。MstPackと同じ値 |
| **id** | I18nの一意識別子。命名規則: `{mst_pack_id}_{language}` |
| **mst_pack_id** | パックID。MstPack.idと対応 |
| **language** | 言語コード。`ja`: 日本語 |
| **name** | パック名。改行を含む場合は`\n`で表現 |

#### 7.3 作成例

```
ENABLE,release_key,id,mst_pack_id,language,name
e,202601010,event_item_pack_12_ja,event_item_pack_12,ja,"【お一人様1回まで購入可】\nいいジャン祭 開催記念パック"
e,202601010,monthly_item_pack_3_ja,monthly_item_pack_3,ja,"【お一人様1回まで購入可】\nお得強化パック"
```

### 8. MstPackContent シートの作成

#### 8.1 シートスキーマ

```
ENABLE,id,mst_pack_id,resource_type,resource_id,resource_amount,is_bonus,display_order,release_key
```

#### 8.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | パック内容物の一意識別子。連番採番（例: 113, 114, 115など） |
| **mst_pack_id** | パックID。MstPack.idと対応 |
| **resource_type** | 内包物のタイプ。`FreeDiamond`: ダイヤ、`Coin`: コイン、`Item`: アイテム、`Unit`: ユニット |
| **resource_id** | 内包物のID。resource_typeがItemの場合のみ設定。MstItem.idと対応 |
| **resource_amount** | 内包物の数量 |
| **is_bonus** | おまけフラグ。`0`: 通常、`1`: おまけ。空欄可 |
| **display_order** | 表示順序。数字が大きいほど上に表示 |
| **release_key** | リリースキー。MstPackと同じ値 |

#### 8.3 resource_type設定一覧

| resource_type | 説明 | resource_id設定 | 例 |
|-------------|------|----------------|-----|
| **FreeDiamond** | 無償ダイヤ | 設定不要（空欄） | - |
| **Coin** | コイン | 設定不要（空欄） | - |
| **Item** | アイテム | MstItem.id | `memoryfragment_glo_00001`、`ticket_glo_00003` |
| **Unit** | ユニット | MstUnit.id | `chara_jig_00401` |

#### 8.4 ID採番ルール

MstPackContent.idは、連番で採番します。

**採番パターン**:
- 既存の最大IDを確認し、次の番号を採番
- 通常は100番台から採番される

**採番例**:
```
113  (パック1の内容物1)
114  (パック1の内容物2)
115  (パック1の内容物3)
120  (パック2の内容物1)
```

#### 8.5 作成例

```
ENABLE,id,mst_pack_id,resource_type,resource_id,resource_amount,is_bonus,display_order,release_key
e,113,event_item_pack_12,Item,memoryfragment_glo_00001,50,,4,202601010
e,114,event_item_pack_12,Item,memoryfragment_glo_00002,30,,3,202601010
e,115,event_item_pack_12,Item,memoryfragment_glo_00003,3,,2,202601010
e,116,event_item_pack_12,Item,ticket_glo_00003,10,,1,202601010
```

## データ整合性のチェック

マスタデータ作成後、以下の項目を確認してください。

### 必須チェック項目

- [ ] **ヘッダーの列順が正しいか**
  - スキーマファイルと完全一致している

- [ ] **IDの一意性**
  - すべてのidが一意である
  - 他のリリースのidと重複していない

- [ ] **ID採番ルール**
  - MstStoreProduct.id: 連番
  - OprProduct.id: MstStoreProduct.idと同じ値
  - MstPack.id: `event_item_pack_{連番}` または `monthly_item_pack_{連番}`
  - MstPackContent.id: 連番
  - I18n系テーブルのid: `{親テーブルid}_{language}`

- [ ] **リレーションの整合性**
  - `MstStoreProductI18n.mst_store_product_id` が `MstStoreProduct.id` に存在する
  - `OprProduct.mst_store_product_id` が `MstStoreProduct.id` に存在する
  - `OprProductI18n.opr_product_id` が `OprProduct.id` に存在する
  - `MstPack.product_sub_id` が `OprProduct.id` に存在する
  - `MstPackI18n.mst_pack_id` が `MstPack.id` に存在する
  - `MstPackContent.mst_pack_id` が `MstPack.id` に存在する
  - `MstPackContent.resource_id` が `MstItem.id` に存在する（resource_type=Itemの場合）
  - すべてのI18nテーブルの親IDが存在する

- [ ] **enum値の正確性**
  - product_type: Pack、Diamond、Pass
  - pack_type: Normal、Daily
  - cost_type: Cash、Diamond、PaidDiamond、Ad、Free
  - resource_type: FreeDiamond、Coin、Item、Unit
  - language: ja、en、zh-CN、zh-TW
  - sale_condition: StageClear、UserLevel、__NULL__
  - 大文字小文字が正確に一致している

- [ ] **販売期間の妥当性**
  - start_date < end_date
  - 日時形式が正しい（`YYYY-MM-DD HH:MM:SS`）

- [ ] **価格の整合性**
  - price_ios、price_androidが正の数値である
  - cost_type=Cashの場合、cost_amount=0である（価格はMstStoreProductI18nで管理）

- [ ] **パックタイプとコストタイプの整合性**
  - OprProduct.product_type=Packの場合、MstPackが存在する
  - OprProduct.product_type=Diamondの場合、MstPackは存在しない

### 推奨チェック項目

- [ ] **命名規則の統一**
  - パックIDのプレフィックスが適切（event_item_pack、monthly_item_pack）

- [ ] **I18n設定の完全性**
  - 日本語（ja）が必須で設定されている

- [ ] **パック内容物の妥当性**
  - display_orderが適切に設定されている（重要なアイテムが上）
  - resource_amountが正の数値である

- [ ] **表示優先度の妥当性**
  - display_priorityが適切に設定されている（重要な商品が上）

## 出力フォーマット

最終的な出力は以下の7シート構成で行います。

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

## 重要なポイント

- **7テーブル構成**: ショップ・パックは7テーブルで構成されます
- **階層構造**: MstStoreProduct → OprProduct → MstPack の階層関係を理解する
- **product_typeによる分岐**: Packの場合のみMstPackを作成、Diamondの場合は作成不要
- **プラットフォームIDの不変性**: MstStoreProductのプロダクトIDは変更不可（リストア機能のため）
- **価格管理の分離**: 課金商品の価格はMstStoreProductI18nで管理、ダイヤ商品のコストはMstPackで管理
- **I18nは独立したシート**: 各I18nテーブルは独立したシートとして作成
- **外部キー整合性の徹底**: すべてのリレーションが正しく設定されていることを確認
- **販売期間の重複チェック**: 同じ商品IDで販売期間が重複しないように注意
