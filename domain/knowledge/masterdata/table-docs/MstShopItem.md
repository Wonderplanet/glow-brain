# MstShopItem 詳細説明

> CSVパス: `projects/glow-masterdata/MstShopItem.csv`

---

## 概要

`MstShopItem` は**ゲーム内ショップで販売する非課金商品（コイン・ダイヤ・広告視聴・無料）を管理するテーブル**。課金商品（課金ダイヤ購入等）は別テーブル（`MstStoreProduct` 等）で管理し、本テーブルはゲーム内通貨やアイテムを対価に交換できる商品を扱う。

### ゲームへの影響

- **デイリーショップ** (`shop_type = Daily`): 1日1回購入できる商品群。広告視聴で無料ダイヤ・ダイヤでメモリーアイテムなどを提供。
- **ウィークリーショップ** (`shop_type = Weekly`): 1週間に1回購入できる商品群。デイリーより大きな報酬が多い。
- **コインショップ** (`shop_type = Coin`): コインを消費してアイテム等を購入する常設商品。
- **初回無料** (`is_first_time_free = 1`): 初めて購入する際はコスト不要。
- **交換可能回数** (`tradable_count`): `NULL` の場合は無制限。整数値の場合はその回数まで購入可能。

### テーブル連携図

```
MstShopItem（ショップ商品定義）
  └─ resource_id → MstItem.id（resource_type = Itemの場合のみ）

UsrShopItem（購入履歴）
  └─ mst_shop_item_id → MstShopItem.id
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（意味のある文字列） |
| `shop_type` | enum | 可（NULL） | - | 商品タイプ。`Coin` / `Daily` / `Weekly` |
| `cost_type` | enum | 可（NULL） | - | 消費コストの種類。`Coin` / `Diamond` / `PaidDiamond` / `Ad` / `Free` |
| `cost_amount` | int unsigned | 可（NULL） | 0 | 消費コストの数量。`Ad` / `Free` は NULL |
| `is_first_time_free` | tinyint | 不可 | - | 初回無料フラグ。`0` = 無料なし、`1` = 初回無料 |
| `tradable_count` | int unsigned | 可（NULL） | - | 交換可能回数。NULL = 無制限 |
| `resource_type` | enum | 可（NULL） | - | 獲得物の種類。`FreeDiamond` / `Coin` / `IdleCoin` / `Item` |
| `resource_id` | varchar(255) | 可（NULL） | - | 獲得物のID（`resource_type = Item` のときのみ有効） |
| `resource_amount` | bigint unsigned | 不可 | - | 獲得物の数量 |
| `start_date` | timestamp | 不可 | - | 販売開始日時 |
| `end_date` | timestamp | 不可 | - | 販売終了日時 |
| `release_key` | int | 不可 | 1 | リリースキー |

---

## ShopType（enum）

| 値 | 説明 |
|----|------|
| `Coin` | コインショップ。コインで交換できる常設商品 |
| `Daily` | デイリーショップ。1日1回購入可能 |
| `Weekly` | ウィークリーショップ。1週間に1回購入可能 |

## CostType（enum）

| 値 | 説明 |
|----|------|
| `Coin` | コインを消費して購入 |
| `Diamond` | 無償ダイヤを消費して購入 |
| `PaidDiamond` | 有償ダイヤを消費して購入 |
| `Ad` | 広告視聴で購入（`cost_amount` は NULL） |
| `Free` | 無料で購入（`cost_amount` は NULL） |

## ResourceType（enum）

| 値 | 説明 |
|----|------|
| `FreeDiamond` | 無償ダイヤ |
| `Coin` | コイン |
| `IdleCoin` | 放置コイン |
| `Item` | アイテム（`resource_id` で具体的なアイテムを指定） |

---

## 命名規則 / IDの生成ルール

`id` はショップ種別と連番で命名する:

```
{shop_type_lowercase}{連番2桁}
```

例:
- `daily01`, `daily02` → デイリーショップ商品
- `weekly01`, `weekly02` → ウィークリーショップ商品

---

## 他テーブルとの連携

| 連携先テーブル | 結合キー | 用途 |
|-------------|--------|------|
| `MstItem` | `MstShopItem.resource_id = MstItem.id` | アイテム詳細情報を取得（resource_type = Itemのみ） |
| `UsrShopItem` | `UsrShopItem.mst_shop_item_id = MstShopItem.id` | ユーザーの購入履歴 |

---

## 実データ例

### パターン1: デイリー広告視聴でダイヤ獲得

```csv
ENABLE,id,shop_type,cost_type,cost_amount,is_first_time_free,tradable_count,resource_type,resource_id,resource_amount,start_date,end_date,release_key
e,daily01,Daily,Ad,,0,1,FreeDiamond,,20,2024-09-22 12:00:00,2026-03-16 11:59:59,202509010
```

- 広告1回視聴で無償ダイヤ20個を1日1回獲得できる

### パターン2: デイリーダイヤでメモリーアイテム購入

```csv
ENABLE,id,shop_type,cost_type,cost_amount,is_first_time_free,tradable_count,resource_type,resource_id,resource_amount,start_date,end_date,release_key
e,daily02,Daily,Diamond,10,0,1,Item,memory_glo_00001,100,2024-09-22 12:00:00,2034-01-01 00:00:00,202509010
```

- ダイヤ10個でメモリーアイテム100個を1日1回購入できる

---

## 設定時のポイント

1. **`shop_type` と `tradable_count` の組み合わせで購入制限を設定する**。`Daily` は通常 `tradable_count = 1` で1日1回制限。
2. **`cost_type = Ad` と `Free` の場合は `cost_amount` を NULL にする**。これらは消費リソースがないため数量の概念がない。
3. **`resource_type = Item` の場合のみ `resource_id` を設定する**。`FreeDiamond` / `Coin` / `IdleCoin` の場合は NULL のままにする。
4. **`start_date` / `end_date` で販売期間を制御する**。常設商品は `end_date` に遠い未来の日時（例: `2034-01-01 00:00:00`）を設定する。
5. **既存商品の内容変更は新規IDで新しいレコードを追加して行う**。例えば広告ダイヤの獲得量を変更する場合は `daily01` を変更せず、`daily08` などの新IDで新レコードを追加し、`daily01` の `end_date` を変更して切り替える。
6. **`release_key` は商品追加タイミングのリリースキーに合わせる**。日時管理と release_key 管理を組み合わせて版管理する。
7. **`is_first_time_free = 1` の設定は慎重に行う**。初回無料設定が正しく機能しているかクライアント実装も合わせて確認する。
