# MstPackContent 詳細説明

> CSVパス: `projects/glow-masterdata/MstPackContent.csv`

---

## 概要

`MstPackContent` は**ショップパックの内容物（中身のアイテム・通貨など）を定義するテーブル**。`MstPack` で定義された各パックに対して、1つ以上のリソースレコードを紐付けることでパックの内容を構成する。

複数レコードで1パックの内容を表現でき、`is_bonus` フラグでおまけアイテムを識別することもできる。

### ゲームプレイへの影響

- パック購入時にこのテーブルのレコードすべてがプレイヤーに付与される
- `is_bonus = 1` のアイテムはUI上でおまけとして表示される場合がある
- `display_order` でパック詳細画面内の表示順序を制御する

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_pack_id` | varchar(255) | 不可 | - | 親パックID（`mst_packs.id`） |
| `resource_type` | enum | 不可 | - | 内包物のタイプ（後述のenum参照） |
| `resource_id` | varchar(255) | 可 | NULL | 内包物のID（resource_typeがItemの場合のみ使用） |
| `resource_amount` | bigint unsigned | 不可 | 0 | 内包物の数量 |
| `is_bonus` | tinyint unsigned | 不可 | 0 | おまけフラグ（1=おまけアイテム） |
| `display_order` | int unsigned | 不可 | - | パック詳細画面内での表示順 |
| `release_key` | int | 不可 | 1 | リリースキー |

---

## ResourceType（内包物タイプ）

| 値 | 説明 | resource_id |
|----|------|------------|
| `FreeDiamond` | 無償ダイヤ | NULL |
| `Coin` | コイン | NULL |
| `Item` | アイテム | アイテムID |
| `Unit` | ユニット（キャラ） | ユニットID |

---

## 命名規則 / IDの生成ルール

- `id`: 現状のデータでは単純な連番（`1`、`2`、`3`…）が使用されている

---

## 他テーブルとの連携

```
MstPack
  └─ id → MstPackContent.mst_pack_id（1パック = 複数コンテンツ）

MstPackContent
  └─ resource_id → MstItem.id（resource_type = Item の場合）
  └─ resource_id → MstUnit.id（resource_type = Unit の場合）
```

---

## 実データ例

**パターン1: キャラパックの内容物（ユニット1体）**

| ENABLE | id | mst_pack_id | resource_type | resource_id | resource_amount | is_bonus | display_order | release_key |
|--------|-----|------------|---------------|-------------|-----------------|----------|---------------|-------------|
| e | 1 | start_chara_pack_1 | Unit | chara_spy_00001 | 1 | NULL | 1 | 202509010 |

**パターン2: アイテムパックの内容物（複数アイテム）**

| ENABLE | id | mst_pack_id | resource_type | resource_id | resource_amount | is_bonus | display_order | release_key |
|--------|-----|------------|---------------|-------------|-----------------|----------|---------------|-------------|
| e | 2 | start_item_pack_1 | Item | memory_glo_00001 | 200 | NULL | 5 | 202509010 |
| e | 3 | start_item_pack_1 | Item | memory_glo_00002 | 200 | NULL | 4 | 202509010 |
| e | 7 | start_item_pack_1 | Item | ticket_glo_00002 | 10 | NULL | 6 | 202509010 |

**パターン3: おまけアイテムが含まれるパック**

| ENABLE | id | mst_pack_id | resource_type | resource_id | resource_amount | is_bonus | display_order | release_key |
|--------|-----|------------|---------------|-------------|-----------------|----------|---------------|-------------|
| e | 8 | start_item_pack_2 | Item | ticket_glo_00203 | 1 | NULL | 2 | 202509010 |
| e | 9 | start_item_pack_2 | Item | box_glo_00008 | 10 | 1 | 1 | 202509010 |

---

## 設定時のポイント

1. **mst_pack_idは既存パックと一致させる**: `MstPack` に存在しないIDを指定するとパック内容が読み込めなくなる
2. **resource_idの使い分け**: `FreeDiamond`・`Coin` は resource_id をNULLにする。`Item`・`Unit` は対応するマスタのIDを正確に設定する
3. **display_orderで表示順を制御**: 数値が小さいほど先に表示される。主要アイテムを上位に、おまけを別途設定すると分かりやすい
4. **is_bonusのUI確認**: `is_bonus = 1` の場合のUI表示仕様をクライアントチームと確認する
5. **resource_amountのデフォルトは0**: 設定漏れを防ぐため必ず正の値を設定する
6. **複数レコードで1パックを構成できる**: アイテムパックのように複数種類のアイテムをセットにする場合は複数レコードを作成する
