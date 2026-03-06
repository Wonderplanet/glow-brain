# MstStoreProduct 詳細説明

> CSVパス: `projects/glow-masterdata/MstStoreProduct.csv`
> i18n CSVパス: `projects/glow-masterdata/MstStoreProductI18n.csv`

---

## 概要

AppStore / GooglePlay / WebStore に登録している課金商品のプロダクトIDと、言語別の価格情報を管理するテーブル群。`mst_store_products` が商品のプラットフォームID（Apple・Google・Web）を管理し、`mst_store_products_i18n` が言語ごとの価格情報を管理する。

- 一度定義したプロダクトIDは変更してはならない（リストア機能のため）
- `mst_store_products_i18n` には言語別価格・有償通貨部分の価格などの情報が含まれる
- WebStore対応商品は `product_id_webstore` に値を入力し、非対応商品は NULL にする
- クライアントは `MstStoreProductData`（プロダクトID情報）と `MstStoreProductI18nData`（価格情報）の2つのデータクラスを持つ

---

## 全カラム一覧

### MstStoreProduct カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | UUID（商品の識別子） |
| release_key | bigint | 不可 | - | リリースキー |
| product_id_ios | varchar(255) | 可 | - | AppStoreのプロダクトID |
| product_id_android | varchar(255) | 可 | - | GooglePlayのプロダクトID |
| product_id_webstore | varchar(255) | 可 | - | WebStoreのSKU（NULL: モバイルアプリ専用商品） |

### MstStoreProductI18n カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | UUID（例: `{商品ID}_{言語コード}`） |
| mst_store_product_id | varchar(255) | 不可 | - | `mst_store_products.id` |
| language | varchar(255) | 不可 | - | 言語コード（例: `ja`・`en`） |
| price_ios | decimal(10,3) | 不可 | - | AppStoreの価格 |
| price_android | decimal(10,3) | 不可 | - | GooglePlayの価格 |
| price_webstore | decimal(10,3) | 可 | - | WebStoreの販売価格（NULL: WebStore非対応） |
| paid_diamond_price_ios | decimal(10,3) | 不可 | `0.000` | 商品に含まれる有償通貨部分の価格（iOS） |
| paid_diamond_price_android | decimal(10,3) | 不可 | `0.000` | 商品に含まれる有償通貨部分の価格（Android） |
| paid_diamond_price_webstore | decimal(10,3) | 不可 | `0.000` | 商品に含まれる有償通貨部分の価格（WebStore） |
| release_key | int | 不可 | `1` | リリースキー |

---

## 他テーブルとの連携

| 関連テーブル | カラム | 説明 |
|---|---|---|
| `mst_store_products_i18n` | `mst_store_product_id` → `mst_store_products.id` | 商品の言語別価格情報 |

---

## 実データ例

### 例1: MstStoreProduct（商品プロダクトID）

```
id | product_id_ios | product_id_android             | product_id_webstore | release_key
1  | BNEI0434_0001  | com.bandainamcoent.jumble_0001 | NULL                | 202509010
2  | BNEI0434_0002  | com.bandainamcoent.jumble_0002 | NULL                | 202509010
3  | BNEI0434_0003  | com.bandainamcoent.jumble_0003 | NULL                | 202509010
```

各プロダクトには iOS / Android 両プラットフォームのIDが設定されており、WebStoreは未対応（NULL）。

### 例2: MstStoreProductI18n（言語別価格情報）

```
id      | mst_store_product_id | language | price_ios | price_android | price_webstore | paid_diamond_price_ios | paid_diamond_price_android | release_key
1_ja    | 1                    | ja       | 160       | 160           | 0              | 0                      | 0                          | 202509010
2_ja    | 2                    | ja       | 480       | 480           | 0              | 0                      | 0                          | 202509010
3_ja    | 3                    | ja       | 1000      | 1000          | 0              | 0                      | 0                          | 202509010
4_ja    | 4                    | ja       | 3000      | 3000          | 0              | 0                      | 0                          | 202509010
```

商品1は160円、商品2は480円、商品3は1,000円、商品4は3,000円の価格設定。

---

## 設定時のポイント

1. **プロダクトIDは変更禁止**: 一度AppStore / GooglePlayに登録したプロダクトIDを変更すると、ユーザーのリストアが機能しなくなる。IDは永続的に保持する
2. **i18nのIDは `{商品ID}_{言語コード}` 形式**: 実データでは `1_ja`・`2_ja` のようにメインIDと言語コードを組み合わせたIDが使われている
3. **WebStore非対応商品は product_id_webstore を NULL に**: モバイルアプリ専用商品は `product_id_webstore` を NULL にし、`price_webstore` も NULL にする
4. **有償通貨価格の分離管理**: `paid_diamond_price_*` で商品に含まれる有償ダイヤ相当の価格を管理する。課税計算や会計処理のために分離して記録する
5. **クライアントクラス（本体）**: `MstStoreProductData`（`GLOW.Core.Data.Data`名前空間）。`id`・`releaseKey`・`productIdIos`・`productIdAndroid` が配信される。`product_id_webstore` はクライアントには含まれない
6. **クライアントクラス（i18n）**: `MstStoreProductI18nData`（`GLOW.Core.Data.Data`名前空間）。`id`・`mstStoreProductId`・`language`・`priceIos`・`priceAndroid` が配信される。WebStore価格と有償通貨価格はクライアントには含まれない
7. **言語ごとの価格設定**: 地域ごとに価格が異なる場合、言語コードごとに i18n レコードを追加する
